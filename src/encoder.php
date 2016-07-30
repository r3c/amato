<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Encoder
{
	/*
	** Decode tokenized string into tag chains and plain string.
	** $token:	tokenized string
	** return:	(plain, chains) array or null on parsing error
	*/
	public abstract function decode ($token);

	/*
	** Encode tag chains and plain string into tokenized string.
	** $plain:	plain string
	** $chains:	resolved tag chains
	** return:	tokenized string
	*/
	public abstract function encode ($plain, $chains);
}

?>
