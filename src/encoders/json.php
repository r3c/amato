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
	public function encode ($plain, $chains)
	{
		return json_encode (array ($plain, $chains));
	}
}

?>
