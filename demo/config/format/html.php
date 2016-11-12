<?php

$format = array
(
	'.'		=> array ('amato_format_html_newline'),
	'a'		=> array ('amato_format_html_anchor'),
	'align'	=> array ('amato_format_html_align', 2),
	'b'		=> array (_amato_format_html_tag ('b')),
	'box'	=> array ('amato_format_html_box', 2),
	'c'		=> array ('amato_format_html_center', 2),
	'src'	=> array ('amato_format_html_code', 2),
	'cmd'	=> array (_amato_format_html_class ('div', 'cmd'), 2),
	'color'	=> array ('amato_format_html_color'),
	'em'	=> array (_amato_format_html_tag ('em')),
	'font'	=> array ('amato_format_html_font'),
	'hr'	=> array ('amato_format_html_horizontal', 2),
	'i'		=> array (_amato_format_html_tag ('i')),
	'img'	=> array ('amato_format_html_image'),
	'list'	=> array ('amato_format_html_list', 2),
	'modo'	=> array (_amato_format_html_class ('div', 'modo'), 2),
	'pre'	=> array ('amato_format_html_pre', 2),
	'quote'	=> array (_amato_format_html_tag ('blockquote'), 2),
	'ref'	=> array ('amato_format_html_ref'),
	's'		=> array (_amato_format_html_class ('span', 's')),
	'slap'	=> array ('amato_format_html_slap'),
	'smile'	=> array ('amato_format_html_smiley'),
	'spoil'	=> array (_amato_format_html_class ('span', 'spoil')),
	'sub'	=> array (_amato_format_html_tag ('sub')),
	'sup'	=> array (_amato_format_html_tag ('sup')),
	'table'	=> array ('amato_format_html_table', 2),
	'u'		=> array (_amato_format_html_class ('span', 'u'))
);

function _amato_format_html_class ($id, $name)
{
	return function ($params, $markup, $closing) use ($id, $name)
	{
		if ($markup === '')
			return '';

		return '<' . $id . ' class="' . $name . '">' . $markup . '</' . $id . '>';
	};
}

function _amato_format_html_tag ($id)
{
	return function ($params, $markup, $closing) use ($id)
	{
		if ($markup === '')
			return '';

		return '<' . $id . '>' . $markup . '</' . $id . '>';
	};
}

function _amato_format_html_escape ($string)
{
	return htmlspecialchars ($string, ENT_COMPAT, CHARSET);
}

function amato_format_html_align ($params, $markup, $closing)
{
	$align = array ('c' => 'center', 'r' => 'right');

	return '<div style="text-align: ' . _amato_format_html_escape (isset ($align[$params['w']]) ? $align[$params['w']] : 'left') . ';">' . $markup . '</div>';
}

function amato_format_html_anchor ($params, $markup, $closing)
{
	if (!preg_match ('#^([-+.0-9A-Za-z]+://)?(([^:@]+(:[0-9]+)?@)?[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+.*)#', $params['u'], $matches))
		return $markup;

	$target = _amato_format_html_escape (isset ($params['i']) ? '_self' : '_blank');
	$url = _amato_format_html_escape (($matches[1] ? $matches[1] : 'http://') . $matches[2]);

	return '<a href="' . $url . '" target="' . $target . '">' . ($markup ?: $url) . '</a>';
}

function amato_format_html_box ($params, $markup, $closing)
{
	return '<div class="box box_0"><h1 onclick="this.parentNode.className = this.parentNode.className.indexOf(\'box_1\') &gt;= 0 ? \'box box_0\' : \'box box_1\';">' . _amato_format_html_escape ($params['t']) . '</h1><div>' . $markup . '</div></div>';
}

function amato_format_html_center ($params, $markup, $closing)
{
	return '<div class="center">' . $markup . '</div>';
}

function amato_format_html_code ($params, $markup, $closing)
{
	static $brushes;

	if (!isset ($brushes))
		$brushes = array_flip (array ('as3', 'bash', 'csharp', 'c', 'cpp', 'css', 'delphi', 'diff', 'groovy', 'js', 'java', 'jfx', 'm68k', 'perl', 'php', 'plain', 'ps', 'py', 'rails', 'scala', 'sql', 'vb', 'xml'));

	if (!isset ($brushes[$params['l']]))
		return $markup;

	return '<pre class="brush: ' . $params['l'] . '">' . str_replace ('<br />', "\n", $markup) . '</pre>';
}

function amato_format_html_color ($params, $markup, $closing)
{
	return '<span style="color: #' . _amato_format_html_escape ($params['h']) . ';">' . $markup . '</span>';
}

function amato_format_html_font ($params, $markup, $closing)
{
	return '<span style="font-size: ' . max (min ((int)$params['p'], 300), 50) . '%; line-height: 100%;">' . $markup . '</span>';
}

function amato_format_html_horizontal ($params, $markup, $closing)
{
	return '<hr />';
}

function amato_format_html_image ($params, $markup, $closing)
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

function amato_format_html_list (&$params, $markup, $closing)
{
	if (!isset ($params['buffer']))
	{
		$params['buffer'] = '';
		$params['item'] = '';
		$params['next'] = 0;
		$params['stack'] = array ();
		$params['tag'] = 'u';
	}

	$params['item'] .= $markup;
	$tag = isset ($params['t']) ? $params['t'] : '';

	if ($tag === 'o' || $tag === 'u' || $closing)
	{
		// Flush accumulated item text if any
		if (trim ($params['item']) !== '')
		{
			$level = max ($params['next'], 1);

			while (count ($params['stack']) > $level)
				$params['buffer'] .= '</li></' . array_pop ($params['stack']) . 'l>';

			if (count ($params['stack']) === $level)
				$params['buffer'] .= '</li><li>';

			for (; count ($params['stack']) < $level; $params['stack'][] = $params['tag'])
				$params['buffer'] .= '<' . $params['tag'] . 'l><li>';

			$params['buffer'] .= $params['item'];
			$params['item'] = '';
			$params['next'] = 1;
		}

		// Increase level otherwise
		else
			$params['next'] = min ($params['next'] + 1, 8);

		// Save last tag type
		$params['tag'] = $tag;
	}

	if (!$closing)
		return '';

	while (count ($params['stack']) > 0)
		$params['buffer'] .= '</li></' . array_pop ($params['stack']) . 'l>';

	return $params['buffer'];
}

function amato_format_html_newline ($params, $markup, $closing)
{
	return '<br />';
}

function amato_format_html_pre ($params, $markup, $closing)
{
	return '<pre>' . _amato_format_html_escape ($params['b']) . '</pre>';
}

function amato_format_html_ref ($params, $markup, $closing)
{
	return '<a href="#" onclick="return false;">./' . $params['p'] . '</a>';
}

function amato_format_html_slap ($params, $markup, $closing)
{
	return '!slap ' . $params['u'] . ($params['u'] ? '<br /><span style="color: #990099;">&bull; #login# slaps ' . $params['u'] . ' around a bit with a large trout !</span><br />' : '');
}

function amato_format_html_smiley ($params, $markup, $closing)
{
	global $config;
	static $names;

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

function amato_format_html_table (&$params, $markup, $closing)
{
	if (!isset ($params['cols']))
		$params['cols'] = 0;

	if (!isset ($params['head']))
		$params['head'] = false;

	if (!isset ($params['rows']))
		$params['rows'] = array (array ());

	if (!isset ($params['size']))
		$params['size'] = 0;

	if (!isset ($params['span']))
		$params['span'] = 1;

	$markup = preg_replace ('#^([[:blank:]]*)(<br />)?(.*)(<br />)?([[:blank:]]*)$#', '$1$3$5', $markup);

	if ($params['span'] === 1 && trim ($markup) === '')
		;
	else if ($markup === '')
		++$params['span'];
	else
	{
		$span = $params['span'];

		$params['rows'][count ($params['rows']) - 1][] = array ($params['head'], $span, $markup);
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

	if (!$closing)
		return '';

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

?>
