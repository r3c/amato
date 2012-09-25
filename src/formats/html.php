<?php

require_once ('src/mapa.php');

/*
** String format modifiers for each available tag, as name => properties
**   .level:	optional nesting level (a tag can only enclose tags of lower or
**				equal levels), default is 1
**   .limit:	optional allowed number of uses of this tag, default is 100
**   .start:	optional tag begin callback, undefined if none
**   .step:		optional tag break callback, undefined if none
**   .stop:		tag end callback
*/
$mapaFormatsHTML = array
(
	'!'		=> array
	(
		'level'	=> 0,
//		'limit'	=> 100,
		'apply'	=> function ($name, $value, $params) { return $params[0]; }
//		'start'	=> 'mapaDemoAStart',
//		'step'	=> 'mapaDemoAStep',
//		'stop'	=> 'mapaDemoAStop'
	),
	'.'		=> array
	(
		'apply'	=> function ($name, $value, $params) { return '<a href="" onclick="getPost(event, ' . 'FIXME' . ',' . htmlspecialchars ($params[0]) . ');return false;">./' . htmlspecialchars ($params[0]) . '</a>'; }
	),
	'0'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'1'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'2'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'3'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'4'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'5'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'6'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'7'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'8'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'9'		=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'10'	=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'11'	=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'12'	=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'13'	=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'14'	=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'15'	=> array
	(
		'stop'	=> 'mapaDemoColorStop'
	),
	'a'		=> array
	(
		'apply'	=> 'mapaDemoAnchorApply',
		'stop'	=> 'mapaDemoAnchorStop',
	),
	'b'		=> array
	(
		'stop'	=> 'mapaDemoSimpleStop'
	),
	'box'	=> array
	(
		'stop'	=> function ($name, $value, $params, $body) { return '<div class="box box_0"><h1 onclick="this.parentNode.className = this.parentNode.className.indexOf(\'box_1\') >= 0 ? \'box box_0\' : \'box box_1\';">' . htmlspecialchars ($params[0]) . '</h1><div>' . $body . '</div></div>'; }
	),
	'c'		=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<div class="center">' . $body . '</div>' : ''; }
	),
	'cmd'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<div class="cmd">' . $body . '</div>' : ''; }
	),
	'em'	=> array
	(
		'stop'	=> 'mapaDemoSimpleStop'
	),
	'hr'	=> array
	(
		'level'	=> 2,
		'apply'	=> function ($name, $value, $params) { return '<hr />'; },
	),
	'i'		=> array
	(
		'stop'	=> 'mapaDemoSimpleStop'
	),
	'img'	=> array
	(
		'apply'	=> 'mapaDemoImageApply'
	),
	'list'	=> array
	(
		'level'	=> 2,
		'start'	=> 'mapaDemoListStart',
		'step'	=> 'mapaDemoListStep',
		'stop'	=> 'mapaDemoListStop'
	),
	'q'		=> array
	(
		'limit'	=> 8,
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<blockquote>' . $body . '</blockquote>' : ''; }
	),
	's'		=> array
	(
		'stop'	=> 'mapaDemoSpanStop'
	),
	'src'	=> array
	(
		'apply'	=> 'mapaDemoSourceApply',
	),
	'sub'	=> array
	(
		'stop'	=> 'mapaDemoSimpleStop'
	),
	'sup'	=> array
	(
		'stop'	=> 'mapaDemoSimpleStop'
	),
	'u'		=> array
	(
		'stop'	=> 'mapaDemoSpanStop',
	)
);

function	mapaDemoAnchorApply ($name, $value, $params)
{
	return mapaDemoAnchorStop ($name, $value, $params, $params[0]);
}

function	mapaDemoAnchorStop ($name, $value, $params, $body)
{
	if (!preg_match ('#^([0-9A-Za-z]+://)?(([^:@]+(:[^@]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $params[0], $matches))
		return $body;

	$href = ($matches[1] ? $matches[1] : 'http://') . $matches[2];

	return '<a href="' . htmlspecialchars ($href) . '">' . $body . '</a>';
}

function	mapaDemoColorStop ($name, $value, $params, $body)
{
	return $body ? '<span class="color' . $name . '">' . $body . '</span>' : '';
}

function	mapaDemoImageApply ($name, $value, $params)
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

	if (!preg_match ('#^([0-9A-Za-z]+://)?([-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $src, $matches))
		return $src;

	$src = htmlspecialchars (($matches[1] ? $matches[1] : 'http://') . $matches[2]);

	if ($size !== null)
		return '<a href="' . $src . '" target="_blank"><img alt="img" src="' . $src . '" onload="this.onload = null; this.width *= ' . $size . ';" /></a>';
	else
		return '<img alt="img" src="' . $src . '" />';
}

function	mapaDemoListStart ($name, $value, &$params)
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

function	mapaDemoListStep ($name, $value, &$params, $body)
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
		$params['out'] .= $body;
	}
	else
		++$params['next'];

	$params['tag'] = $value . 'l';
}

function	mapaDemoListStop ($name, $value, &$params, $body)
{
	mapaDemoListStep ($name, $value, $params, $body);

	while ($params['level']--)
		$params['out'] .= '</li></' . array_pop ($params['stack']) . '>';

	return $params['out'];
}

function	mapaDemoSimpleStop ($name, $value, $params, $body)
{
	return $body ? '<' . $name . '>' . $body . '</' . $name . '>' : '';
}

function	mapaDemoSpanStop ($name, $value, $params, $body)
{
	return $body ? '<span class="' . $name . '">' . $body . '</span>' : '';
}

function	mapaDemoSourceApply ($name, $value, $params)
{
	global	$db;

	$source = $db->getFirst ('SELECT code FROM sources WHERE id = ?', $params, null);

	if ($source !== null)
		return '<pre>' . stripslashes (gzuncompress ($source['code'])) . '</pre>';

	return '<center><b>' . $GLOBALS['_LANG_num_src'] . $params[0] . ' N/A</b></center>';
}

/*
** Missing:
** - media
** - !slap
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
** - www.
** - unicode
*/

?>
