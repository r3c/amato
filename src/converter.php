<?php

namespace Umen;

defined ('UMEN') or die;

abstract class Converter
{
	/*
	** Convert plain text string to tokenized format.
	** $text:	plain text string
	** $custom:	optional custom convert information
	** return:	tokenized string
	*/
	public abstract function convert ($text, $custom = null);

	/*
	** Convert tokenized string back to plain text format.
	** $token:	tokenized string
	** $custom:	optional custom revert information
	** return:	plain text string
	*/
	public abstract function revert ($token, $custom = null);
}

?>
