<?php

namespace Amato;

defined ('AMATO') or die;

class CompactEncoder extends Encoder
{
	const CAPTURE = ',';
	const CAPTURE_VALUE = '=';
	const ESCAPE = '\\';
	const PLAIN = '|';
	const TAG = ';';
	const TAG_MARKER = '@';

	private static $escapes_decode = array
	(
		self::CAPTURE		=> true,
		self::CAPTURE_VALUE	=> true,
		self::PLAIN			=> true,
		self::TAG			=> true,
		self::TAG_MARKER	=> true
	);

	private static $escapes_encode = array
	(
		self::CAPTURE		=> true,
		self::CAPTURE_VALUE	=> true,
		self::ESCAPE		=> true,
		self::PLAIN			=> true,
		self::TAG			=> true,
		self::TAG_MARKER	=> true
	);

	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		$length = strlen ($token);

		// Parse tags
		$shift_tag = 0;
		$tags = array ();

		for ($i = 0; $i < $length && $token[$i] !== self::PLAIN; )
		{
			// Skip to next tag
			if ($i > 0)
			{
				if ($token[$i] !== self::TAG)
					return null;

				++$i;
			}

			// Read tag id
			$id = '';

			for (; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
			{
				if ($token[$i] === self::ESCAPE && $i + 1 < $length)
					++$i;

				$id .= $token[$i];
			}

			// Read markers
			$markers = array ();
			$shift = $shift_tag;

			while ($i < $length && $token[$i] === self::TAG_MARKER)
			{
				// Read offset delta
				for ($j = ++$i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
					++$i;

				$shift += (int)substr ($token, $j, $i - $j);

				// Read captures
				$captures = array ();

				while ($i < $length && $token[$i] === self::CAPTURE)
				{
					$key = '';

					for (++$i; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
					{
						if ($token[$i] === self::ESCAPE && $i + 1 < $length)
							++$i;

						$key .= $token[$i];
					}

					$value = '';

					for (++$i; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
					{
						if ($token[$i] === self::ESCAPE && $i + 1 < $length)
							++$i;

						$value .= $token[$i];
					}

					$captures[$key] = $value;
				}

				// Append to tag markers
				$markers[] = array ($shift, $captures);
			}

			// Append to tags
			$shift_tag = $markers[0][0];

			$tags[] = array ($id, $markers);
		}

		if ($i >= $length || $token[$i++] !== self::PLAIN)
			return null;

		return array ($tags, (string)substr ($token, $i));
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($tags, $plain)
	{
		$shift_tag = 0;
		$token = '';

		foreach ($tags as $tag)
		{
			list ($id, $markers) = $tag;

			// Write tag id
			if ($token !== '')
				$token .= self::TAG;

			$token .= $this->escape ((string)$id);

			// Write markers
			$shift = $shift_tag;

			foreach ($markers as $marker)
			{
				// Write offset delta
				$token .= self::TAG_MARKER . ($marker[0] - $shift);

				// Write captures
				foreach ($marker[1] as $key => $value)
					$token .= self::CAPTURE . $this->escape ($key) . self::CAPTURE_VALUE . $this->escape ((string)$value);

				$shift = $marker[0];
			}

			$shift_tag = $markers[0][0];
		}

		return $token . self::PLAIN . $plain;
	}

	private function escape ($string)
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
