<?php

namespace Amato;

defined ('AMATO') or die;

class CompactEncoder extends Encoder
{
	const CAPTURE = ',';
	const CAPTURE_VALUE = '=';
	const CHAIN = ';';
	const CHAIN_MARKER = '@';
	const ESCAPE = '\\';
	const PLAIN = '|';

	private static $escapes_decode = array
	(
		self::CAPTURE		=> true,
		self::CAPTURE_VALUE	=> true,
		self::CHAIN			=> true,
		self::CHAIN_MARKER	=> true,
		self::PLAIN			=> true
	);

	private static $escapes_encode = array
	(
		self::CAPTURE		=> true,
		self::CAPTURE_VALUE	=> true,
		self::CHAIN			=> true,
		self::CHAIN_MARKER	=> true,
		self::ESCAPE		=> true,
		self::PLAIN			=> true
	);

	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		$length = strlen ($token);

		// Parse chains
		$shift_chain = 0;
		$chains = array ();

		for ($i = 0; $i < $length && $token[$i] !== self::PLAIN; )
		{
			// Skip to next chain
			if ($i > 0)
			{
				if ($token[$i] !== self::CHAIN)
					return null;

				++$i;
			}

			// Read chain id
			$id = '';

			for (; $i < $length && !isset (self::$escapes_decode[$token[$i]]); ++$i)
			{
				if ($token[$i] === self::ESCAPE && $i + 1 < $length)
					++$i;

				$id .= $token[$i];
			}

			// Read markers
			$markers = array ();
			$shift = $shift_chain;

			while ($i < $length && $token[$i] === self::CHAIN_MARKER)
			{
				// Read offset delta
				for ($j = ++$i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
					++$i;

				$shift += (int)substr ($token, $j, $i - $j);

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

				// Append to chain markers
				$markers[] = array ($shift, $captures);
			}

			// Append to chains
			$shift_chain = $markers[0][0];

			$chains[] = array ($id, $markers);
		}

		if ($i >= $length || $token[$i++] !== self::PLAIN)
			return null;

		return array ($chains, (string)substr ($token, $i));
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($chains, $plain)
	{
		$shift_chain = 0;
		$token = '';

		foreach ($chains as $chain)
		{
			list ($id, $markers) = $chain;

			// Write chain id
			if ($token !== '')
				$token .= self::CHAIN;

			$token .= self::escape ((string)$id);

			// Write markers
			$shift = $shift_chain;

			foreach ($markers as $marker)
			{
				// Write offset delta
				$token .= self::CHAIN_MARKER . ($marker[0] - $shift);

				// Write captures
				foreach ($marker[1] as $key => $value)
				{
					$token .= self::CAPTURE . self::escape ($key);
					$value = (string)$value;

					if ($value !== '')
						$token .= self::CAPTURE_VALUE . self::escape ((string)$value);
				}

				$shift = $marker[0];
			}

			$shift_chain = $markers[0][0];
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
