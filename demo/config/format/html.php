<?php

$format = array
(
	'.'			=> array ('amato_format_html_newline'),
	'a'			=> array ('amato_format_html_anchor'),
	'align'		=> array ('amato_format_html_align', 2),
	'b'			=> array (_amato_format_html_tag ('b')),
	'c'			=> array ('amato_format_html_color'),
	'center'	=> array ('amato_format_html_center', 2),
	'code'		=> array ('amato_format_html_code', 2),
	'emoji'		=> array ('amato_format_html_emoji'),
	'font'		=> array ('amato_format_html_font'),
	'hr'		=> array ('amato_format_html_horizontal', 2),
	'i'			=> array (_amato_format_html_tag ('i')),
	'img'		=> array ('amato_format_html_image'),
	'list'		=> array ('amato_format_html_list', 2),
	'pre'		=> array ('amato_format_html_pre', 2),
	'quote'		=> array (_amato_format_html_tag ('blockquote'), 2),
	's'			=> array (_amato_format_html_class ('span', 's')),
	'spoil'		=> array (_amato_format_html_class ('span', 'spoil')),
	'sub'		=> array (_amato_format_html_tag ('sub')),
	'sup'		=> array (_amato_format_html_tag ('sup')),
	'table'		=> array ('amato_format_html_table', 2),
	'u'			=> array (_amato_format_html_class ('span', 'u'))
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
	if (preg_match ('#^[+.0-9A-Za-z]{1,16}://#', $params['u']))
		$url = _amato_format_html_escape ($params['u']);
	else
		$url = _amato_format_html_escape ('http://' . $params['u']);

	return '<a href="' . $url . '">' . ($markup ?: $url) . '</a>';
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

function amato_format_html_emoji ($params, $markup, $closing)
{
	return '<img alt="#' . $params['n'] . '#" src="res/emojis/' . $params['n'] . '.gif" />';
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

	return '<table class="markup">' . $rows . '</table>';
}

?>
