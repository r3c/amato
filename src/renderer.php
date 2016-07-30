<?php

namespace Umen;

defined ('UMEN') or die;

abstract class Renderer
{
	/*
	** Render tokenized string.
	** $token:	tokenized string
	** $escape:	optional plain text escape callback (string) -> string
	** return:	rendered string
	*/
	public abstract function render ($token, $escape = null);
}

?>
