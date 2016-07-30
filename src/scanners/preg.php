<?php

namespace Amato;

defined ('AMATO') or die;

class PregScanner extends Scanner
{
	const CAPTURE_BEGIN		= '<';
	const CAPTURE_END		= '>';
	const CAPTURE_NAME		= ':';
	const DECODE_CAPTURE	= 0;
	const DECODE_PLAIN		= 1;
	const ESCAPE			= '%';

	public function __construct ($escape = '\\')
	{
		$this->escape = $escape;
		$this->rules = array ();
	}

	/*
	** Override for Scanner::assign.
	*/
	public function assign ($pattern)
	{
		$length = strlen ($pattern);
		$names = array ();
		$parts = array ();
		$regex = '';

		for ($i = 0; $i < $length; ++$i)
		{
			if ($pattern[$i] === self::CAPTURE_BEGIN)
			{
				$name = '';

				for (++$i; $i < $length && $pattern[$i] !== self::CAPTURE_NAME; ++$i)
					$name .= $pattern[$i] === self::ESCAPE ? $pattern[++$i] : $pattern[$i];

				if ($i >= $length)
					throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected capture regular pattern');

				$value = '';

				for (++$i; $i < $length && $pattern[$i] !== self::CAPTURE_END; ++$i)
					$value .= $pattern[$i] === self::ESCAPE ? $pattern[++$i] : $pattern[$i];

				if ($i >= $length)
					throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected end of capture');

				$names[] = $name;
				$parts[] = array (self::DECODE_CAPTURE, $name);

				$regex .= '(' . str_replace ('/', '\\/', $value) . ')';
			}
			else
			{
				$count = count ($parts);
				$plain = preg_quote ($pattern[$i], '/');

				if ($count > 0 && $parts[$count - 1][0] === self::DECODE_PLAIN)
					$parts[$count - 1][1] .= $plain;
				else
					$parts[] = array (self::DECODE_PLAIN, $plain);

				$regex .= $plain;
			}
		}

		$this->rules[] = array ('/' . $regex . '/m', $names, $parts);

		return count ($this->rules) - 1;
	}

	public function find ($string)
	{
		$candidates = array ();

		// Match all candidates from input plain string
		foreach ($this->rules as $key => $rule)
		{
			list ($pattern, $names) = $rule;

			if (preg_match_all ($pattern, $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false)
				throw new \Exception ('invalid regular expression pattern "' . $pattern . '" for tag');

			foreach ($matches as $match)
			{
				$captures = array ();

				for ($i = min (count ($match) - 1, count ($names)); $i-- > 0; )
					$captures[$names[$i]] = $match[$i + 1][0];

				$offset = mb_strlen (substr ($string, 0, $match[0][1]));
				$length = mb_strlen ($match[0][0]);
				$index = str_pad ($offset, 8, '0', STR_PAD_LEFT) . ':' . str_pad (100000000 - $length, 8, '0', STR_PAD_LEFT);

				$candidates[$index][] = array ($key, $offset, $length, $captures);
			}
		}

		// Order candidates by offset ascending, length descending
		ksort ($candidates);

		$matches = array ();

		foreach ($candidates as $overlaps)
			$matches = array_merge ($matches, $overlaps);

		return $matches;
	}

	/*
	** Override for Scanner::escape.
	*/
	public function escape ($string, $verify)
	{
		$candidates = $this->find ($string);

		for ($i = count ($candidates) - 1; $i >= 0; )
		{
			list ($offset, $length, $match, $captures) = $candidates[$i--];

			if ($captures === null || $verify ($match))
			{
				$string = mb_substr ($string, 0, $offset) . $this->escape . mb_substr ($string, $offset);

				while ($i >= 0 && $candidates[$i][0] + $candidates[$i][1] > $offset)
					--$i;
			}
		}

		return $string;
	}

	/*
	** Override for Scanner::make.
	*/
	public function make ($accept, $captures)
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
	public function scan ($string, $process, $verify)
	{
		$candidates = $this->find ($string);
		$count = count ($candidates);
		$shift = 0;

		// Send candidates to matching callback
		for ($i = 0; $i < $count; ++$i)
		{
			list ($offset, $length, $match, $captures) = $candidates[$i];

			$resume = $offset + $length;

			// Candidate is a match, use callback to validate
			if ($match !== null)
			{
				if (!$process ($match, $offset - $shift, $length, $captures))
					continue;
			}

			// Candidate is an escape sequence followed by a valid candidate
			else if ($i + 1 < $count && $candidates[$i + 1][0] === $offset + $length)
			{
				$match = $candidates[$i + 1][2];

				if ($match !== null && !$verify ($match))
					continue;

				$string = mb_substr ($string, 0, $offset - $shift) . mb_substr ($string, $offset - $shift + $length);

				$resume += 1;
				$shift += $length;
			}

			// Remove overlapped matches from candidates list
			while ($i + 1 < $count && $candidates[$i + 1][0] < $resume)
			{
				array_splice ($candidates, $i + 1, 1);

				--$count;
			}
		}

		return $string;
	}
}

?>
