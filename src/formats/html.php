<?php

/*
** Missing:
** - li
** - ul
*/

/*
** String format modifiers for each available tag, as name => properties
**   .alone:	optional tag alone callback, undefined if none
**   .level:	optional nesting level (a tag can only enclose tags of lower or
**				equal levels), default is 1
**   .start:	optional tag begin callback, undefined if none
**   .step:		optional tag break callback, undefined if none
**   .stop:		optional tag end callback, undefined if none
*/
$htmlFormat = array
(
	'.'		=> array
	(
		'alone'	=> function ($name, $flag, $params) { return '<br />'; }
//		'level'	=> 1,
//		'start'	=> 'umenHTMLAStart',
//		'step'	=> 'umenHTMLAStep',
//		'stop'	=> 'umenHTMLAStop'
	),
	'0'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'1'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'2'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'3'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'4'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'5'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'6'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'7'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'8'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'9'		=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'10'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'11'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'12'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'13'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'14'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'15'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'a'		=> array
	(
		'alone'	=> 'umenHTMLAnchorAlone',
		'stop'	=> 'umenHTMLAnchorStop',
	),
	'align'	=> array
	(
		'level'	=> 2,
		'start'	=> function ($name, $flag, &$params) { $align = array ('c' => 'center', 'l' => 'left', 'r' => 'right'); $params[0] = $align[$flag]; },
		'stop'	=> function ($name, $flag, $params, $body) { return $body ? '<div style="text-align: ' . $params[0] . ';">' . $body . '</div>' : ''; }
	),
	'b'		=> array
	(
		'stop'	=> 'umenHTMLTagStop'
	),
	'box'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $flag, $params, $body) { return '<div class="box box_0"><h1 onclick="this.parentNode.className = this.parentNode.className.indexOf(\'box_1\') &gt;= 0 ? \'box box_0\' : \'box box_1\';">' . $params[0] . '</h1><div>' . $body . '</div></div>'; }
	),
	'c'		=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $flag, $params, $body) { return $body ? '<div class="center">' . $body . '</div>' : ''; }
	),
	'cmd'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $flag, $params, $body) { return $body ? '<div class="cmd">' . $body . '</div>' : ''; }
	),
	'color'	=> array
	(
		'stop'	=> 'umenHTMLColorStop'
	),
	'em'	=> array
	(
		'stop'	=> 'umenHTMLTagStop'
	),
	'flash'	=> array
	(
		'alone'	=> 'umenHTMLFlashAlone'
	),
	'font'	=> array
	(
		'stop'	=> function ($name, $flag, $params, $body) { return $body ? '<span style="font-size: ' . max (min ((int)$params[0], 300), 50) . '%; line-height: 100%;">' . $body . '</span>' : ''; }
	),
	'hr'	=> array
	(
		'alone'	=> function ($name, $flag, $params) { return '<hr />'; },
		'level'	=> 2
	),
	'i'		=> array
	(
		'stop'	=> 'umenHTMLTagStop'
	),
	'img'	=> array
	(
		'alone'	=> 'umenHTMLImageAlone'
	),
	'list'	=> array
	(
		'level'	=> 2,
		'start'	=> 'umenHTMLListStart',
		'step'	=> 'umenHTMLListStep',
		'stop'	=> 'umenHTMLListStop'
	),
	'modo'	=> array
	(
		'level'	=> 2,
		'stop'	=> 'umenHTMLDivStop'
	),
	'poll'	=> array
	(
		'alone'	=> 'umenHTMLPollAlone',
		'level'	=> 2
	),
	'pre'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $flag, $params, $body) { return $body ? '<pre>' . str_replace (array ("\r\n", "\r", "\n"), '<br />', $body) . '</pre>' : ''; }
	),
	'quote'	=> array
	(
		'level'	=> 2,
		'stop'	=> function ($name, $flag, $params, $body) { return $body ? '<blockquote>' . $body . '</blockquote>' : ''; }
	),
	'ref'	=> array
	(
		'alone'	=> function ($name, $flag, $params) { return '<a href="" onclick="getPost(event, ' . 'FIXME' . ',' . $params[0] . ');return false;">./' . $params[0] . '</a>'; }
	),
	's'		=> array
	(
		'stop'	=> 'umenHTMLSpanStop'
	),
	'slap'	=> array
	(
		'alone'	=> function ($name, $flag, $params) { return '!slap ' . $params[0] . ($params[0] ? '<br /><span style="color: #990099;">&bull; FIXME slaps ' . $params[0] . ' around a bit with a large trout !</span>' : ''); }
	),
	'smile'	=> array
	(
		'alone'	=> 'umenHTMLSmileyAlone'
	),
	'spoil'	=> array
	(
		'stop'	=> 'umenHTMLSpanStop'
	),
	'src'	=> array
	(
		'alone'	=> 'umenHTMLSourceAlone',
	),
	'sub'	=> array
	(
		'stop'	=> 'umenHTMLTagStop'
	),
	'sup'	=> array
	(
		'stop'	=> 'umenHTMLTagStop'
	),
	'table'	=> array
	(
		'level'	=> 2,
		'start'	=> 'umenHTMLTableStart',
		'step'	=> 'umenHTMLTableStep',
		'stop'	=> 'umenHTMLTableStop'
	),
	'u'		=> array
	(
		'stop'	=> 'umenHTMLSpanStop',
	),
	'uni'	=> array
	(
		'alone'	=> function ($name, $flag, $params) { return '&#' . $params[0] . ';'; }
	)
);

function	umenHTMLAnchorAlone ($name, $flag, $params)
{
	return umenHTMLAnchorStop ($name, $flag, $params, $params[0]);
}

function	umenHTMLAnchorStop ($name, $flag, $params, $body)
{
	if (!preg_match ('#^([0-9A-Za-z+]+://)?(([^:@]+(:[^@]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $params[0], $matches))
		return $body;

	$href = ($matches[1] ? $matches[1] : 'http://') . $matches[2];

	return '<a href="' . $href . '">' . $body . '</a>';
}

function	umenHTMLColorStop ($name, $flag, $params, $body)
{
	if (isset ($params[0]))
		$attr = 'style="color: #' . $params[0] . ';"';
	else
		$attr = 'class="color' . $name . '"';

	return $body ? '<span ' . $attr . '>' . $body . '</span>' : '';
}

function	umenHTMLDivStop ($name, $flag, $params, $body)
{
	return $body ? '<div class="' . $name . '">' . $body . '</div>' : '';
}

function	umenHTMLFlashAlone ($name, $flag, $params)
{
	if (isset ($params[0]) && isset ($params[1]))
	{
		$size = array (max (min ((int)$params[0], 1024), 32), max (min ((int)$params[1], 1024), 32));
		$url = $params[2];
	}
	else
	{
		$size = array (550, 400);
		$url = $params[0];
	}

	if (!preg_match ('#^([0-9A-Za-z+]+://)?(([^:@]+(:[^@]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $url, $matches))
		return '';

	// data="ADRESSE"
	return '<object type="application/x-shockwave-flash" width="' . $size[0] . '" height="' . $size[1] . '"><param name="movie" value="' . (($matches[1] ? $matches[1] : 'http://') . $matches[2]) . '" /><param name="allowFullScreen" value="true" /></object>';
}

function	umenHTMLImageAlone ($name, $flag, $params)
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

	if (!preg_match ('#^([0-9A-Za-z+]+://)?([-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $src, $matches))
		return $src;

	$src = ($matches[1] ? $matches[1] : 'http://') . $matches[2];

	if ($size !== null)
		return '<a href="' . $src . '" target="_blank"><img alt="img" src="' . $src . '" onload="this.onload = null; this.width *= ' . $size . ';" /></a>';
	else
		return '<img alt="img" src="' . $src . '" />';
}

function	umenHTMLListStart ($name, $flag, &$params)
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

function	umenHTMLListStep ($name, $flag, &$params, $body)
{
	$body = trim ($body);

	if ($params['tag'] !== '' && $body)
	{
		for (; $params['level'] > $params['next']; --$params['level'])
			$params['out'] .= '</li></' . array_pop ($params['stack']) . '>';

		if ($params['level'] == $params['next'])
			$params['out'] .= '</li><li>';

		for (; $params['level'] < $params['next']; ++$params['level'])
			$params['out'] .= '<' . ($params['stack'][] = $params['tag']) . '><li>';

		$params['next'] = 1;
	}
	else
		$params['next'] = min ($params['next'] + 1, 8);

	$params['out'] .= $body;
	$params['tag'] = $flag . 'l';
}

function	umenHTMLListStop ($name, $flag, $params, $body)
{
	umenHTMLListStep ($name, $flag, $params, $body);

	while ($params['level']--)
		$params['out'] .= '</li></' . array_pop ($params['stack']) . '>';

	return $params['out'];
}

function	umenHTMLPollAlone ($name, $flag, $params)
{
	$s = (int)$params[0];

	include ("sond.php");

	return $sondINC;
}

function	umenHTMLSmileyAlone ($name, $flag, $params)
{
	static	$natives;

	switch ($flag)
	{
		case '0':
			$alt = ':D';
			$src = 'res/n/biggrin.gif';

			break;

		case '1':
			$alt = ':(';
			$src = 'res/n/frown.gif';

			break;

		case '2':
			$alt = ':o';
			$src = 'res/n/redface.gif';

			break;

		case '3':
			$alt = ':)';
			$src = 'res/n/smile.gif';

			break;

		case '4':
			$alt = ':p';
			$src = 'res/n/tongue.gif';

			break;

		case '5':
			$alt = ';)';
			$src = 'res/n/wink.gif';

			break;

		case '6':
			$alt = '=)';
			$src = 'res/n/smile2.gif';

			break;

		case '7':
			$alt = '%)';
			$src = 'res/n/mod.gif';

			break;

		case '8':
			$alt = ':|';
			$src = 'res/n/droit.gif';

			break;

		case '9':
			$alt = ':S';
			$src = 'res/n/cst.gif';

			break;

		case 'c':
			$alt = $params[0];
			$src = 'res/c/' . $params[0] . '.gif';

			if (!file_exists ($src))
				return '##' . $params[0] . '##';

			break;

		case 'n':
			if (!isset ($natives))
			{
				$natives = array_flip (array
				(
					'bang', 'eek', 'confus', 'cool', 'roll', 'rage', 'alien', 'attention', 'vador', 'crayon', 'devil', 'doom', 'picol', 'vtff', 'mad', 'rotfl', 'zzz', 'miam', 'tsss', 'sick', 'pleure', 'oui', 'fou', 'love', 'tusors', 'triso', 'top', 'hum', 'black', 'coeur', 'hein', 'interdit', 'gni', 'couic', 'fuck', 'gol', 'grrr', 'magic', 'non', 'bisoo', 'coin', 'tp', 'fleurs', 'wc', 'lapin', 'poulpe', 'info', 'tv', 'doc', 'skull', 'mur', 'pam', 'dehors', 'tusors', 'chew', 'lol', 'boing', 'yel', 'biz', 'cyborg', 'chinois', 'calin', 'censure', 'scotch',
					'anniv', 'arme', 'aveugle', 'banane', 'bandana', 'beret', 'blabla', 'bobo', 'bonbon', 'bourre', 'bulle', 'bzz', 'camouflage', 'car', 'casque', 'champignon', 'chante', 'chapo', 'chat', 'chausson', 'citrouille', 'classe', 'cle', 'cookie', 'coupe', 'cowboy', 'croque', 'cubiste', 'cuisse', 'diable', 'dingue', 'donut', 'drapeau', 'ecoute', 'eeek', 'enflamme', 'epee', 'fantome', 'fatigue', 'fesses', 'feu', 'fille', 'flic', 'flocon', 'fondu', 'fou2', 'fouet', 'froid', 'furieux', 'groupe', 'guitare', 'helico', 'hippy', 'hypno', 'interdit2', 'karate', 'king', 'krokro', 'langue', 'livre', 'lolpaf', 'loupe', 'love2', 'lune', 'marteau', 'masque', 'micro', 'mimi', 'note', 'peur', 'piano', 'pluie', 'pomme', 'reine', 'santa', 'sapin', 'saucisse', 'shhh', 'skate', 'slug', 'snail', 'snowman', 'soda', 'soleil', 'splat', 'starwars', 'stylo', 'stylobille', 'superguerrier', 'surf', 'swirl', 'tasse', 'tilt', 'toilettes', 'tomate', 'tombe', 'tompette', 'tortue', 'trefle', 'warp', 'yoyo', 'zen',
					'ciao', 'crash', 'drapeaublanc', 'fou3', 'fucktricol', 'ouin', 'slurp', 'sygus', 'hum2', 'fireball', 'tricol', 'trifaq', 'trigic', 'trigni', 'trilol', 'trilove', 'trinon', 'tripo', 'trisors', 'trisotfl', 'tritop', 'trivil', 'trifouet', 'trifus', 'trilangue', 'triroll', 'couic2', 'faq', 'furax', 'ooh', 'bigeyes', 'civ3', 'ptw', 'fear', 'hehe', 'fleche', 'tripaf', 'gnimod', 'trioui', 'sheep', 'tromb',
					'biere', 'citrouille2', 'foot', 'gato', 'hotdog', 'kado', 'cornet', 'meuh', 'mobile', 'pizza', 'poisson', 'yin'
				));
			}

			if (!isset ($natives[$params[0]]))
				return '#' . $params[0] . '#';

			$alt = $params[0];
			$src = 'res/n/' . $params[0] . '.gif';

			break;
	}

	return '<img alt="' . $alt . '" src="' . $src . '" />';
}

function	umenHTMLSourceAlone ($name, $flag, $params)
{
	global	$db;

	$source = $db->getFirst ('SELECT code FROM sources WHERE id = ?', $params, null);

	if ($source !== null)
		return '<pre>' . stripslashes (gzuncompress ($source['code'])) . '</pre>';

	return '<center><b>' . $GLOBALS['_LANG_num_src'] . $params[0] . ' N/A</b></center>';
}

function	umenHTMLSpanStop ($name, $flag, $params, $body)
{
	return $body ? '<span class="' . $name . '">' . $body . '</span>' : '';
}

function	umenHTMLTableStart ($name, $flag, &$params)
{
	$params = $params + array
	(
		'cols'	=> 0,
		'head'	=> false,
		'rows'	=> array (array ()),
		'size'	=> 0,
		'span'	=> 0
	);
}

function	umenHTMLTableStep ($name, $flag, &$params, $body)
{
	if ($body !== '')
	{
		$span = max ($params['span'], 1);

		$params['rows'][count ($params['rows']) - 1][] = array ($params['head'], $span, $body);
		$params['size'] += $span;
		$params['span'] = 1;
	}
	else
		++$params['span'];

	switch ($flag)
	{
		case 'c':
			$params['head'] = false;

			break;

		case 'h':
			$params['head'] = true;

			break;

		case 'r':
			$params['cols'] = max ($params['cols'], $params['size']);
			$params['head'] = false;
			$params['rows'][] = array ();
			$params['size'] = 0;
			$params['span'] = 0;

			break;
	}
}

function	umenHTMLTableStop ($name, $flag, $params, $body)
{
	umenHTMLTableStep ($name, $flag, $params, $body);

	$rows = '';

	foreach ($params['rows'] as $row)
	{
		$rows .= '<tr>';
		$i = 0;

		foreach ($row as $cell)
		{
			$span = $cell[1];
			$tag = $cell[0] ? 'th' : 'td';
			$text = $cell[2];

			$al = substr ($text, -2) === '  ';
			$ar = substr ($text, 0, 2) === '  ';

			if ($al && $ar)
				$align = 'center';
			else if ($al)
				$align = 'left';
			else if ($ar)
				$align = 'right';
			else
				$align = '';

			$rows .=
				'<' . $tag . ($span > 1 ? ' colspan="' . $span . '"' : '') . ($align !== '' ? (' style="text-align: ' . $align . ';">') : '>') .
				preg_replace ('#^[[:blank:]]*(<br />)?(.*)(<br />)?[[:blank:]]*$#', '$2', $text) .
				'</' . $tag . '>';

			$i += $span;
		}

		if ($i < $params['cols'])
			$rows .= '<td ' . ($params['cols'] > $i + 1 ? ' colspan="' . ($params['cols'] - $i) . '"' : '') . '></td>';

		$rows .= '</tr>';
	}

	return '<table>' . $rows . '</table>';
}

function	umenHTMLTagStop ($name, $flag, $params, $body)
{
	return $body ? '<' . $name . '>' . $body . '</' . $name . '>' : '';
}

?>
