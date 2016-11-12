<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Encoder
{
	/*
	** Decode tokenized string into tag groups and plain string.
	** $token:	tokenized string
	** return:	(plain, groups) array or null on parsing error
	*/
	public abstract function decode ($token);

	/*
	** Encode tag groups and plain string into tokenized string.
	** $plain:	plain string
	** $groups:	resolved tag groups
	** return:	tokenized string
	*/
	public abstract function encode ($plain, $groups);
}

?>
