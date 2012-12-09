<?php

namespace Umen;

defined ('UMEN') or die;

class	JSONEncoder extends Encoder
{
	public function	decode ($token)
	{
		return json_decode ($token, true);
	}

	public function	encode ($scopes, $plain)
	{
		return json_encode (array ($scopes, $plain));
	}
}

?>
