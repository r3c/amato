<?php

/*
** Missing:
** - li
** - ul
*/

/*
** String format modifiers for each available tag, as name => properties
**   .level:	optional nesting level (a tag can only enclose tags of lower or
**				equal levels), default is 1
**   .onAlone:	optional tag alone callback, undefined if none
**   .onStart:	optional tag begin callback, undefined if none
**   .onStep:	optional tag break callback, undefined if none
**   .onStop:	optional tag end callback, undefined if none
*/
$htmlFormat = array
(
	'.'		=> array
	(
//		'level'		=> 1,
		'onAlone'	=> 'umenHTMLNewLineAlone'
//		'onStart'	=> 'umenHTMLNewLineStart',
//		'onStep'	=> 'umenHTMLNewLineStep',
//		'onStop'	=> 'umenHTMLNewLinetop'
	),
	'0'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'1'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'2'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'3'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'4'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'5'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'6'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'7'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'8'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'9'		=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'10'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'11'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'12'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'13'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'14'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'15'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'a'		=> array
	(
		'onAlone'	=> 'umenHTMLAnchorAlone',
		'onStop'	=> 'umenHTMLAnchorStop',
	),
	'align'	=> array
	(
		'level'		=> 2,
		'onStart'	=> 'umenHTMLAlignStart',
		'onStop'	=> 'umenHTMLAlignStop'
	),
	'b'		=> array
	(
		'onStop'	=> 'umenHTMLTagStop'
	),
	'box'	=> array
	(
		'level'		=> 2,
		'onStop'	=> 'umenHTMLBoxStop'
	),
	'c'		=> array
	(
		'level'		=> 2,
		'onStop'	=> 'umenHTMLCenterStop'
	),
	'cmd'	=> array
	(
		'level'		=> 2,
		'onStop'	=> 'umenHTMLCommandStop'
	),
	'color'	=> array
	(
		'onStop'	=> 'umenHTMLColorStop'
	),
	'em'	=> array
	(
		'onStop'	=> 'umenHTMLTagStop'
	),
	'flash'	=> array
	(
		'onAlone'	=> 'umenHTMLFlashAlone'
	),
	'font'	=> array
	(
		'onStop'	=> 'umenHTMLFontStop'
	),
	'hr'	=> array
	(
		'level'		=> 2,
		'onAlone'	=> 'umenHTMLHorizontalAlone'
	),
	'i'		=> array
	(
		'onStop'	=> 'umenHTMLTagStop'
	),
	'img'	=> array
	(
		'onAlone'	=> 'umenHTMLImageAlone'
	),
	'list'	=> array
	(
		'level'		=> 2,
		'onStart'	=> 'umenHTMLListStart',
		'onStep'	=> 'umenHTMLListStep',
		'onStop'	=> 'umenHTMLListStop'
	),
	'modo'	=> array
	(
		'level'		=> 2,
		'onStop'	=> 'umenHTMLDivStop'
	),
	'poll'	=> array
	(
		'level'		=> 2,
		'onAlone'	=> 'umenHTMLPollAlone'
	),
	'pre'	=> array
	(
		'level'		=> 2,
		'onStop'	=> 'umenHTMLPreStop'
	),
	'quote'	=> array
	(
		'level'		=> 2,
		'onStop'	=> 'umenHTMLQuoteStop'
	),
	'ref'	=> array
	(
		'onAlone'	=> 'umenHTMLRefStop'
	),
	's'		=> array
	(
		'onStop'	=> 'umenHTMLSpanStop'
	),
	'slap'	=> array
	(
		'onAlone'	=> 'umenHTMLSlapStop'
	),
	'smile'	=> array
	(
		'onAlone'	=> 'umenHTMLSmileyAlone'
	),
	'spoil'	=> array
	(
		'onStop'	=> 'umenHTMLSpanStop'
	),
	'src'	=> array
	(
		'onStop'	=> 'umenHTMLSourceStop',
	),
	'sub'	=> array
	(
		'onStop'	=> 'umenHTMLTagStop'
	),
	'sup'	=> array
	(
		'onStop'	=> 'umenHTMLTagStop'
	),
	'table'	=> array
	(
		'level'		=> 2,
		'onStart'	=> 'umenHTMLTableStart',
		'onStep'	=> 'umenHTMLTableStep',
		'onStop'	=> 'umenHTMLTableStop'
	),
	'u'		=> array
	(
		'onStop'	=> 'umenHTMLSpanStop',
	),
	'uni'	=> array
	(
		'onAlone'	=> 'umenHTMLUnicodeAlone'
	),
	'yt'	=> array
	(
		'onAlone'	=> 'umenHTMLYoutubeAlone'
	)
);

function	umenHTMLAlignStart ($name, $flag, &$params)
{
	$align = array ('c' => 'center', 'l' => 'left', 'r' => 'right');

	$params[0] = $align[$flag];
}

function	umenHTMLAlignStop ($name, $flag, $params, $body)
{
	return $body ? '<div style="text-align: ' . $params[0] . ';">' . $body . '</div>' : '';
}

function	umenHTMLAnchorAlone ($name, $flag, $params)
{
	return umenHTMLAnchorStop ($name, $flag, $params, $params['u']);
}

function	umenHTMLAnchorStop ($name, $flag, $params, $body)
{
	if (!preg_match ('#^([0-9A-Za-z+]+://)?(([^:@]+(:[^@]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $params['u'], $matches))
		return $body;

	$href = ($matches[1] ? $matches[1] : 'http://') . $matches[2];

	return '<a href="' . $href . '">' . $body . '</a>';
}

function	umenHTMLBoxStop ($name, $flag, $params, $body)
{
	return '<div class="box box_0"><h1 onclick="this.parentNode.className = this.parentNode.className.indexOf(\'box_1\') &gt;= 0 ? \'box box_0\' : \'box box_1\';">' . $params['t'] . '</h1><div>' . $body . '</div></div>';
}

function	umenHTMLCenterStop ($name, $flag, $params, $body)
{
	return $body ? '<div class="center">' . $body . '</div>' : '';
}

function	umenHTMLColorStop ($name, $flag, $params, $body)
{
	if (isset ($params['h']))
		$attr = 'style="color: #' . $params['h'] . ';"';
	else
		$attr = 'class="color' . $name . '"';

	return $body ? '<span ' . $attr . '>' . $body . '</span>' : '';
}

function	umenHTMLCommandStop ($name, $flag, $params, $body)
{
	return $body ? '<div class="cmd">' . $body . '</div>' : '';
}

function	umenHTMLDivStop ($name, $flag, $params, $body)
{
	return $body ? '<div class="' . $name . '">' . $body . '</div>' : '';
}

function	umenHTMLFlashAlone ($name, $flag, $params)
{
	if (isset ($params['x']) && isset ($params['y']))
	{
		$size = array (max (min ((int)$params['x'], 1024), 32), max (min ((int)$params['y'], 1024), 32));
		$url = $params['u'];
	}
	else
	{
		$size = array (550, 400);
		$url = $params['u'];
	}

	if (!preg_match ('#^([0-9A-Za-z+]+://)?(([^:@]+(:[^@]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $url, $matches))
		return '';

	// data="ADRESSE"
	return '<object type="application/x-shockwave-flash" width="' . $size[0] . '" height="' . $size[1] . '"><param name="movie" value="' . (($matches[1] ? $matches[1] : 'http://') . $matches[2]) . '" /><param name="allowFullScreen" value="true" /></object>';
}

function	umenHTMLFontStop ($name, $flag, $params, $body)
{
	return $body ? '<span style="font-size: ' . max (min ((int)$params['p'], 300), 50) . '%; line-height: 100%;">' . $body . '</span>' : '';
}

function	umenHTMLHorizontalAlone ($name, $flag, $params)
{
	return '<hr />';
}

function	umenHTMLImageAlone ($name, $flag, $params)
{
	if (isset ($params['p']))
	{
		$size = round (max (min (intval ($params['p']), 200), 20) * 0.01, 2);
		$src = $params['u'];
	}
	else
	{
		$size = null;
		$src = $params['u'];
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

function	umenHTMLNewLineAlone ($name, $flag, $params)
{
	return '<br />';
}

function	umenHTMLPollAlone ($name, $flag, $params)
{
	$s = (int)$params['i'];

	include ("sond.php");

	return $sondINC;
}

function	umenHTMLPreStop ($name, $flag, $params, $body)
{
	return $body ? '<pre>' . str_replace (array ("\r\n", "\r", "\n"), '<br />', $body) . '</pre>' : '';
}

function	umenHTMLQuoteStop ($name, $flag, $params, $body)
{
	return $body ? '<blockquote>' . $body . '</blockquote>' : '';
}

function	umenHTMLRefStop ($name, $flag, $params)
{
	return '<a href="" onclick="getPost(event, ' . (int)$GLOBALS['s'] . ', ' . $params['n'] . '); return false;">./' . $params['n'] . '</a>';
}

function	umenHTMLSlapStop ($name, $flag, $params)
{
	global	$config, $mbI;

	$login = htmlspecialchars ((isset ($mbI) && isset ($mbI['login']) ? $mbI['login'] : '?'), ENT_COMPAT, $config['render.charset']);

	return '!slap ' . $params['u'] . ($params['u'] ? '<br /><span style="color: #990099;">&bull; ' . $login . ' slaps ' . $params['u'] . ' around a bit with a large trout !</span><br />' : '');
}

function	umenHTMLSmileyAlone ($name, $flag, $params)
{
	global	$config;
	static	$names;

	switch ($flag)
	{
		case '0':
			$alt = ':D';
			$src = $config['static.url'] . '/sprite/smile/biggrin.gif';

			break;

		case '1':
			$alt = ':(';
			$src = $config['static.url'] . '/sprite/smile/frown.gif';

			break;

		case '2':
			$alt = ':o';
			$src = $config['static.url'] . '/sprite/smile/redface.gif';

			break;

		case '3':
			$alt = ':)';
			$src = $config['static.url'] . '/sprite/smile/smile.gif';

			break;

		case '4':
			$alt = ':p';
			$src = $config['static.url'] . '/sprite/smile/tongue.gif';

			break;

		case '5':
			$alt = ';)';
			$src = $config['static.url'] . '/sprite/smile/wink.gif';

			break;

		case '6':
			$alt = '=)';
			$src = $config['static.url'] . '/sprite/smile/smile2.gif';

			break;

		case '7':
			$alt = '%)';
			$src = $config['static.url'] . '/sprite/smile/mod.gif';

			break;

		case '8':
			$alt = ':|';
			$src = $config['static.url'] . '/sprite/smile/droit.gif';

			break;

		case '9':
			$alt = ':S';
			$src = $config['static.url'] . '/sprite/smile/cst.gif';

			break;

		case 'c':
			$alt = $params['n'];
			$src = 'sp/img/' . $alt . '.img';

			if (!file_exists ($src))
				return '##' . $alt . '##';

			break;

		case 'n':
			if (!isset ($names))
			{
				$names = array_flip (array
				(
					'bang', 'eek', 'confus', 'cool', 'roll', 'rage', 'alien', 'attention', 'vador', 'crayon', 'devil', 'doom', 'picol', 'vtff', 'mad', 'rotfl', 'zzz', 'miam', 'tsss', 'sick', 'pleure', 'oui', 'fou', 'love', 'tusors', 'triso', 'top', 'hum', 'black', 'coeur', 'hein', 'interdit', 'gni', 'couic', 'fuck', 'gol', 'grrr', 'magic', 'non', 'bisoo', 'coin', 'tp', 'fleurs', 'wc', 'lapin', 'poulpe', 'info', 'tv', 'doc', 'skull', 'mur', 'pam', 'dehors', 'tusors', 'chew', 'lol', 'boing', 'yel', 'biz', 'cyborg', 'chinois', 'calin', 'censure', 'scotch',
					'anniv', 'arme', 'aveugle', 'banane', 'bandana', 'beret', 'blabla', 'bobo', 'bonbon', 'bourre', 'bulle', 'bzz', 'camouflage', 'car', 'casque', 'champignon', 'chante', 'chapo', 'chat', 'chausson', 'citrouille', 'classe', 'cle', 'cookie', 'coupe', 'cowboy', 'croque', 'cubiste', 'cuisse', 'diable', 'dingue', 'donut', 'drapeau', 'ecoute', 'eeek', 'enflamme', 'epee', 'fantome', 'fatigue', 'fesses', 'feu', 'fille', 'flic', 'flocon', 'fondu', 'fou2', 'fouet', 'froid', 'furieux', 'groupe', 'guitare', 'helico', 'hippy', 'hypno', 'interdit2', 'karate', 'king', 'krokro', 'langue', 'livre', 'lolpaf', 'loupe', 'love2', 'lune', 'marteau', 'masque', 'micro', 'mimi', 'note', 'peur', 'piano', 'pluie', 'pomme', 'reine', 'santa', 'sapin', 'saucisse', 'shhh', 'skate', 'slug', 'snail', 'snowman', 'soda', 'soleil', 'splat', 'starwars', 'stylo', 'stylobille', 'superguerrier', 'surf', 'swirl', 'tasse', 'tilt', 'toilettes', 'tomate', 'tombe', 'tompette', 'tortue', 'trefle', 'warp', 'yoyo', 'zen',
					'ciao', 'crash', 'drapeaublanc', 'fou3', 'fucktricol', 'ouin', 'slurp', 'sygus', 'hum2', 'fireball', 'tricol', 'trifaq', 'trigic', 'trigni', 'trilol', 'trilove', 'trinon', 'tripo', 'trisors', 'trisotfl', 'tritop', 'trivil', 'trifouet', 'trifus', 'trilangue', 'triroll', 'couic2', 'faq', 'furax', 'ooh', 'bigeyes', 'civ3', 'ptw', 'fear', 'hehe', 'fleche', 'tripaf', 'gnimod', 'trioui', 'sheep', 'tromb',
					'biere', 'citrouille2', 'foot', 'gato', 'hotdog', 'kado', 'cornet', 'meuh', 'mobile', 'pizza', 'poisson', 'yin'
				));
			}

			$alt = $params['n'];
			$src = $config['static.url'] . '/sprite/smile/' . $alt . '.gif';

			if (!isset ($names[$alt]))
				return '#' . $alt . '#';

			break;
	}

	return '<img alt="' . $alt . '" src="' . $src . '" />';
}

function	umenHTMLSourceStop ($name, $flag, $params, $body)
{
	static	$brushes;
	global	$includeSH;
/*
	global	$config;
	global	$db;

	if (is_numeric ($params[0]))
	{
		$source = $db->getFirst ('SELECT code FROM sources WHERE id = ?', $params, null);

		if ($source !== null)
			return '<pre>' . stripslashes (gzuncompress ($source['code'])) . '</pre>';

		return '<center><b>' . $GLOBALS['_LANG_num_src'] . $params[0] . ' N/A</b></center>';
	}

	return '<a href="#" onclick="popup(\'source.php?s=' . (int)$params[0] . '\', \'800\', \'600\'); return false;"><img align="middle" alt="source" src="' . $config['static.url'] . '/sprite/source.gif">Source $source[type]</a>
*/
	if (!isset ($brushes))
		$brushes = array_flip (array ('as3', 'bash', 'csharp', 'c', 'cpp', 'css', 'delphi', 'diff', 'groovy', 'js', 'java', 'jfx', 'm68k', 'perl', 'php', 'plain', 'ps', 'py', 'rails', 'scala', 'sql', 'vb', 'xml'));

	if (isset ($brushes[$params['l']]))
	{
		$includeSH = true;

		return '<pre class="brush: ' . $params['l'] . '">' . str_replace ('<br />', "\n", $body) . '</pre>';
	}

	return $body;
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
		'span'	=> 1
	);
}

function	umenHTMLTableStep ($name, $flag, &$params, $body)
{
	$body = preg_replace ('#^([[:blank:]]*)(<br />)?(.*)(<br />)?([[:blank:]]*)$#', '$1$3$5', $body);

	if ($params['span'] === 1 && trim ($body) === '')
		;
	else if ($body === '')
		++$params['span'];
	else
	{
		$span = $params['span'];

		$params['rows'][count ($params['rows']) - 1][] = array ($params['head'], $span, $body);
		$params['size'] += $span;
		$params['span'] = 1;
	}

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
			$params['span'] = 1;

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
				$text .
				'</' . $tag . '>';

			$i += $span;
		}

		if ($i < $params['cols'])
			$rows .= '<td ' . ($params['cols'] > $i + 1 ? ' colspan="' . ($params['cols'] - $i) . '"' : '') . '></td>';

		$rows .= '</tr>';
	}

	return '<table class="table">' . $rows . '</table>';
}

function	umenHTMLTagStop ($name, $flag, $params, $body)
{
	return $body ? '<' . $name . '>' . $body . '</' . $name . '>' : '';
}

function	umenHTMLUnicodeAlone ($name, $flag, $params)
{
	return '&#' . $params['c'] . ';';
}

function	umenHTMLYoutubeAlone ($name, $flag, $params)
{
	return '<iframe class="youtube-player" type="text/html" width="640" height="385" src="http://www.youtube.com/embed/' . rawurlencode ($params['i']) . '" frameborder="0"></iframe>';
}

?>
