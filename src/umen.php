<?php

/*
** Universal Markup ENgine
*/

namespace Umen;

define ('UMEN',					'1.0.1.0');

define ('UMEN_ACTION_ALONE',	0);
define ('UMEN_ACTION_START',	1);
define ('UMEN_ACTION_STEP',		2);
define ('UMEN_ACTION_STOP',		3);

abstract class	Converter
{
	/*
	** Convert original string to tokenized format.
	** $string:	original string
	** $escape:	plain text escaping callback (string) -> string
	** $custom:	custom conversion information
	** return:	tokenized string
	*/
	public abstract function	convert ($string, $escape, $custom = null);

	/*
	** Convert tokenized string back to original format.
	** $token:		tokenized string
	** $unescape:	plain text unescaping callback (string) -> string
	** $custom:		custom inversion information
	** return:		original string
	*/
	public abstract function	inverse ($token, $unescape, $custom = null);
}

abstract class	Encoder
{
	/*
	** Decode tokenized string into tag scopes and plain string.
	** $token:	tokenized string
	** return:	(scopes, plain) array or null on parsing error
	*/
	public abstract function	decode ($token);

	/*
	** Encode tag scopes and plain string into tokenized string.
	** $scopes:	resolved tag scopes
	** $plain:	plain string
	** return:	tokenized string
	*/
	public abstract function	encode ($scopes, $plain);
}

abstract class	Renderer
{
	/*
	** Render tokenized string.
	** $token:	tokenized string
	** return:	rendered string
	*/
	public abstract function	render ($token);
}

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
