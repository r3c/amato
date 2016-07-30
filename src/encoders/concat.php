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

		$unescape = function ($string)
		{
			return str_replace (array ('%a', '%c', '%e', '%p', '%s', '%%'), array ('@', ',', '=', '|', ';', '%'), $string);
		};

		$tags = array ();

		if ($pack[0] !== '')
		{
			foreach (explode (';', $pack[0]) as $tag_string)
			{
				$tag = explode ('@', $tag_string);

				$id = $unescape (array_shift ($tag));
				$markers = array ();

				foreach ($tag as $marker_string)
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

				$tags[] = array ($id, $markers);
			}
		}

		return array ($tags, $pack[1]);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($tags, $plain)
	{
		$escape = function ($string)
		{
			return str_replace (array ('%', '@', ',', '=', '|', ';'), array ('%%', '%a', '%c', '%e', '%p', '%s'), $string);
		};

		$token = '';

		foreach ($tags as $tag)
		{
			if ($token !== '')
				$token .= ';';

			$token .= $escape ($tag[0]);

			foreach ($tag[1] as $marker)
			{
				$token .= '@' . $marker[0];

				foreach ($marker[1] as $key => $value)
					$token .= ',' . $escape ($key) . '=' . $escape ($value);
			}
		}

		return $token . '|' . $plain;
	}
}

?>
