<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Scanner
{
	/*
	** Register a new pattern into this scanner instance.
	** $pattern:	registered pattern
	** return:		pattern accept key
	*/
	public abstract function assign ($pattern);

	/*
	** Find matching candidates within given plain string.
	** $string:		plain text string
	** return:		array of (key, offset, length, captures) candidates
	*/
	public abstract function find ($string);
}

?>
