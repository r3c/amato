<?php

/*
** Universal Markup ENgine
*/

namespace Umen;

define ('UMEN',					'1.0.0.0');

define ('UMEN_ACTION_ALONE',	0);
define ('UMEN_ACTION_START',	1);
define ('UMEN_ACTION_STEP',		2);
define ('UMEN_ACTION_STOP',		3);

abstract class	Converter
{
	/*
	** Convert original string to tokenized format.
	** $context:	custom parsing context
	** $string:		original string
	** return:		tokenized string
	*/
	public abstract function	convert ($context, $string);

	/*
	** Convert tokenized string back to original format.
	** $context:	custom inversion context
	** $token:		tokenized string
	** return:		original string
	*/
	public abstract function	inverse ($context, $token);
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
	** return:		assignation identifier
	*/
	public abstract function	assign ($pattern, $match);

	/*
	** Decode matched pattern back into plain text format.
	** $id:			assignation identifier
	** $captures:	captures array
	** return:		decoded plain string
	*/
	public abstract function	decode ($id, $captures);

	/*
	** Escape given string so it doesn't match any of currently assigned
	** patterns.
	** $string:	raw input string
	** return:	escaped string
	*/
	public abstract function	escape ($string);

	/*
	** Search given string for known patterns, invoke callback for all matches
	** and return cleaned up string (with escape characters removed).
	** $string:		input string
	** $callback:	matching callback (offset, length, match, captures) -> bool
	** return:		cleaned up string
	*/
	public abstract  function	scan ($string, $callback);
}

?>
