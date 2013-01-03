<?php

namespace Umen;

defined ('UMEN') or die;

class	RegExpScanner extends Scanner
{
	const CAPTURE_BEGIN		= '<';
	const CAPTURE_END		= '>';
	const CAPTURE_NAME		= ':';
	const DECODE_CAPTURE	= 0;
	const DECODE_STRING		= 1;
	const GROUP_BEGIN		= '(';
	const GROUP_END			= ')';
	const GROUP_ESCAPE		= '\\';
	const GROUP_NEGATE		= '!';
	const GROUP_RANGE		= '-';
	const REPEAT_BEGIN		= '{';
	const REPEAT_END		= '}';
	const REPEAT_SPLIT		= ',';

	public function	__construct ($escape = '\\')
	{
		$this->escape = $escape;
		$this->table = array ();
	}

	/*
	** Override for Scanner::assign.
	*/
	public function	assign ($expression, $match)
	{
		$capture = null;
		$decode = array ();
		$length = strlen ($expression);
		$keys = array ();
		$pattern = '';

		for ($i = 0; $i < $length; )
		{
			// Parse capture begin instructions
			if ($capture === null && $i < $length && $expression[$i] === self::CAPTURE_BEGIN)
			{
				for ($start = ++$i; $i < $length && $expression[$i] !== self::CAPTURE_NAME; )
					++$i;

				$capture = substr ($expression, $start, $i - $start);
				$decode[] = array (self::DECODE_CAPTURE, $capture);
				$keys[] = $capture;

				$pattern .= '(';
				$i += 1;
			}

			// Parse character or group
			if ($i >= $length)
				throw new \Exception ('parse error for expression "' . $expression . '" at character ' . $i . ', expected character or group');

			if ($expression[$i] === self::GROUP_BEGIN)
			{
				$character = null;
				$pattern .= '[';
				$i += 1;

				if ($i < $length && $expression[$i] === self::GROUP_NEGATE)
				{
					$pattern .= '^';
					$i += 1;
				}

				while ($i < $length && $expression[$i] !== self::GROUP_END)
				{
					if ($i + 1 < $length && $expression[$i] === self::GROUP_ESCAPE)
					{
						$character = $expression[$i + 1];
						$pattern .= preg_quote ($character, '/');
						$i += 2;
					}
					else if ($i + 2 < $length && $expression[$i + 1] === self::GROUP_RANGE)
					{
						$character = $expression[$i];
						$pattern .= preg_quote ($character, '/') . '-' . preg_quote ($expression[$i + 2], '/');
						$i += 3;
					}
					else
					{
						$character = $expression[$i];
						$pattern .= preg_quote ($character, '/');
						$i += 1;
					}
				}

				if ($i >= $length || $expression[$i] !== self::GROUP_END)
					throw new \Exception ('parse error for expression "' . $expression . '" at character ' . $i . ', expected "' . self::GROUP_END . '"');

				if ($character === null)
					throw new \Exception ('empty group in expression "' . $expression . '" at character ' . $i);

				$pattern .= ']';
				$i += 1;
			}
			else
			{
				if ($i + 1 < $length && $expression[$i] === self::GROUP_ESCAPE)
					++$i;

				$character = $expression[$i++];
				$pattern .= preg_quote ($character, '/');
			}

			// Parse repeat modifiers
			if ($i < $length && $expression[$i] === self::REPEAT_BEGIN)
			{
				for ($j = ++$i; $i < $length && $expression[$i] >= '0' && $expression[$i] <= '9'; )
					++$i;

				$repeat = $i > $j ? (int)substr ($expression, $j, $i - $j) : 0;

				if ($i < $length && $expression[$i] == self::REPEAT_SPLIT)
				{
					for ($j = ++$i; $i < $length && $expression[$i] >= '0' && $expression[$i] <= '9'; )
						++$i;

					$max = $i > $j ? (int)substr ($expression, $j, $i - $j) : 0;
					$min = $repeat;
				}
				else
				{
					$max = $repeat;
					$min = $repeat;
				}

				if ($i >= $length || $expression[$i] !== self::REPEAT_END)
					throw new \Exception ('parse error for expression "' . $expression . '" at character ' . $i . ', expected "' . self::REPEAT_END . '"');

				$pattern .= '{' . ($min > 0 ? $min : '0') . ',' . ($max > 0 ? $max : '') . '}';
				$i += 1;
			}
			else
				$repeat = 1;

			// Register constant characters to decode array
			if ($capture === null)
			{
				$constant = str_repeat ($character, $repeat);
				$count = count ($decode);

				if ($count > 0 && $decode[$count - 1][0] === self::DECODE_STRING)
					$decode[$count - 1][1] .= $constant;
				else
					$decode[] = array (self::DECODE_STRING, $constant);
			}

			// Parse capture end instructions
			if ($capture !== null && $i < $length && $expression[$i] === self::CAPTURE_END)
			{
				$capture = null;
				$pattern .= ')';
				$i += 1;
			}
		}

		$this->table[] = array ($match, $decode, '/' . $pattern . '/m', $keys);

		return count ($this->table) - 1;
	}

	/*
	** Override for Scanner::escape.
	*/
	public function	escape ($string, $callback)
	{
		$candidates = $this->find ($string);
		$shift = 0;

		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($offset, $length, $match, $captures) = $candidates[$i];

			if ($captures === null || $callback ($match))
			{
				$string = substr_replace ($string, $this->escape, $offset + $shift, 0);
				$shift += strlen ($this->escape);
			}
		}

		return $string;
	}

	/*
	** Override for Scanner::make.
	*/
	public function	make ($accept, $captures)
	{
		$decode = $this->table[$accept][1];
		$string = '';

		foreach ($decode as $segment)
		{
			if ($segment[0] === self::DECODE_STRING)
				$string .= $segment[1];
			else if (isset ($captures[$segment[1]]))
				$string .= $captures[$segment[1]];
		}

		return $string;
	}

	/*
	** Override for Scanner::scan.
	*/
	public function	scan ($string, $callback)
	{
		$candidates = $this->find ($string);
		$shift = 0;

		// Send candidates to matching callback
		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($offset, $length, $match, $captures) = $candidates[$i];

			if ($captures !== null)
			{
				if (!$callback ($offset - $shift, $length, $match, $captures))
					continue;
			}
			else
			{
				$string = substr_replace ($string, '', $offset - $shift, $length);

				$offset += 1;
				$shift += $length;
			}

			while ($i + 1 < count ($candidates) && $candidates[$i + 1][0] < $offset + $length)
				array_splice ($candidates, $i + 1, 1);
		}

		return $string;
	}

	/*
	** Find all tag candidates from input plain text.
	** $string:	input plain text string
	** return:	sorted candidates array (offset, length, match, captures)
	*/
	private function	find ($string)
	{
		// Search for candidates amongst tag patterns
		$candidates = array ();

		foreach ($this->table as $array)
		{
			list ($match, $decode, $pattern, $keys) = $array;

			if (preg_match_all ($pattern, $string, $finds, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false)
				throw new \Exception ('invalid regular expression pattern "' . $pattern . '" for tag');

			foreach ($finds as $find)
			{
				$captures = array ();

				for ($i = min (count ($keys), count ($find) - 1); $i-- > 0; )
					$captures[$keys[$i]] = $find[$i + 1][0];

				$candidates[] = array ($find[0][1], strlen ($find[0][0]), $match, $captures);
			}
		}

		// Search for escape sequences within input string
		if (preg_match_all ('/' . preg_quote ($this->escape, '/') . '/', $string, $finds, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false)
			throw new \Exception ('invalid regular expression pattern "' . $this->escape . '" for escape sequence');

		foreach ($finds as $find)
			$candidates[] = array ($find[0][1], strlen ($find[0][0]), null, null);

		// Sort candidates by start offset
		usort ($candidates, function ($a, $b)
		{
			return $a[0] < $b[0] ? -1 : 1;
		});

		return $candidates;
	}
}

?>
