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
	** $callback:	escape requirement verification callback (match) -> bool
	** return:		escaped string
	*/
	public abstract function	escape ($string, $callback);

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
	** $callback:	matching callback (offset, length, match, captures) -> bool
	** return:		cleaned up string
	*/
	public abstract  function	scan ($string, $callback);
}

?>
