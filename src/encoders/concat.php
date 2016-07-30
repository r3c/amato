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

		list ($groups_string, $plain) = $pack;

		$unescape = function ($string)
		{
			return str_replace (array ('%a', '%c', '%e', '%p', '%s', '%%'), array ('@', ',', '=', '|', ';', '%'), $string);
		};

		$groups = array ();

		if ($groups_string !== '')
		{
			foreach (explode (';', $groups_string) as $group_string)
			{
				$group = explode ('@', $group_string);

				$id = $unescape (array_shift ($group));
				$markers = array ();

				foreach ($group as $marker_string)
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

				$groups[] = array ($id, $markers);
			}
		}

		return array ($plain, $groups);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($plain, $groups)
	{
		$escape = function ($string)
		{
			return str_replace (array ('%', '@', ',', '=', '|', ';'), array ('%%', '%a', '%c', '%e', '%p', '%s'), $string);
		};

		$groups_string = '';

		foreach ($groups as $group)
		{
			if ($groups_string !== '')
				$groups_string .= ';';

			$groups_string .= $escape ($group[0]);

			foreach ($group[1] as $marker)
			{
				$groups_string .= '@' . $marker[0];

				foreach ($marker[1] as $key => $value)
					$groups_string .= ',' . $escape ($key) . '=' . $escape ($value);
			}
		}

		return $groups_string . '|' . $plain;
	}
}

?>
