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
		'level'	=> 0,
//		'limit'	=> 100,
//		'start'	=> 'ymlDemoAStart',
//		'step'	=> 'ymlDemoAStep',
		'stop'	=> function ($name, $params, $body) { return $params[0]; }
	),
/*	'.'		=> array
	(
		'stop'	=> 'ymlDemoReferenceStop'
	),*/
	'0'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'1'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'2'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'3'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'4'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'5'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'6'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'7'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'8'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'9'		=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'10'	=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'11'	=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'12'	=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'13'	=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'14'	=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'15'	=> array
	(
		'stop'	=> 'ymlDemoColorStop'
	),
	'a'		=> array
	(
		'stop'	=> 'ymlDemoAnchorStop',
	),
	'b'		=> array
	(
		'stop'	=> 'ymlDemoSimpleStop'
	),
	'box'	=> array
	(
		'stop'	=> 'ymlDemoBoxStop'
	),
	'c'		=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $params, $body) { return $body ? '<div class="center">' . $body . '</div>' : ''; }
	),
	'cmd'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $params, $body) { return $body ? '<div class="cmd">' . $body . '</div>' : ''; }
	),
	'em'	=> array
	(
		'stop'	=> 'ymlDemoSimpleStop'
	),
	'hr'	=> array
	(
		'stop'	=> function ($name, $params, $body) { return '<hr />'; },
	),
	'i'		=> array
	(
		'stop'	=> 'ymlDemoSimpleStop'
	),
	'img'	=> array
	(
		'stop'	=> 'ymlDemoImageStop'
	),
	'list'	=> array
	(
		'level'	=> 2,
		'start'	=> 'ymlDemoListStart',
		'step'	=> 'ymlDemoListStep',
		'stop'	=> 'ymlDemoListStop'
	),
	'q'		=> array
	(
		'limit'	=> 8,
		'stop'	=> function ($name, $params, $body) { return $body ? '<blockquote>' . $body . '</blockquote>' : ''; }
	),
	's'		=> array
	(
		'step'	=> 'ymlDemoSpanStop'
	),
	'sub'	=> array
	(
		'stop'	=> 'ymlDemoSimpleStop'
	),
	'sup'	=> array
	(
		'stop'	=> 'ymlDemoSimpleStop'
	),
	'u'		=> array
	(
		'stop'	=> 'ymlDemoSpanStop',
	)
);

function	ymlDemoAnchorStop ($name, $params, $body)
{
	$target = isset ($params[0]) ? $params[0] : $body;

	if (!preg_match ('@^([0-9A-Za-z]+://)?([-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)@', $target, $matches))
		return $body;

	return '<a href="' . htmlspecialchars (($matches[1] ? $matches[1] : 'http://') . $matches[2]) . '">' . $body . '</a>';
}

function	ymlDemoBoxStop ($name, $params, $body)
{
	return '<div class="box box_0"><h1 onclick="this.parentNode.className = this.parentNode.className.indexOf(\'box_1\') >= 0 ? \'box box_0\' : \'box box_1\';">' . htmlspecialchars ($params[0]) . '</h1><div>' . $body . '</div></div>';
}

function	ymlDemoColorStop ($name, $params, $body)
{
	return $body ? '<span class="color' . $name . '">' . $body . '</span>' : '';
}

function	ymlDemoImageStop ($name, $params, $body)
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

function	ymlDemoListStart ($name, &$params)
{
	$params = $params + array
	(
		'level'	=> 0,
		'next'	=> 0,
		'out'	=> '',
		'stack'	=> array (),
		'tag'	=> ''
	);
}

function	ymlDemoListStep ($name, &$params, $body)
{
	$body = trim ($body);

	if ($params['tag'] && $body)
	{
		for (; $params['level'] > $params['next']; --$params['level'])
			$params['out'] .= '</li></' . array_pop ($params['stack']) . '>';

		if ($params['level'] == $params['next'])
			$params['out'] .= '</li><li>';

		for (; $params['level'] < $params['next']; ++$params['level'])
			$params['out'] .= '<' . ($params['stack'][] = $params['tag']) . '><li>';

		$params['next'] = 1;
		$params['out'] .=	$body;
	}
	else
		++$params['next'];

	$params['tag'] = ($name == '#' ? 'ol' : 'ul');
}

function	ymlDemoListStop ($name, &$params, $body)
{
	ymlDemoListStep ($name, $params, $body);

	while ($params['level']--)
		$params['out'] .= '</li></' . array_pop ($params['stack']) . '>';

	return $params['out'];
}

/*
function	ymlDemoReferenceStop ($name, $params, $body)
{
	return '-FIXME: ref to post ' . $body . '-';
}
*/
function	ymlDemoSimpleStop ($name, $params, $body)
{
	return $body ? '<' . $name . '>' . $body . '</' . $name . '>' : '';
}

function	ymlDemoSpanStop ($name, $params, $body)
{
	return $body ? '<span class="' . $name . '">' . $body . '</span>' : '';
}

/*
** Missing:
** - media
** - !slap
** - ./0
** - name@domain.com
** - email
** - pre
** - modo
** - google
** - tiwiki
** - smiley
** - urli
** - color
** - serif
** - spoiler
** - noedit
** - nosmile
** - font
** - table
** - itable
** - li
** - ul
** - code
** - left
** - right
** - sp
** - flash
** - png
** - sondage
** - source
** - http://
** - www.
** - unicode
*/

?>
