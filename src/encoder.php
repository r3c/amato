<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Encoder
{
	/*
	** Decode tokenized string into tag chains and plain string.
	** $token:	tokenized string
	** return:	(chains, plain) array or null on parsing error
	*/
	public abstract function decode ($token);

	/*
	** Encode tag chains and plain string into tokenized string.
	** $chains:	resolved tag chains
	** $plain:	plain string
	** return:	tokenized string
	*/
	public abstract function encode ($chains, $plain);
}

?>
