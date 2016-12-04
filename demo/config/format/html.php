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
	$o = $params->last ('o');
	$u = $params->last ('u');

	if ($o)
	{
		$level = max (min ((int)$o, 8), 1);
		$tag = 'o';
	}
	else if ($u)
	{
		$level = max (min ((int)$u, 8), 1);
		$tag = 'u';
	}
	else
	{
		$level = 1;
		$tag = 'u';
	}

	// Update HTML buffer
	$buffer = $params->get ('buffer', '');
	$stack = $params->get ('stack', '');

	for (; strlen ($stack) > $level; $stack = substr ($stack, 1))
		$buffer .= '</li></' . $stack[0] . 'l>';

	if (strlen ($stack) === $level)
		$buffer .= '</li><li>';

	for (; strlen ($stack) < $level; $stack = $tag . $stack)
		$buffer .= '<' . $tag . 'l><li>';

	$buffer .= $body;

	// Save current buffer and tags stack
	$params->forget ('o');
	$params->forget ('u');

	$params['buffer'] = $buffer;
	$params['stack'] = $stack;

	// Finalize rendering
	if (!$closing)
		return '';

	for (; strlen ($stack) > 0; $stack = substr ($stack, 1))
		$buffer .= '</li></' . $stack[0] . 'l>';

	return $buffer;
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
	$break = $params->get ('break', true);
	$rows = $params->get ('rows', array ());
	$span = $params->get ('span', 0);

	// No body specified since last tag, make next one span on one more column
	if ($body === '')
		++$span;

	// Body was specified, append cell to current row
	else
	{
		// Line break was requested, append a new blank row
		if ($break)
		{
			$break = false;
			$rows[] = array ('', 0);
		}

		// Build colspan HTML attribute if needed
		$span = max ($span, 1);

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
		$current = count ($rows) - 1;
		$tag = $params->last ('t') === 'h' ? 'th' : 'td';

		$rows[$current][0] .= '<' . $tag . $colspan . $style . '>' . $body . '</' . $tag . '>';
		$rows[$current][1] += $span;

		$span = 1;
	}

	// End of table not reached, remember tag for next call and exit
	if (!$closing)
	{
		if ($params['t'] === 'r')
		{
			$break = true;
			$span = 0;
		}

		$params['break'] = $break;
		$params['rows'] = $rows;
		$params['span'] = $span;

		return '';
	}

	// Render table by merging computed rows, extending their span when needed
	if (count ($rows) === 0)
		return '';

	$html = '';
	$max = 0;

	foreach ($rows as $row)
		$max = max ($row[1], $max);

	foreach ($rows as $row)
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
