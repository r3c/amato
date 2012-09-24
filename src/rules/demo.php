<?php

require_once ('src/yml.php');

/*
** String parsing parameters character classes, as name => characters.
*/
$ymlClassesDemo = array
(
	'alnum'	=> '+0-9A-Za-z',
	'any'	=> '-',
	'int'	=> '+0-9',
	'text'	=> '-]',
	'url'	=> '+0-9A-Za-z-._~:/?#@!$&\'()*+,;='
);

/*
** String parsing rules for each available tag, as name => properties
**   .decode:	optional flag to disable tag decoding
**   .literal:	optional flag to allow only literal bodies for this tag
**   .tags:		matching tag patterns, as pattern => behavior
**     .0:	tag type
**     .1:	optional custom identifier
*/
$ymlRulesDemo = array
(
	'!'		=> array
	(
		'tags'	=> array
		(
			'\\\\(any)'	=> array (YML_TYPE_SINGLE)
		)
	),
	'.'		=> array
	(
		'tags'	=> array
		(
			'./(int*)'	=> array (YML_TYPE_SINGLE)
		)
	),
	'0'		=> array
	(
		'tags'	=> array
		(
			'[0]'	=> array (YML_TYPE_BEGIN),
			'[/0]'	=> array (YML_TYPE_END)
		)
	),
	'1'		=> array
	(
		'tags'	=> array
		(
			'[1]'	=> array (YML_TYPE_BEGIN),
			'[/1]'	=> array (YML_TYPE_END)
		)
	),
	'2'		=> array
	(
		'tags'	=> array
		(
			'[2]'	=> array (YML_TYPE_BEGIN),
			'[/2]'	=> array (YML_TYPE_END)
		)
	),
	'3'		=> array
	(
		'tags'	=> array
		(
			'[3]'	=> array (YML_TYPE_BEGIN),
			'[/3]'	=> array (YML_TYPE_END)
		)
	),
	'4'		=> array
	(
		'tags'	=> array
		(
			'[4]'	=> array (YML_TYPE_BEGIN),
			'[/4]'	=> array (YML_TYPE_END)
		)
	),
	'5'		=> array
	(
		'tags'	=> array
		(
			'[5]'	=> array (YML_TYPE_BEGIN),
			'[/5]'	=> array (YML_TYPE_END)
		)
	),
	'6'		=> array
	(
		'tags'	=> array
		(
			'[6]'	=> array (YML_TYPE_BEGIN),
			'[/6]'	=> array (YML_TYPE_END)
		)
	),
	'7'		=> array
	(
		'tags'	=> array
		(
			'[7]'	=> array (YML_TYPE_BEGIN),
			'[/7]'	=> array (YML_TYPE_END)
		)
	),
	'8'		=> array
	(
		'tags'	=> array
		(
			'[8]'	=> array (YML_TYPE_BEGIN),
			'[/8]'	=> array (YML_TYPE_END)
		)
	),
	'9'		=> array
	(
		'tags'	=> array
		(
			'[9]'	=> array (YML_TYPE_BEGIN),
			'[/9]'	=> array (YML_TYPE_END)
		)
	),
	'10'	=> array
	(
		'tags'	=> array
		(
			'[10]'	=> array (YML_TYPE_BEGIN),
			'[/10]'	=> array (YML_TYPE_END)
		)
	),
	'11'	=> array
	(
		'tags'	=> array
		(
			'[11]'	=> array (YML_TYPE_BEGIN),
			'[/11]'	=> array (YML_TYPE_END)
		)
	),
	'12'	=> array
	(
		'tags'	=> array
		(
			'[12]'	=> array (YML_TYPE_BEGIN),
			'[/12]'	=> array (YML_TYPE_END)
		)
	),
	'13'	=> array
	(
		'tags'	=> array
		(
			'[13]'	=> array (YML_TYPE_BEGIN),
			'[/13]'	=> array (YML_TYPE_END)
		)
	),
	'14'	=> array
	(
		'tags'	=> array
		(
			'[14]'	=> array (YML_TYPE_BEGIN),
			'[/14]'	=> array (YML_TYPE_END)
		)
	),
	'15'	=> array
	(
		'tags'	=> array
		(
			'[15]'	=> array (YML_TYPE_BEGIN),
			'[/15]'	=> array (YML_TYPE_END)
		)
	),
	'a'		=> array
	(
		'tags'	=> array
		(
//			'http://(url*)'	=> array (YML_TYPE_SINGLE),
			'[url]'			=> array (YML_TYPE_BEGIN),
			'[url=(url*)]'	=> array (YML_TYPE_BEGIN),
			'[/url]'		=> array (YML_TYPE_END)
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'[b]'	=> array (YML_TYPE_BEGIN),
			'[/b]'	=> array (YML_TYPE_END)
		)
	),
	'box'	=> array
	(
		'tags'	=> array
		(
			'[box=(text*)]'	=> array (YML_TYPE_BEGIN),
			'[/box]'		=> array (YML_TYPE_END)
		)
	),
	'c'		=> array
	(
		'tags'	=> array
		(
			'[center]'	=> array (YML_TYPE_BEGIN),
			'[/center]'	=> array (YML_TYPE_END)
		)
	),
	'cmd'	=> array
	(
		'decode'	=> false,
		'tags'		=> array
		(
			'[yncMd:159]'	=> array (YML_TYPE_BEGIN),
			'[/yncMd:159]'	=> array (YML_TYPE_END)
		)
	),
	'em'	=> array
	(
		'tags'	=> array
		(
			'[em]'	=> array (YML_TYPE_BEGIN),
			'[/em]'	=> array (YML_TYPE_END)
		)
	),
	'hr'	=> array
	(
		'tags'	=> array
		(
			'[hr]'	=> array (YML_TYPE_SINGLE)
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'[i]'	=> array (YML_TYPE_BEGIN),
			'[/i]'	=> array (YML_TYPE_END)
		)
	),
	'img'	=> array
	(
		'tags'	=> array
		(
			'[img=(int*)](url*)[/img]'	=> array (YML_TYPE_SINGLE),
			'[img](url*)[/img]'			=> array (YML_TYPE_SINGLE)
		)
	),
	'list'	=> array
	(
		'tags'	=> array
		(
			'[list]'	=> array (YML_TYPE_BEGIN),
			'#'			=> array (YML_TYPE_BETWEEN, 'o'),
			'*'			=> array (YML_TYPE_BETWEEN, 'u'),
			'[/list]'	=> array (YML_TYPE_END)
		)
	),
	'q'		=> array
	(
		'tags'	=> array
		(
			'[cite]'	=> array (YML_TYPE_BEGIN),
			'[/cite]'	=> array (YML_TYPE_END),
			'[quote]'	=> array (YML_TYPE_BEGIN),
			'[/quote]'	=> array (YML_TYPE_END)
		)
	),
	's'		=> array
	(
		'tags'	=> array
		(
			'[s]'	=> array (YML_TYPE_BEGIN),
			'[/s]'	=> array (YML_TYPE_END)
		)
	),
	'src'	=> array
	(
		'tags'	=> array
		(
			'[source=(int*)]'	=> array (YML_TYPE_SINGLE)
		)
	),
	'sub'	=> array
	(
		'tags'	=> array
		(
			'[sub]'		=> array (YML_TYPE_BEGIN),
			'[/sub]'	=> array (YML_TYPE_END)
		)
	),
	'sup'	=> array
	(
		'tags'	=> array
		(
			'[sup]'		=> array (YML_TYPE_BEGIN),
			'[/sup]'	=> array (YML_TYPE_END)
		)
	),
	'u'		=> array
	(
		'tags'	=> array
		(
			'[u]'	=> array (YML_TYPE_BEGIN),
			'[/u]'	=> array (YML_TYPE_END)
		)
	)
);

/* DELETE BELOW */

/*
** List of modifiers, each modifier can define the following keys:
** limit:	maximum allowed occurrences of this modifier per string (optional)
** prec:	modifier precedence (optional, default is 1)
** tags:	list of modifier tag expressions and types (expr => type)
**			expr:	tag expression as string, can contain parameters
**			type:	tag type as integer, valid types are:
**				0:	standalone tag (eg. [hr])
**				1:	opening tag (eg. [table])
**				2:	inline tag (eg. |, ^ or $)
**				3:	closing tag (eg. [/table])
** init:	opening tags callback function ($tag, &$args) (optional)
**			tag:	matched tag expression
**			args:	value of variable parameters
** step:	inline tags callback function ($tag, $str, &$args) (optional)
**			tag:	matched tag expression
**			str: 	string between previous tag and this one
**			args:	value of variable parameters
**			return:	replacement string or null
** stop:	closing tags callback function ($str, &$args)
**			str: 	string between previous tag and this one
**			args:	value of variable parameters
**			return:	replacement string or null
*/
$_formatModifiers = array
(
	array
	(
		'prec'	=> 2,
		'tags'	=> array ('[align=(a)]' => 1, '[/align]' => 3),
		'stop'	=> 'mirariFormatAlignStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[b]' => 1, '[/b]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 2,
		'tags'	=> array ('[block=(a),(i),(i),(i)]' => 1, '[block=(a),(i),(i)]' => 1, '[block=(a),(i)]' => 1, '[block=(a),(i)]' => 1, '[block=(a)]' => 1, '[/block]' => 3),
		'stop'	=> 'mirariFormatBlockStop'
	),
	array
	(
		'prec'	=> 2,
		'tags'	=> array ('[box=(h),(h),(i)]' => 1, '[box=(h),(h)]' => 1, '[box=(h)]' => 1, '[/box]' => 3),
		'stop'	=> 'mirariFormatBoxStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[color=(h)]' => 1, '[/color]' => 3),
		'stop'	=> 'mirariFormatColorStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[!]' => 1, '[/!]' => 3),
		'stop'	=> 'mirariFormatCommentStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[img]' => 1, '[img=(i),(i)]' => 1, '[/img]' => 3),
		'stop'	=> 'mirariFormatImageStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[i]' => 1, '[/i]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 2,
		'tags'	=> array ('[hr]' => 0),
		'stop'	=> 'mirariFormatLineStop'
	),
	array
	(
		'prec'	=> 2,
		'tags'	=> array ('[list]' => 1, '*' => 2, '#' => 2, '[/list]' => 3),
		'init'	=> 'mirariFormatListInit',
		'step'	=> 'mirariFormatListStep',
		'stop'	=> 'mirariFormatListStop',
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[size=(i)]' => 1, '[/size]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[s]' => 1, '[/s]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[sub]' => 1, '[/sub]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[sup]' => 1, '[/sup]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 2,
		'tags'	=> array ('[table=(i)]' => 1, '[table]' => 1, '^' => 2, '|' => 2, '$' => 2, '[/table]' => 3),		
		'init'	=> 'mirariFormatTableInit',
		'step'	=> 'mirariFormatTableStep',
		'stop'	=> 'mirariFormatTableStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[u]' => 1, '[/u]' => 3),
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop'
	),
	array
	(
		'prec'	=> 1,
		'tags'	=> array ('[url]' => 1, '[url=(s)]' => 1, '[/url]' => 3),
		'stop'	=> 'mirariFormatUrlStop'
	)
);

function	mirariFormatAlignStop ($str, &$args)
{
	if ($args[0] == 'center' || $args[0] == 'left' || $args[0] == 'right')
		return '<div style="text-align: ' . $args[0] . ';">' . $str . '</div>';

	return null;
}

function	mirariFormatBlockStop ($str, &$args)
{
	$style = '';

	if ($args[0] == 'left' || $args[0] == 'right')
		$style .= ' float: ' . $args[0] . ';';
	else if ($args[0] == 'center')
		$style .= ' margin: auto;';
	else if ($args[0] != 'normal')
		return;

	if (is_numeric ($args[3]))
		$style .= ' width: ' . max (min ($args[1], 100), 5) . '%;';

	if (is_numeric ($args[1]))
	{
		if (is_numeric ($args[2]))
			$style .= ' padding: ' . max (min ($args[1], 128), 0) . 'px ' . max (min ($args[2], 128), 0) . 'px;';
		else
			$style .= ' padding: ' . max (min ($args[1], 128), 0) . 'px;';
	}

	return '<div style="' . substr ($style, 1) . '">' . $str . '</div>';
}

function	mirariFormatBoxStop ($str, &$args)
{
	$len1 = strlen ($args[0]);
	$len2 = isset ($args[1]) ? strlen ($args[1]) : 3;

	if (($len1 != 3 && $len1 != 6) || ($len2 != 3 && $len2 != 6))
		return null;
	else if (isset ($args[2]))
		$style = 'background: #' . $args[0] . '; border: ' . max (min (128, $args[2]), 1) . 'px solid #' . $args[1] . ';';
	else if (isset ($args[1]))
		$style = 'background: #' . $args[0] . '; border: 1px solid #' . $args[1] . ';';
	else
		$style = 'background: #' . $args[0] . ';';

	return '<div style="' . $style . '">' . $str . '</div>';
}

function	mirariFormatColorStop ($str, &$args)
{
	$len = strlen ($args[0]);

	if ($len != 3 && $len != 6)
		return null;

	return '<span style="color: #' . $args[0] . ';">' . $str . '</span>';
}

function	mirariFormatCommentStop ($str)
{
	return '<!--' . $str . '-->';
}

function	mirariFormatImageStop ($str, &$args)
{
	if (preg_match ('@^[0-9A-Za-z]+://@', $str))
		$src = $str;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $str))
		$src = 'http://' . $str;
	else
		return null;

	if (is_numeric ($args[0]) && is_numeric ($args[1]))
		return '<img alt="image" height="' . max (min ($args[1], 768), 1) . '" src="' . $src . '" width="' . max (min ($args[0], 1024), 1) . '" />';

	return '<img alt="image" src="' . $src . '" />';
}

function	mirariFormatLineStop ()
{
	return '<hr />';
}

function	mirariFormatListInit ($tag, &$args)
{
	$args = $args + array
	(
		'level'	=> 0,
		'next'	=> 0,
		'out'	=> '',
		'stack'	=> array (),
		'tag'	=> ''
	);
}

function	mirariFormatListStep ($tag, $str, &$args)
{
	$str = trim ($str);

	if ($args['tag'] && $str)
	{
		for (; $args['level'] > $args['next']; --$args['level'])
			$args['out'] .= '</li></' . array_pop ($args['stack']) . '>';

		if ($args['level'] == $args['next'])
			$args['out'] .= '</li><li>';

		for (; $args['level'] < $args['next']; ++$args['level'])
			$args['out'] .= '<' . ($args['stack'][] = $args['tag']) . '><li>';

		$args['next'] = 1;
		$args['out'] .=	$str;
	}
	else
		++$args['next'];

	$args['tag'] = ($tag == '#' ? 'ol' : 'ul');
}

function	mirariFormatListStop ($str, &$args)
{
	mirariFormatListStep ('', $str, $args);

	while ($args['level']--)
		$args['out'] .= '</li></' . array_pop ($args['stack']) . '>';

	return $args['out'];
}

function	mirariFormatSpanInit ($tag, &$args)
{
	$tags = array
	(
		'[b]'			=> ' style="font-weight: bold;"',
		'[i]'			=> ' style="font-style: italic;"',
		'[s]'			=> ' style="text-decoration: line-through;"',
		'[size=(i)]'	=> ' style="font-size: ' . max (min ($args[0], 300), 20) . '%;"',
		'[sub]'			=> ' style="vertical-align: sub;"',
		'[sup]'			=> ' style="vertical-align: super;"',
		'[u]'			=> ' style="text-decoration: underline;"'
	);

	$args['attr'] = isset ($tags[$tag]) ? $tags[$tag] : '';
}

function	mirariFormatSpanStop ($str, &$args)
{
	return '<span' . $args['attr'] . '>' . $str . '</span>';
}

function	mirariFormatTableInit ($tag, &$args)
{
	$args = $args + array
	(
		'col'	=> 0,
		'cols'	=> 0,
		'row'	=> array (),
		'rows'	=> array (),
		'span'	=> 1,
		'tag'	=> ''
	);
}

function	mirariFormatTableStep ($tag, $str, &$args)
{
	if ($args['tag'])
	{
		if ($str)
		{
			$args['col'] += $args['span'];
			$args['row'][] = array ($str, $args['tag'], $args['span']);
			$args['span'] = 1;
		}
		else
			$args['span']++;
	}

	if ($tag == '^')
		$args['tag'] = 'th';
	else if ($tag == '|')
		$args['tag'] = 'td';
	else if ($args['col'] > 0)
	{
		$args['cols'] = max ($args['cols'], $args['col']);
		$args['col'] = 0;
		$args['rows'][] = $args['row'];
		$args['row'] = array ();
		$args['tag'] = '';
	}
}

function	mirariFormatTableStop ($str, &$args)
{
	mirariFormatTableStep ('', $str, $args);

	$out = '';

	if ($args['cols'] > 0)
	{
		$out = '<table' . (is_numeric ($args[0]) ? (' style="width: ' . max (min ($args[0], 100), 5) . '%;">') : '>');

		foreach ($args['rows'] as $row)
		{
			$out .= '<tr>';
			$i = 0;

			foreach ($row as $col)
			{
				$al = substr ($col[0], -2) == '  ';
				$ar = substr ($col[0], 0, 2) == '  ';

				if ($al && $ar)
					$align = 'center';
				else if ($al)
					$align = 'left';
				else if ($ar)
					$align = 'right';
				else
					$align = '';

				$out .= '<' . $col[1] . ($col[2] > 1 ? (' colspan="' . $col[2] . '"') : '') . ($align ? (' style="text-align: ' . $align . ';">') : '>')
				      . trim ($col[0])
				      . '</' . $col[1] . '>';
				$i += $col[2];
			}

			if ($i < $args['cols'])
				$out .= '<td colspan="' . ($args['cols'] - $i) . '"></td>';

			$out .= '</tr>';
		}

		$out .= '</table>';
	}

	return $out;
}

function	mirariFormatUrlStop ($str, &$args)
{
	$target = isset ($args[0]) ? $args[0] : $str;

	if (preg_match ('@^[0-9A-Za-z]+://@', $target))
		$href = $target;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $target))
		$href = 'http://' . $target;
	else
		return null;

	return '<a href="' . $href . '">' . $str . '</a>';
}

?>
