<?php

namespace Umen;

defined ('UMEN') or die;

abstract class	Scanner
{
	/*
	** Register a new pattern into this scanner instance.
	** $pattern:	registered pattern
	** $match:		associated matching object
	** return:		pattern accept identifier
	*/
	public abstract function	assign ($pattern, $match);

	/*
	** Escape given string so it doesn't match any of currently assigned
	** patterns.
	** $string:		plain text string
	** $verify:		escape requirement check (match) -> bool
	** return:		escaped string
	*/
	public abstract function	escape ($string, $verify);

	/*
	** Make plain text string compatible with given pattern.
	** $accept:		pattern accept identifier
	** $captures:	captures array
	** return:		plain text string
	*/
	public abstract function	make ($accept, $captures);

	/*
	** Search given string for known patterns, invoke callback for all matches
	** and return cleaned up string (with escape characters removed).
	** $string:		plain text string
	** $process:	match processor (match, offset, length, captures) -> bool
	** $verify:		escape requirement check (match) -> bool
	** return:		cleaned up string
	*/
	public abstract  function	scan ($string, $process, $verify);
}

?>
