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
	** Build tag from given pattern accept key and captures array.
	** $key:		pattern accept key
	** $captures:	captures array
	** return:		tag string
	*/
	public abstract function build ($key, $captures);

	/*
	** Escape plain string into safe markup string.
	** $plain:	plain text string
	** return:	escaped markup string
	*/
	public abstract function escape ($plain);

	/*
	** Find matching candidates within given plain string.
	** $string:	plain text string
	** return:	array of (key, offset, length, captures) candidates
	*/
	public abstract function find ($string);
}

?>
