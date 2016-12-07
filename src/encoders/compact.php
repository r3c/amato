<?php

namespace Amato;

defined ('AMATO') or die;

class CompactEncoder extends Encoder
{
	const ESCAPE = '\\';
	const MAGIC = '!';
	const MARKER = ';';
	const PARAM = ',';
	const PLAIN = '#';
	const VALUE = '=';

	private static $escapes_decode = array
	(
		self::MARKER	=> true,
		self::PLAIN		=> true,
		self::PARAM		=> true,
		self::VALUE		=> true
	);

	private static $escapes_encode = array
	(
		self::ESCAPE	=> true,
		self::MARKER	=> true,
		self::PARAM		=> true,
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
		// Check magic number
		if (substr ($token, 0, strlen (self::MAGIC)) !== self::MAGIC)
			return null;

		// Parse markers
		$length = strlen ($token);
		$markers = array ();
		$offset = 0;

		for ($i = strlen (self::MAGIC); $i < $length && $token[$i] !== self::PLAIN; )
		{
			// Skip to next marker
			if ($i > 0)
			{
				if ($token[$i] !== self::MARKER)
					return null;

				++$i;
			}

			// Read offset delta
			for ($j = $i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
				++$i;

			$offset += (int)substr ($token, $j, $i - $j);

			// Read type
			if ($i >= $length || !isset (self::$markers[$token[$i]]))
				return null;

			$type = self::$markers[$token[$i++]];

			// Read id
			$id = '';

			for (; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
			{
				if ($token[$i] === self::ESCAPE && $i + 1 < $length)
					++$i;

				$id .= $token[$i];
			}

			// Read parameters
			$params = array ();

			while ($i < $length && $token[$i] === self::PARAM)
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
		$token = self::MAGIC;
		$types = array_flip (self::$markers);

		foreach ($markers as $marker)
		{
			list ($id, $offset, $is_first, $is_last, $params) = $marker;

			// Write marker separator if not first
			if ($token !== '')
				$token .= self::MARKER;

			// Write type, id and offset
			$token .= $offset - $shift;
			$token .= $types[($is_first ? 1 : 0) + ($is_last ? 2 : 0)];
			$token .= self::escape ((string)$id);

			// Write params
			foreach ($params as $key => $value)
			{
				$token .= self::PARAM . self::escape ($key);
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
