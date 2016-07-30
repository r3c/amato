<?php

namespace Amato;

defined ('AMATO') or die;

class SleepEncoder extends Encoder
{
	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		return unserialize ($token);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($plain, $groups)
	{
		return serialize (array ($plain, $groups));
	}
}

?>
