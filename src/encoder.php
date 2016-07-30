<?php

namespace Umen;

defined ('UMEN') or die;

abstract class Encoder
{
	/*
	** Decode tokenized string into tag scopes and plain string.
	** $token:	tokenized string
	** return:	(scopes, plain) array or null on parsing error
	*/
	public abstract function decode ($token);

	/*
	** Encode tag scopes and plain string into tokenized string.
	** $scopes:	resolved tag scopes
	** $plain:	plain string
	** return:	tokenized string
	*/
	public abstract function encode ($scopes, $plain);
}

?>
