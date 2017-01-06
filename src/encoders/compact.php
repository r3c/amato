<?php

namespace Amato;

defined ('AMATO') or die;

class CompactEncoder extends Encoder
{
	const CAPTURE = ',';
	const CAPTURE_VALUE = '=';
	const ESCAPE = '\\';
	const GROUP = ';';
	const GROUP_MARKER = '@';
	const MAGIC = '!';
	const PLAIN = '|';

	private static $escapes_decode = array
	(
		self::CAPTURE		=> true,
		self::CAPTURE_VALUE	=> true,
		self::GROUP			=> true,
		self::GROUP_MARKER	=> true,
		self::PLAIN			=> true
	);

	private static $escapes_encode = array
	(
		self::CAPTURE		=> true,
		self::CAPTURE_VALUE	=> true,
		self::ESCAPE		=> true,
		self::GROUP			=> true,
		self::GROUP_MARKER	=> true,
		self::PLAIN			=> true
	);

	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		$skip = strlen (self::MAGIC);

		// Check magic number
		if (substr ($token, 0, $skip) !== self::MAGIC)
			return null;

		// Parse groups
		$delta = 0;
		$groups = array ();
		$length = strlen ($token);

		for ($i = $skip; $i < $length && $token[$i] !== self::PLAIN; )
		{
			// Skip to next group
			if ($i !== $skip)
			{
				if ($token[$i] !== self::GROUP)
					return null;

				++$i;
			}

			// Read group id
			$id = '';

			for (; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
			{
				if ($token[$i] === self::ESCAPE && $i + 1 < $length)
					++$i;

				$id .= $token[$i];
			}

			// Read markers
			$markers = array ();
			$delta_group = $delta;

			while ($i < $length && $token[$i] === self::GROUP_MARKER)
			{
				// Read offset delta
				for ($j = ++$i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
					++$i;

				$delta_group += (int)substr ($token, $j, $i - $j);

				// Read captures
				$captures = array ();

				while ($i < $length && $token[$i] === self::CAPTURE)
				{
					// Read capture key
					$key = '';

					for (++$i; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
					{
						if ($token[$i] === self::ESCAPE && $i + 1 < $length)
							++$i;

						$key .= $token[$i];
					}

					// Read capture value if any
					$value = '';

					if ($i < $length && $token[$i] === self::CAPTURE_VALUE)
					{
						for (++$i; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
						{
							if ($token[$i] === self::ESCAPE && $i + 1 < $length)
								++$i;

							$value .= $token[$i];
						}
					}

					// Store in captures array
					$captures[$key] = $value;
				}

				// Append to group markers
				$markers[] = array ($delta_group, $captures);
			}

			// Append to groups
			$delta = $markers[0][0];

			$groups[] = array ($id, $markers);
		}

		if ($i >= $length || $token[$i++] !== self::PLAIN)
			return null;

		return array ((string)substr ($token, $i), $groups);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($plain, $groups)
	{
		// Serialize to string
		$delta = 0;
		$next = false;
		$token = self::MAGIC;

		foreach ($groups as $group)
		{
			list ($id, $markers) = $group;

			// Write group id
			if ($next)
				$token .= self::GROUP;
			else
				$next = true;

			$token .= self::escape ((string)$id);

			// Write markers
			$delta_group = $delta;

			foreach ($markers as $marker)
			{
				// Write offset delta
				$token .= self::GROUP_MARKER . ($marker[0] - $delta_group);

				// Write captures
				foreach ($marker[1] as $key => $value)
				{
					$token .= self::CAPTURE . self::escape ($key);
					$value = (string)$value;

					if ($value !== '')
						$token .= self::CAPTURE_VALUE . self::escape ((string)$value);
				}

				$delta_group = $marker[0];
			}

			$delta = $markers[0][0];
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
