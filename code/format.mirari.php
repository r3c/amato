<?php

$_formatArguments = array
(
	'a'	=> '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
	'h'	=> '0123456789ABCDEFabcdef',
	'i'	=> '0123456789',
	's'	=> '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz&~"\'#{(-|_\\^@)=+}%*,?;.:/!'
);

$_formatModifiers = array
(
	array
	(
		'flag'	=> 1,
		'stop'	=> 'mirariFormatAlignStop',
		'tags'	=> array ('[align=(a)]' => 1, '[/align]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[b]' => 1, '[/b]' => 3)
	),
	array
	(
		'stop'	=> 'mirariFormatColorStop',
		'wrap'	=> 'mirariFormatColorWrap',
		'tags'	=> array ('[color=(h),(a)]' => 1, '[color=(h)]' => 1, '[/color]' => 3)
	),
	array
	(
		'stop'	=> 'mirariFormatCommentStop',
		'tags'	=> array ('[!]' => 1, '[/!]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'mirariFormatFloatStop',
		'tags'	=> array ('[float=(a)]' => 1, '[/float]' => 3)
	),
	array
	(
		'stop'	=> 'mirariFormatImageStop',
		'tags'	=> array ('[img]' => 1, '[img=(i),(i)]' => 1, '[/img]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[i]' => 1, '[/i]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'mirariFormatLineStop',
		'tags'	=> array ('[hr]' => 0)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'mirariFormatListInit',
		'step'	=> 'mirariFormatListStep',
		'stop'	=> 'mirariFormatListStop',
		'tags'	=> array ('[list]' => 1, '*' => 2, '#' => 2, '[/list]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[size=(i)]' => 1, '[/size]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[s]' => 1, '[/s]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[sub]' => 1, '[/sub]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[sup]' => 1, '[/sup]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'mirariFormatTableInit',
		'step'	=> 'mirariFormatTableStep',
		'stop'	=> 'mirariFormatTableStop',
		'tags'	=> array ('[table]' => 1, '[table=(i)]' => 1, '^' => 2, '|' => 2, "\n" => 2, '[/table]' => 3)
	),
	array
	(
		'init'	=> 'mirariFormatSpanInit',
		'stop'	=> 'mirariFormatSpanStop',
		'wrap'	=> 'mirariFormatSpanWrap',
		'tags'	=> array ('[u]' => 1, '[/u]' => 3)
	),
	array
	(
		'stop'	=> 'mirariFormatUrlStop',
		'tags'	=> array ('[url]' => 1, '[url=(s)]' => 1, '[/url]' => 3)
	)
);

function	mirariFormatAlignStop ($str, &$args)
{
	if ($args[0] == 'center' || $args[0] == 'left' || $args[0] == 'right')
		return '<div style="text-align: ' . $args[0] . ';">' . $str . '</div>';

	return null;
}

function	mirariFormatColorStop ($str, &$args)
{
	$hexa = $args[0];
	$mode = isset ($args[1]) ? $args[1] : 'fg';
	$len = strlen ($hexa);

	if ($len != 3 && $len != 6)
		return null;
	else if ($mode == 'bg')
		$attr = 'style="background: #' . $hexa . ';"';
	else if ($mode == 'fg')
		$attr = 'style="color: #' . $hexa . ';"';
	else
		return null;

	if ($args['div'])
		return '<div ' . $attr . '>' . $str . '</div>';

	return '<span ' . $attr . '>' . $str . '</span>';
}

function	mirariFormatColorWrap ($flag, &$args)
{
	if ($flag)
		$args['div'] = true;
}

function	mirariFormatCommentStop ($str)
{
	return '<!--' . $str . '-->';
}

function	mirariFormatFloatStop ($str, &$args)
{
	if ($args[0] == 'left' || $args[0] == 'right')
		return '<div style="float: ' . $args[0] . ';">' . $str . '</div>';

	return null;
}

function	mirariFormatImageStop ($str, &$args)
{
	if (preg_match ('@^[0-9A-Za-z]+://@', $str))
		$src = $str;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $str))
		$src = 'http://' . $str;
	else
		return null;

	if (isset ($args[0]) && isset ($args[1]))
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
	if ($args['div'])
		return '<div' . $args['attr'] . '>' . $str . '</div>';

	return '<span' . $args['attr'] . '>' . $str . '</span>';
}

function	mirariFormatSpanWrap ($flag, &$args)
{
	if ($flag)
		$args['div'] = true;
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
