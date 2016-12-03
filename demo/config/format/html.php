<?php

$formats = array
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
	'table'		=> array ('amato_format_html_table', 2),
	'u'			=> array (_amato_format_html_class ('span', 'u'))
);

function _amato_format_html_class ($id, $name)
{
	return function ($body, $params) use ($id, $name)
	{
		if ($body === '')
			return '';

		return '<' . $id . ' class="' . $name . '">' . $body . '</' . $id . '>';
	};
}

function _amato_format_html_tag ($id)
{
	return function ($body, $params) use ($id)
	{
		if ($body === '')
			return '';

		return '<' . $id . '>' . $body . '</' . $id . '>';
	};
}

function _amato_format_html_escape ($string)
{
	return htmlspecialchars ($string, ENT_COMPAT, CHARSET);
}

function amato_format_html_align ($body, $params)
{
	$align = array ('c' => 'center', 'r' => 'right');

	return '<div style="text-align: ' . _amato_format_html_escape (isset ($align[$params['w']]) ? $align[$params['w']] : 'left') . ';">' . $body . '</div>';
}

function amato_format_html_anchor ($body, $params)
{
	if (preg_match ('#^[+.0-9A-Za-z]{1,16}://#', $params['u']))
		$url = _amato_format_html_escape ($params['u']);
	else
		$url = _amato_format_html_escape ('http://' . $params['u']);

	return '<a href="' . $url . '">' . ($body ?: $url) . '</a>';
}

function amato_format_html_center ($body)
{
	return '<div class="center">' . $body . '</div>';
}

function amato_format_html_code ($body, $params)
{
	static $brushes;

	if (!isset ($brushes))
		$brushes = array_flip (array ('as3', 'bash', 'csharp', 'c', 'cpp', 'css', 'delphi', 'diff', 'groovy', 'js', 'java', 'jfx', 'm68k', 'perl', 'php', 'plain', 'ps', 'py', 'rails', 'scala', 'sql', 'vb', 'xml'));

	if (!isset ($brushes[$params['l']]))
		return $body;

	return '<pre class="brush: ' . $params['l'] . '">' . str_replace ('<br />', "\n", $body) . '</pre>';
}

function amato_format_html_color ($body, $params)
{
	return '<span style="color: #' . _amato_format_html_escape ($params['h']) . ';">' . $body . '</span>';
}

function amato_format_html_emoji ($body, $params)
{
	return '<img alt="#' . $params['n'] . '#" src="res/emojis/' . $params['n'] . '.gif" />';
}

function amato_format_html_font ($body, $params)
{
	return '<span style="font-size: ' . max (min ((int)$params['p'], 300), 50) . '%; line-height: 100%;">' . $body . '</span>';
}

function amato_format_html_horizontal ()
{
	return '<hr />';
}

function amato_format_html_image ($body, $params)
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

function amato_format_html_list ($body, &$params, $closing)
{
	// Read parameters from last tag
	if (isset ($params['o']))
	{
		$next_level = max (min ((int)$params['o'], 8), 1);
		$next_tag = 'o';
	}
	else if (isset ($params['u']))
	{
		$next_level = max (min ((int)$params['u'], 8), 1);
		$next_tag = 'u';
	}

	$level = isset ($params['level']) ? $params['level'] : 1;
	$tag = isset ($params['tag']) ? $params['tag'] : $next_tag;

	// Update HTML buffer
	if (!isset ($params['html']))
		$params['html'] = '';

	if (!isset ($params['stack']))
		$params['stack'] = array ();

	while (count ($params['stack']) > $level)
		$params['html'] .= '</li></' . array_pop ($params['stack']) . 'l>';

	if (count ($params['stack']) === $level)
		$params['html'] .= '</li><li>';

	for (; count ($params['stack']) < $level; $params['stack'][] = $tag)
		$params['html'] .= '<' . $tag . 'l><li>';

	$params['html'] .= $body;
	$params['level'] = $next_level;
	$params['tag'] = $next_tag;

	// Finalize rendering
	if (!$closing)
		return '';

	while (count ($params['stack']) > 0)
		$params['html'] .= '</li></' . array_pop ($params['stack']) . 'l>';

	return $params['html'];
}

function amato_format_html_newline ()
{
	return '<br />';
}

function amato_format_html_pre ($body, $params)
{
	return '<pre>' . _amato_format_html_escape ($params['b']) . '</pre>';
}

function amato_format_html_table ($body, &$params, $closing)
{
	if (!isset ($params['break']))
	{
		$params['break'] = true;
		$params['rows'] = array ();
		$params['span'] = 0;
		$params['tag'] = 'td';
	}

	// No body specified since last tag, make next one span on one more column
	if ($body === '')
		++$params['span'];

	// Body was specified, append cell to current row
	else
	{
		// Line break was requested, append a new blank row
		if ($params['break'])
		{
			$params['break'] = false;
			$params['rows'][] = array ('', 0);
		}

		// Read index of current row, tag and span for current cell
		$current = count ($params['rows']) - 1;
		$span = max ($params['span'], 1);
		$tag = $params['tag'];

		// Build colspan HTML attribute if needed
		if ($span > 1)
			$colspan = ' colspan="' . $span . '"';
		else
			$colspan = '';

		// Build style HTML attribute if needed
		$align_left = mb_substr ($body, -2) === '  ';
		$align_right = mb_substr ($body, 0, 2) === '  ';

		if ($align_left && $align_right)
			$style = ' style="text-align: center;"';
		else if ($align_left)
			$style = ' style="text-align: left;"';
		else if ($align_right)
			$style = ' style="text-align: right;"';
		else
			$style = '';

		// Append cell content to current row and reset span
		$params['rows'][$current][0] .= '<' . $tag . $colspan . $style . '>' . $body . '</' . $tag . '>';
		$params['rows'][$current][1] += $span;
		$params['span'] = 1;
	}

	// End of table not reached, remember tag for next call and exit
	if (!$closing)
	{
		switch ($params['t'])
		{
			case 'c':
				$params['tag'] = 'td';

				break;

			case 'h':
				$params['tag'] = 'th';

				break;

			default:
				$params['break'] = true;
				$params['span'] = 0;
				$params['tag'] = 'td';

				break;
		}

		return '';
	}

	// Render table by merging computed rows, extending their span when needed
	if (count ($params['rows']) === 0)
		return '';

	$html = '';
	$max = 0;

	foreach ($params['rows'] as $row)
		$max = max ($row[1], $max);

	foreach ($params['rows'] as $row)
	{
		list ($append, $span) = $row;

		$html .=
			'<tr>' .
				($append) .
				($span < $max ? '<td ' . ($max > $span + 1 ? ' colspan="' . ($max - $span) . '"' : '') . '></td>' : '') .
			'</tr>';
	}

	return '<table class="table">' . $html . '</table>';
}

?>
