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
//		'level'		=> 1,
//		'limit'		=> 100,
		'single'	=> function ($name, $value, $params) { return $params[0]; }
//		'start'		=> 'mapaHTMLAStart',
//		'step'		=> 'mapaHTMLAStep',
//		'stop'		=> 'mapaHTMLAStop'
	),
	'.'		=> array
	(
		'single'	=> function ($name, $value, $params) { return '<br />'; }
	),
	'0'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'1'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'2'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'3'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'4'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'5'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'6'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'7'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'8'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'9'		=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'10'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'11'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'12'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'13'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'14'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'15'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'a'		=> array
	(
		'single'	=> 'mapaHTMLAnchorSingle',
		'stop'		=> 'mapaHTMLAnchorStop',
	),
	'align'	=> array
	(
		'level'	=> 2,
		'start'	=> function ($name, $value, &$params) { $align = array ('c' => 'center', 'l' => 'left', 'r' => 'right'); $params[0] = $align[$value]; },
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<div style="text-align: ' . $params[0] . ';">' . $body . '</div>' : ''; }
	),
	'b'		=> array
	(
		'stop'	=> 'mapaHTMLTagStop'
	),
	'box'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $value, $params, $body) { return '<div class="box box_0"><h1 onclick="this.parentNode.className = this.parentNode.className.indexOf(\'box_1\') &gt;= 0 ? \'box box_0\' : \'box box_1\';">' . $params[0] . '</h1><div>' . $body . '</div></div>'; }
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
	'color'	=> array
	(
		'stop'	=> 'mapaHTMLColorStop'
	),
	'em'	=> array
	(
		'stop'	=> 'mapaHTMLTagStop'
	),
	'font'	=> array
	(
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<span style="font-size: ' . max (min ((int)$params[0], 300), 50) . '%; line-height: 100%;">' . $body . '</span>' : ''; }
	),
	'goog'	=> array
	(
		'single'	=> function ($name, $value, $params) { return $params[0] ? '<b><span class="google1">G</span><span class="google2">o</span><span class="google3">o</span><span class="google1">g</span><span class="google4">l</span><span class="color4">e</span> :</b> <a href="http://www.google.fr/search?hl=fr&amp;ie=UTF-8&amp;oe=UTF-8&amp;q=' . rawurlencode ($params[0]) . '&amp;meta=lr%3Dlang_fr" target="_blank">' . $params[0] . '</a>' : ''; }
	),
	'hr'	=> array
	(
		'level'		=> 2,
		'single'	=> function ($name, $value, $params) { return '<hr />'; },
	),
	'i'		=> array
	(
		'stop'	=> 'mapaHTMLTagStop'
	),
	'img'	=> array
	(
		'limit'		=> 50,
		'single'	=> 'mapaHTMLImageSingle'
	),
	'list'	=> array
	(
		'level'	=> 2,
		'start'	=> 'mapaHTMLListStart',
		'step'	=> 'mapaHTMLListStep',
		'stop'	=> 'mapaHTMLListStop'
	),
	'modo'	=> array
	(
		'level'	=> 2,
		'stop'	=> 'mapaHTMLDivStop'
	),
	'pre'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<pre>' . str_replace (array ("\r\n", "\r", "\n"), '<br />', $body) . '</pre>' : ''; }
	),
	'quote'	=> array
	(
		'level'	=> 2,
		'limit'	=> 8,
		'stop'	=> function ($name, $value, $params, $body) { return $body ? '<blockquote>' . $body . '</blockquote>' : ''; }
	),
	'ref'	=> array
	(
		'single'	=> function ($name, $value, $params) { return '<a href="" onclick="getPost(event, ' . 'FIXME' . ',' . $params[0] . ');return false;">./' . $params[0] . '</a>'; }
	),
	's'		=> array
	(
		'stop'	=> 'mapaHTMLSpanStop'
	),
/*	'slap'	=> array
	(
		'single'	=> function ($name, $value, $params) { return '!slap ' . $params[0] . ($params[0] ? '<br /><span style="color: #990099;">&bull; FIXME slaps ' . $params[0] . ' around a bit with a large trout !</span>' : ''); }
	),*/
	'spoil'	=> array
	(
		'stop'	=> 'mapaHTMLSpanStop'
	),
	'src'	=> array
	(
		'single'	=> 'mapaHTMLSourceSingle',
	),
	'sub'	=> array
	(
		'stop'	=> 'mapaHTMLTagStop'
	),
	'sup'	=> array
	(
		'stop'	=> 'mapaHTMLTagStop'
	),
	'u'		=> array
	(
		'stop'	=> 'mapaHTMLSpanStop',
	)
);

function	mapaHTMLAnchorSingle ($name, $value, $params)
{
	return mapaHTMLAnchorStop ($name, $value, $params, $params[0]);
}

function	mapaHTMLAnchorStop ($name, $value, $params, $body)
{
	if (!preg_match ('#^([0-9A-Za-z]+://)?(([^:@]+(:[^@]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $params[0], $matches))
		return $body;

	$href = ($matches[1] ? $matches[1] : 'http://') . $matches[2];

	return '<a href="' . $href . '">' . $body . '</a>';
}

function	mapaHTMLColorStop ($name, $value, $params, $body)
{
	if (isset ($params[0]))
		$attr = 'style="color: #' . $params[0] . ';"';
	else
		$attr = 'class="color' . $name . '"';

	return $body ? '<span ' . $attr . '>' . $body . '</span>' : '';
}

function	mapaHTMLDivStop ($name, $value, $params, $body)
{
	return $body ? '<div class="' . $name . '">' . $body . '</div>' : '';
}

function	mapaHTMLImageSingle ($name, $value, $params)
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

	$src = ($matches[1] ? $matches[1] : 'http://') . $matches[2];

	if ($size !== null)
		return '<a href="' . $src . '" target="_blank"><img alt="img" src="' . $src . '" onload="this.onload = null; this.width *= ' . $size . ';" /></a>';
	else
		return '<img alt="img" src="' . $src . '" />';
}

function	mapaHTMLListStart ($name, $value, &$params)
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

function	mapaHTMLListStep ($name, $value, &$params, $body)
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

function	mapaHTMLListStop ($name, $value, &$params, $body)
{
	mapaHTMLListStep ($name, $value, $params, $body);

	while ($params['level']--)
		$params['out'] .= '</li></' . array_pop ($params['stack']) . '>';

	return $params['out'];
}

function	mapaHTMLSourceSingle ($name, $value, $params)
{
	global	$db;

	$source = $db->getFirst ('SELECT code FROM sources WHERE id = ?', $params, null);

	if ($source !== null)
		return '<pre>' . stripslashes (gzuncompress ($source['code'])) . '</pre>';

	return '<center><b>' . $GLOBALS['_LANG_num_src'] . $params[0] . ' N/A</b></center>';
}

function	mapaHTMLSpanStop ($name, $value, $params, $body)
{
	return $body ? '<span class="' . $name . '">' . $body . '</span>' : '';
}

function	mapaHTMLTagStop ($name, $value, $params, $body)
{
	return $body ? '<' . $name . '>' . $body . '</' . $name . '>' : '';
}

/*
** Missing:
** - !slap
** - name@domain.com
** - smiley
** - nosmile
** - table
** - itable
** - li
** - ul
** - code
** - sp
** - flash
** - sondage
** - www.
** - unicode
*/

?>
