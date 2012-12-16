<?php

namespace Umen;

defined ('UMEN') or die;

define ('UMEN_SCANNER_REGEXP_CAPTURE_BEGIN',	'<');
define ('UMEN_SCANNER_REGEXP_CAPTURE_END',		'>');
define ('UMEN_SCANNER_REGEXP_CAPTURE_NAME',		':');
define ('UMEN_SCANNER_REGEXP_DECODE_CAPTURE',	0);
define ('UMEN_SCANNER_REGEXP_DECODE_STRING',	1);
define ('UMEN_SCANNER_REGEXP_GROUP_BEGIN',		'(');
define ('UMEN_SCANNER_REGEXP_GROUP_END',		')');
define ('UMEN_SCANNER_REGEXP_GROUP_ESCAPE',		'\\');
define ('UMEN_SCANNER_REGEXP_GROUP_NEGATE',		'!');
define ('UMEN_SCANNER_REGEXP_GROUP_RANGE',		'-');
define ('UMEN_SCANNER_REGEXP_REPEAT_BEGIN',		'{');
define ('UMEN_SCANNER_REGEXP_REPEAT_END',		'}');
define ('UMEN_SCANNER_REGEXP_REPEAT_SPLIT',		',');

class	RegExpScanner extends Scanner
{
	public function	__construct ($escape = '\\')
	{
		$this->escape = $escape;
		$this->table = array ();
	}

	public function	assign ($pattern, $match)
	{
		$capture = null;
		$decode = array ();
		$length = strlen ($pattern);
		$names = array ();
		$regexp = '/';

		for ($i = 0; $i < $length; )
		{
			// Parse capture instructions
			if ($i < $length && $pattern[$i] === UMEN_SCANNER_REGEXP_CAPTURE_BEGIN)
			{
				for ($start = ++$i; $i < $length && $pattern[$i] !== UMEN_SCANNER_REGEXP_CAPTURE_NAME; )
					++$i;

				$capture = substr ($pattern, $start, $i - $start);
				$decode[] = array (UMEN_SCANNER_REGEXP_DECODE_CAPTURE, $capture);
				$names[] = $capture;
				$regexp .= '(';
				$i += 1;
			}

			if ($i < $length && $pattern[$i] === UMEN_SCANNER_REGEXP_CAPTURE_END)
			{
				$capture = null;
				$regexp .= ')';
				$i += 1;
			}

			// Parse character or group
			$character = '?';

			if ($i < $length)
			{
				if ($pattern[$i] === UMEN_SCANNER_REGEXP_GROUP_BEGIN)
				{
					$regexp .= '[';
					$i += 1;

					if ($i < $length && $pattern[$i] === UMEN_SCANNER_REGEXP_GROUP_NEGATE)
					{
						$regexp .= '!';
						$i += 1;
					}

					while ($i < $length && $pattern[$i] !== UMEN_SCANNER_REGEXP_GROUP_END)
					{
						if ($i + 1 < $length && $pattern[$i] === UMEN_SCANNER_REGEXP_GROUP_ESCAPE)
						{
							$character = $pattern[$i + 1];
							$regexp .= preg_quote ($character, '/');
							$i += 2;
						}
						else if ($i + 2 < $length && $pattern[$i + 1] === UMEN_SCANNER_REGEXP_GROUP_RANGE)
						{
							$character = $pattern[$i];
							$regexp .= preg_quote ($character, '/') . '-' . preg_quote ($pattern[$i + 2], '/');
							$i += 3;
						}
						else
						{
							$character = $pattern[$i];
							$regexp .= preg_quote ($character, '/');
							$i += 1;
						}
					}

					if ($i >= $length || $pattern[$i] !== UMEN_SCANNER_REGEXP_GROUP_END)
						throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . UMEN_SCANNER_REGEXP_GROUP_END . '"');

					$regexp .= ']';
					$i += 1;
				}
				else
				{
					if ($i + 1 < $length && $pattern[$i] === UMEN_SCANNER_REGEXP_GROUP_ESCAPE)
						++$i;

					$character = $pattern[$i++];
					$regexp .= preg_quote ($character, '/');
				}
			}

			// Parse repeat modifiers
			if ($i < $length && $pattern[$i] === UMEN_SCANNER_REGEXP_REPEAT_BEGIN)
			{
				for ($j = ++$i; $i < $length && $pattern[$i] >= '0' && $pattern[$i] <= '9'; )
					++$i;

				$min = $i > $j ? (int)substr ($pattern, $j, $i - $j) : 0;

				if ($i < $length && $pattern[$i] == UMEN_SCANNER_REGEXP_REPEAT_SPLIT)
				{
					for ($j = ++$i; $i < $length && $pattern[$i] >= '0' && $pattern[$i] <= '9'; )
						++$i;

					$max = $i > $j ? (int)substr ($pattern, $j, $i - $j) : 0;
				}
				else
					$max = $min;

				if ($i >= $length || $pattern[$i] !== UMEN_SCANNER_REGEXP_REPEAT_END)
					throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . UMEN_SCANNER_REGEXP_REPEAT_END . '"');

				$regexp .= '{' . ($min > 0 ? $min : '') . ',' . ($max > 0 ? $max : '') . '}';
				$i += 1;
			}
			else
				$min = 1;

			// Register constant characters to decode array
			if ($capture === null)
			{
				$constant = str_repeat ($character, $min);
				$count = count ($decode);

				if ($count > 0 && $decode[$count - 1][0] === UMEN_SCANNER_REGEXP_DECODE_STRING)
					$decode[$count - 1][1] .= $constant;
				else
					$decode[] = array (UMEN_SCANNER_REGEXP_DECODE_STRING, $constant);
			}
		}

		$regexp .= '/';

		$this->table[] = array ($match, $decode, $regexp, $names);

		return count ($this->table) - 1;
	}

	public function	escape ($string, $callback)
	{
throw new \Exception ('not implemented');
	}

	public function	make ($accept, $captures)
	{
		$decode = $this->table[$accept][1];
		$string = '';

		foreach ($decode as $segment)
		{
			if ($segment[0] === UMEN_SCANNER_DECODE_STRING)
				$string .= $segment[1];
			else if (isset ($captures[$segment[1]]))
				$string .= $captures[$segment[1]];
		}

		return $string;
	}

	public function	scan ($string, $callback)
	{
echo "FIXME: no escape sequence!<br />";
		// Search for candidates within input string
		$candidates = array ();

		foreach ($this->table as $array)
		{
			list ($match, $decode, $regexp, $names) = $array;

			if (preg_match_all ($regexp, $string, $finds, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false)
				throw new \Exception ('invalid regular expression "' . $regexp . '"');

			foreach ($finds as $find)
			{
				$captures = array ();

				for ($i = min (count ($names), count ($find) - 1); $i-- > 0; )
					$captures[$names[$i]] = $find[$i + 1][0];

				$candidates[] = array ($find[0][1], strlen ($find[0][0]), $match, $captures);
			}
		}

		// Sort candidates by start offset
		uasort ($candidates, function ($a, $b)
		{
			return $a[0] < $b[0] ? -1 : 1;
		});

		// Send found candidates to matching callback
		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($offset, $length, $match, $captures) = $candidates[$i];

			if (call_user_func ($callback, $offset, $length, $match, $captures))
			{

				while ($i + 1 < count ($candidates) && $candidates[$i + 1] < $offset + $length)
					array_splice ($candidates, $i + 1, 1);
			}
		}

		return $string;
	}
}

?>
