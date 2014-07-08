<?php

namespace Umen;

defined ('UMEN') or die;

abstract class	Renderer
{
	/*
	** Render tokenized string.
	** $token:	tokenized string
	** $escape:	optional plain text escape callback (string) -> string
	** $custom:	optional custom render information
	** return:	rendered string
	*/
	public abstract function	render ($token, $escape = null, $custom = null);
}

?>
