<?php

namespace Amato;

defined ('AMATO') or die;

class ConcatEncoder extends Encoder
{
	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		$pack = explode ('|', $token, 2);

		if (count ($pack) < 2)
			return null;

		list ($chains_string, $plain) = $pack;

		$unescape = function ($string)
		{
			return str_replace (array ('%a', '%c', '%e', '%p', '%s', '%%'), array ('@', ',', '=', '|', ';', '%'), $string);
		};

		$chains = array ();

		if ($chains_string !== '')
		{
			foreach (explode (';', $chains_string) as $chain_string)
			{
				$chain = explode ('@', $chain_string);

				$id = $unescape (array_shift ($chain));
				$markers = array ();

				foreach ($chain as $marker_string)
				{
					$marker = explode (',', $marker_string);

					$captures = array ();
					$offset = (int)array_shift ($marker);

					foreach ($marker as $capture)
					{
						$pair = explode ('=', $capture, 2);

						if (count ($pair) > 1)
							$captures[$unescape ($pair[0])] = $unescape ($pair[1]);
					}

					$markers[] = array ($offset, $captures);
				}

				$chains[] = array ($id, $markers);
			}
		}

		return array ($plain, $chains);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($plain, $chains)
	{
		$escape = function ($string)
		{
			return str_replace (array ('%', '@', ',', '=', '|', ';'), array ('%%', '%a', '%c', '%e', '%p', '%s'), $string);
		};

		$chains_string = '';

		foreach ($chains as $chain)
		{
			if ($chains_string !== '')
				$chains_string .= ';';

			$chains_string .= $escape ($chain[0]);

			foreach ($chain[1] as $marker)
			{
				$chains_string .= '@' . $marker[0];

				foreach ($marker[1] as $key => $value)
					$chains_string .= ',' . $escape ($key) . '=' . $escape ($value);
			}
		}

		return $chains_string . '|' . $plain;
	}
}

?>
