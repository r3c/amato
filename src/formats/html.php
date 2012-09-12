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
	'!'		=> array
	(
		'level'		=> 0,
		'stop'		=> function ($body, $params) { return $params[0]; }
	),
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
		'stop'		=> function ($body) { return $body ? '<b>' . $body . '</b>' : ''; }
	),
	'hr'	=> array
	(
		'stop'		=> function ($body) { return '<hr />'; },
	),
	'i'		=> array
	(
		'stop'		=> function ($body) { return $body ? '<i>' . $body . '</i>' : ''; }
	),
	'img'	=> array
	(
		'stop'		=> 'ymlDemoImgStop'
	),
	'u'		=> array
	(
		'stop'		=> function ($body) { return $body ? '<span style="text-decoration: underline;">' . $body . '</span>' : ''; },
	)
);

function	ymlDemoAStop ($body, $params)
{
	$target = isset ($params[0]) ? $params[0] : $body;

	if (!preg_match ('@^([0-9A-Za-z]+://)?([-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)@', $target, $matches))
		return $body;

	return '<a href="' . htmlspecialchars (($matches[1] ? $matches[1] : 'http://') . $matches[2]) . '">' . $body . '</a>';
}

function	ymlDemoImgStop ($body, $params)
{
	if (isset ($params[1]))
	{
		$size = round (max (min (intval ($params[0]), 200), 20) * 0.01, 2);
		$src = $params[1];
	}
	else
	{
		$size = null;
		$src = $params[0];
	}

	if (!preg_match ('@^([0-9A-Za-z]+://)?([-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)@', $src, $matches))
		return $src;

	$src = htmlspecialchars (($matches[1] ? $matches[1] : 'http://') . $matches[2]);

	if ($size !== null)
		return '<a href="' . $src . '" target="_blank"><img alt="img" src="' . $src . '" onload="this.onload = null; this.width *= ' . $size . ';" /></a>';
	else
		return '<img alt="img" src="' . $src . '" />';
}

?>
