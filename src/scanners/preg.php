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
					throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected capture name separator');

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
				$plain = $pattern[$i];

				if ($count > 0 && $parts[$count - 1][0] === self::DECODE_PLAIN)
					$parts[$count - 1][1] .= $plain;
				else
					$parts[] = array (self::DECODE_PLAIN, $plain);

				$regex .= preg_quote ($plain, '/');
			}
		}

		$this->rules[] = array ('/(?=(' . $regex . '))/m', $names, $parts);

		return count ($this->rules) - 1;
	}

	/*
	** Override for Scanner::build.
	*/
	public function build ($key, $params)
	{
		$tag = '';

		foreach ($this->rules[$key][2] as $part)
			$tag .= $part[0] === self::DECODE_CAPTURE ? $params[$part[1]] : $part[1];

		return $tag;
	}

	/*
	** Override for Scanner:escape.
	*/
	public function escape ($plain)
	{
		return $this->escape . $plain;
	}

	/*
	** Override for Scanner::find.
	*/
	public function find ($string)
	{
		$sequences = array ();
		$order = 0;

		// Match all escape sequences in input string
		if (preg_match_all ('/(?=(' . preg_quote ($this->escape, '/') . '))/m', $string, $matches, PREG_OFFSET_CAPTURE) === false)
			throw new \Exception ('invalid escape pattern "' . $this->escape . '"');

		foreach ($matches[1] as $match)
		{
			$length = mb_strlen ($match[0]);
			$offset = mb_strlen (substr ($string, 0, $match[1]));

			$sequences[self::index ($offset, $length, $order)] = array (null, $offset, $length);
		}

		// Match all tag sequences in input string
		foreach ($this->rules as $key => $rule)
		{
			list ($pattern, $names) = $rule;

			if (preg_match_all ($pattern, $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false)
				throw new \Exception ('invalid tag pattern "' . $pattern . '"');

			++$order;

			foreach ($matches as $match)
			{
				// Copy named groups to captures array
				$captures = array ();

				for ($i = min (count ($match) - 2, count ($names)); $i-- > 0; )
					$captures[$names[$i]] = $match[$i + 2][0];

				// Append to sequences array, using custom key for fast sorting
				$length = mb_strlen ($match[1][0]);
				$offset = mb_strlen (substr ($string, 0, $match[1][1]));

				$sequences[self::index ($offset, $length, $order)] = array ($key, $offset, $length, $captures);
			}
		}

		// Order sequences by offset ascending, length descending, rule ascending
		ksort ($sequences);

		return array_values ($sequences);
	}

	/*
	** Build array index to allow sorting of sequences using "ksort" instead
	** of "usort" (ugly but way faster).
	*/
	private static function index ($offset, $length, $order)
	{
		return str_pad ($offset, 8, '0', STR_PAD_LEFT) . ':' . str_pad (100000000 - $length, 8, '0', STR_PAD_LEFT) . ':' . str_pad ($order, 8, '0', STR_PAD_LEFT);
	}
}

?>
