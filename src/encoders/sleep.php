<?php

namespace Umen;

defined ('UMEN') or die;

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
	public function encode ($scopes, $plain)
	{
		return serialize (array ($scopes, $plain));
	}
}

?>
