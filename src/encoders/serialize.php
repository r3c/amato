<?php

namespace Umen;

defined ('UMEN') or die;

class	SerializeEncoder extends Encoder
{
	public function	decode ($token)
	{
		return unserialize ($token);
	}

	public function	encode ($scopes, $plain)
	{
		return serialize (array ($scopes, $plain));
	}
}

?>
