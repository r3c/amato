<?php

namespace Umen;

defined ('UMEN') or die;

class ConcatEncoder extends Encoder
{
	const VERSION	= 1;

	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		$pack = explode ('|', $token, 3);

		if (count ($pack) < 3 || (int)$pack[0] !== self::VERSION)
			return null;

		return array (array_map (function ($scope)
		{
			$array = explode (',', $scope);

			$action = (int)$array[2];
			$captures = array ();
			$delta = (int)$array[0];
			$flag = str_replace (array ('%p', '%s', '%c', '%%'), array ('|', ';', ',', '%'), $array[3]);
			$name = str_replace (array ('%p', '%s', '%c', '%%'), array ('|', ';', ',', '%'), $array[1]);

			for ($i = 4; $i < count ($array); ++$i)
			{
				list ($key, $value) = explode ('=', $array[$i], 2);

				$key = str_replace (array ('%p', '%s', '%c', '%e', '%%'), array ('|', ';', ',', '=', '%'), $key);
				$value = str_replace (array ('%p', '%s', '%c', '%%'), array ('|', ';', ',', '%'), $value);

				$captures[$key] = $value;
			}

			return array ($delta, $name, $action, $flag, $captures);
		}, explode (';', $pack[1])), $pack[2]);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($scopes, $plain)
	{
		return (string)self::VERSION . '|' . implode (';', array_map (function ($scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			$string =
				(string)$delta . ',' .
				str_replace (array ('%', '|', ';', ','), array ('%%', '%p', '%s', '%c'), $name) . ',' .
				(string)$action . ',' .
				str_replace (array ('%', '|', ';', ','), array ('%%', '%p', '%s', '%c'), $flag);

			foreach ($captures as $key => $value)
			{
				$string .= ',' .
					str_replace (array ('%', '|', ';', ',', '='), array ('%%', '%p', '%s', '%c', '%e'), $key) . '=' .
					str_replace (array ('%', '|', ';', ','), array ('%%', '%p', '%s', '%c'), $value);
			}

			return $string;
		}, $scopes)) . '|' . $plain;
	}
}

?>
