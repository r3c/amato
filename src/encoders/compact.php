<?php

namespace Amato;

defined ('AMATO') or die;

class CompactEncoder extends Encoder
{
	const CAPTURE = ',';
	const ESCAPE = '\\';
	const MARKER = ';';
	const PLAIN = '#';
	const VALUE = '=';

	private static $escapes_decode = array
	(
		self::CAPTURE	=> true,
		self::MARKER	=> true,
		self::PLAIN		=> true,
		self::VALUE		=> true
	);

	private static $escapes_encode = array
	(
		self::CAPTURE	=> true,
		self::ESCAPE	=> true,
		self::MARKER	=> true,
		self::PLAIN		=> true,
		self::VALUE		=> true
	);

	private static $markers = array
	(
		'-'	=> 0,
		'<'	=> 1,
		'>'	=> 2,
		'|'	=> 3
	);

	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		$length = strlen ($token);

		// Parse markers
		$markers = array ();
		$offset = 0;

		for ($i = 0; $i < $length && $token[$i] !== self::PLAIN; )
		{
			// Skip to next marker
			if ($i > 0)
			{
				if ($token[$i] !== self::MARKER)
					return null;

				++$i;
			}

			// Read type
			if ($i >= $length || !isset (self::$markers[$token[$i]]))
				return null;

			$type = self::$markers[$token[$i++]];

			// Read id
			$id = '';

			for (; $i < $length && $token[$i] !== self::CAPTURE; ++$i)
			{
				if ($token[$i] === self::ESCAPE && $i + 1 < $length)
					++$i;

				$id .= $token[$i];
			}

			if ($i >= $length)
				return null;

			// Read offset delta
			for ($j = ++$i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
				++$i;

			$offset += (int)substr ($token, $j, $i - $j);

			// Read params
			$params = array ();

			while ($i < $length && $token[$i] === self::CAPTURE)
			{
				// Read param key
				$key = '';

				for (++$i; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
				{
					if ($token[$i] === self::ESCAPE && $i + 1 < $length)
						++$i;

					$key .= $token[$i];
				}

				// Read param value if any
				$value = '';

				if ($i < $length && $token[$i] === self::VALUE)
				{
					for (++$i; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
					{
						if ($token[$i] === self::ESCAPE && $i + 1 < $length)
							++$i;

						$value .= $token[$i];
					}
				}

				// Store in params array
				$params[$key] = $value;
			}

			// Append to markers
			$markers[] = array ($id, $offset, !!($type & 1), !!($type & 2), $params);
		}

		if ($i >= $length || $token[$i] !== self::PLAIN)
			return null;

		return array ((string)substr ($token, ++$i), $markers);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($plain, $markers)
	{
		$shift = 0;
		$token = '';
		$types = array_flip (self::$markers);

		foreach ($markers as $marker)
		{
			list ($id, $offset, $is_first, $is_last, $params) = $marker;

			// Write marker separator if not first
			if ($token !== '')
				$token .= self::MARKER;

			// Write type, id and offset
			$token .= $types[($is_first ? 1 : 0) + ($is_last ? 2 : 0)];
			$token .= self::escape ((string)$id);
			$token .= self::CAPTURE;
			$token .= $offset - $shift;

			// Write params
			foreach ($params as $key => $value)
			{
				$token .= self::CAPTURE . self::escape ($key);
				$value = (string)$value;

				if ($value !== '')
					$token .= self::VALUE . self::escape ((string)$value);
			}

			$shift = $offset;
		}

		return $token . self::PLAIN . $plain;
	}

	private static function escape ($string)
	{
		$escape = '';
		$length = strlen ($string);

		for ($i = 0; $i < $length; ++$i)
		{
			$character = $string[$i];

			if (isset (self::$escapes_encode[$character]))
				$escape .= self::ESCAPE;

			$escape .= $character;
		}

		return $escape;
	}
}

?>
