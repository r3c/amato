<?php

require_once ('src/yml.php');

/*
** String format modifiers for each available tag, as name => properties
**   .level:	optional nesting level (a tag can only enclose tags of lower or
**				equal levels), default is 1
**   .limit:	optional allowed number of uses of this tag, default is 100
**   .start:	optional tag begin callback, undefined if none
**   .step:		optional tag break callback, undefined if none
**   .stop:		tag end callback
*/
$ymlFormatsHTML = array
(
	'a'		=> array
	(
		'level'		=> 2,
//		'limit'		=> 100,
//		'start'		=> 'ymlDemoAStart',
//		'step'		=> 'ymlDemoAStep',
		'stop'		=> 'ymlDemoAStop',
	),
	'b'		=> array
	(
		'stop'		=> function ($body) { return $body ? '<b>' . $body . '</b>' : ''; },
	),
	'hr'	=> array
	(
		'stop'		=> function ($body) { return '<hr />'; },
	),
	'u'		=> array
	(
		'stop'		=> function ($body) { return $body ? '<u>' . $body . '</u>' : ''; },
	)
);

function	ymlDemoAStop ($body, $arguments)
{
	$target = isset ($arguments[0]) ? $arguments[0] : $body;

	if (preg_match ('@^[0-9A-Za-z]+://@', $target))
		$href = $target;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $target))
		$href = 'http://' . $target;
	else
		return null;

	return '<a href="' . $href . '">' . $body . '</a>';
}

?>
