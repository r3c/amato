<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Renderer
{
	/*
	** Render tokenized string.
	** $token:	tokenized string
	** $state:	custom state
	** return:	rendered string
	*/
	public abstract function render ($token, $state = null);
}

?>
