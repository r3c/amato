<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Renderer
{
	/*
	** Render tokenized string.
	** $token:	tokenized string
	** return:	rendered string
	*/
	public abstract function render ($token);
}

?>
