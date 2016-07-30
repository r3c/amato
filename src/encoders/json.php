<?php

namespace Amato;

defined ('AMATO') or die;

class JSONEncoder extends Encoder
{
	/*
	** Override for Encoder::decode.
	*/
	public function decode ($token)
	{
		return json_decode ($token, true);
	}

	/*
	** Override for Encoder::encode.
	*/
	public function encode ($tags, $plain)
	{
		return json_encode (array ($tags, $plain));
	}
}

?>
