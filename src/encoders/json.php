<?php

namespace Umen;

defined ('UMEN') or die;

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
	public function encode ($scopes, $plain)
	{
		return json_encode (array ($scopes, $plain));
	}
}

?>
