<?php

$_formatArguments = array
(
	'a'	=> '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
	'h'	=> '#0123456789ABCDEFabcdef',
	'i'	=> '0123456789',
	's'	=> '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz&~"\'#{(-|_\\^@)=+}%*,?;.:/!'
);

$_formatModifiers = array
(
	array
	(
		'flag'	=> 1,
		'limit'	=> 3,
		'stop'	=> 'yNFormatBoxStop',
		'tags'	=> array ('[box=(s)]' => 1, '[/box]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[b]' => 1, '[/b]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatCenterStop',
		'tags'	=> array ('[center]' => 1, '[/center]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'limit'	=> 8,
		'stop'	=> 'yNFormatCiteStop',
		'tags'	=> array ('[cite]' => 1, '[quote]' => 1, '[/cite]' => 3, '[/quote]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatHtmlInit',
		'stop'	=> 'yNFormatHtmlStop',
		'wrap'	=> 'yNFormatHtmlWrap',
		'tags'	=> array ('[code]' => 1, '[/code]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatColorStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[color=(h)]' => 1, '[(i)]' => 1, '[/color]' => 3, '[/(i)]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatCommentStop',
		'tags'	=> array ('[!]' => 1, '[/!]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatHtmlInit',
		'stop'	=> 'yNFormatHtmlStop',
		'wrap'	=> 'yNFormatHtmlWrap',
		'tags'	=> array ('[em]' => 1, '[/em]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatEmailStop',
		'tags'	=> array ('[email]' => 1, '[/email]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatFlashStop',
		'tags'	=> array ('[flash]' => 1, '[flash=(i),(i)]' => 1, '[/flash]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatFloatStop',
		'tags'	=> array ('[float=(a)]' => 1, '[/float]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[font=(i)]' => 1, '[/font]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatGoogleStop',
		'wrap'	=> 'yNFormatGoogleWrap',
		'tags'	=> array ('[google]' => 1, '[/google]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[i]' => 1, '[/i]' => 3)
	),
	array
	(
		'limit'	=> 100,
		'stop'	=> 'yNFormatImageStop',
		'tags'	=> array ('[img]' => 1, '[img=(i),(i)]' => 1, '[/img]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatLineStop',
		'tags'	=> array ('[hr]' => 0)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatLeftStop',
		'tags'	=> array ('[left]' => 1, '[/left]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'yNFormatHtmlInit',
		'stop'	=> 'yNFormatHtmlStop',
		'wrap'	=> 'yNFormatHtmlWrap',
		'tags'	=> array ('[li]' => 1, '[/li]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatMediaStop',
		'tags'	=> array ('[media]' => 1, '[media=(i),(i)]' => 1, '[/media]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatMirariColorStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[mirari:color=(h),(a)]' => 1, '[mirari:color=(h)]' => 1, '[/mirari:color]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'yNFormatMirariListInit',
		'step'	=> 'yNFormatMirariListStep',
		'stop'	=> 'yNFormatMirariListStop',
		'tags'	=> array ('[mirari:list]' => 1, '[*]' => 2, '[#]' => 2, '[/mirari:list]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'yNFormatMirariTableInit',
		'step'	=> 'yNFormatMirariTableStep',
		'stop'	=> 'yNFormatMirariTableStop',
		'tags'	=> array ('[mirari:table]' => 1, '[mirari:table=(i)]' => 1, '[^^]' => 2, '[||]' => 2, '[--]' => 2, '[/mirari:table]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatModoStop',
		'tags'	=> array ('[modo]' => 1, '[/modo]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatNone',
		'tags'	=> array ('[noedit]' => 0, '[nosmile]' => 0)
	),
	array
	(
		'limit'	=> 100,
		'stop'	=> 'yNFormatPngStop',
		'tags'	=> array ('[png=(i),(i)]' => 1, '[/png]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatPreStop',
		'tags'	=> array ('[pre]' => 1, '[/pre]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatRightStop',
		'tags'	=> array ('[right]' => 1, '[/right]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[s]' => 1, '[/s]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatSlapStop',
		'tags'	=> array ('!slap (s)' => 0)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatSmileyStop',
		'tags'	=> array ('[smiley]' => 1, '[smiley=(i)]' => 1, '[/smiley]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'limit'	=> 3,
		'stop'	=> 'yNFormatSondageStop',
		'tags'	=> array ('[sondage=(i)]' => 0)
	),
	array
	(
		'stop'	=> 'yNFormatSourceStop',
		'tags'	=> array ('[source=(i)]' => 0)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[spoiler]' => 1, '[/spoiler]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[sub]' => 1, '[/sub]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[sup]' => 1, '[/sup]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'yNFormatTableInit',
		'step'	=> 'yNFormatTableStep',
		'stop'	=> 'yNFormatTableStop',
		'tags'	=> array ('[table]' => 1, '[itable]' => 1, '[-]' => 2, '[|]' => 2, '[/table]' => 3, '[/itable]' => 3)
	),
	array
	(
		'stop'	=> 'yNFormatTiwikiStop',
		'wrap'	=> 'yNFormatTiwikiWrap',
		'tags'	=> array ('[tiwiki]' => 1, '[/tiwiki]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatSpanInit',
		'stop'	=> 'yNFormatSpanStop',
		'wrap'	=> 'yNFormatSpanWrap',
		'tags'	=> array ('[u]' => 1, '[/u]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'init'	=> 'yNFormatHtmlInit',
		'stop'	=> 'yNFormatHtmlStop',
		'wrap'	=> 'yNFormatHtmlWrap',
		'tags'	=> array ('[ul]' => 1, '[/ul]' => 3)
	),
	array
	(
		'init'	=> 'yNFormatUrlInit',
		'stop'	=> 'yNFormatUrlStop',
		'tags'	=> array ('[url]' => 1, '[urli]' => 1, '[url=(s)]' => 1, '[urli=(s)]' => 1, '[/url]' => 3, '[/urli]' => 3)
	),
	array
	(
		'flag'	=> 1,
		'stop'	=> 'yNFormatyncMdStop',
		'tags'	=> array ('[yncMd:159]' => 1, '[/yncMd:159]' => 3)
	)
);

function	yNFormatBoxStop ($str, &$args)
{
	return '<div class="box"><div class="t" onclick="box (this);"><img src="' . $GLOBALS['skinP'] . '/application_put.png" />' . $args[0] . '</div><div class="c">' . $str . '</div></div>';
}

function	yNFormatCenterStop ($str)
{
	return '<div style="text-align: center;">' . $str . '</div>';
}

function	yNFormatCiteStop ($str)
{
	return '<blockquote class="cite">' . $str . '</blockquote>';
}

function	yNFormatColorStop ($str, &$args)
{
	$len = strlen ($args[0]);

	if (is_numeric ($args[0]) && $args[0] >= 0 && $args[0] <= 15)
		$attr = 'class="color' . $args[0] . '"';
	else if ($args[0][0] == '#' && ($len == 4 || $len == 7))
		$attr = 'style="color: ' . $args[0] . ';"';
	else if ($args[0][0] != '#' && ($len == 3 || $len == 6))
		$attr = 'style="color: #' . $args[0] . ';"';
	else
		return null;

	if ($args['div'])
		return '<div ' . $attr . '>' . $str . '</div>';

	return '<span ' . $attr . '>' . $str . '</span>';
}

function	yNFormatCommentStop ($str)
{
	return '<!--' . $str . '-->';
}

function	yNFormatEmailStop ($str)
{
	if (!preg_match ('|^[0-9A-Za-z.]+@[0-9A-Za-z]+(\\.[0-9A-Za-z]+)+$|', $str))
		return null;

	return '<a href="mailto:' . rawurlencode ($str) . '">' . $str . '</a>';
}

function	yNFormatFlashStop ($str, &$args)
{
	if (isset ($args[0]) && isset ($args[1]))
	{
		$x = max (min ($args[0], 1024), 1);
		$y = max (min ($args[1], 768), 1);
	}
	else
	{
		$x = 550;
		$y = 400;
	}

	return '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="' . $x . '" height="' . $y . '"><param name="movie" value="' . $str . '"><param name="quality" value="high"><embed src="' . $str . '" width="' . $x . '" height="' . $y . '" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash"></embed></object>';
}

function	yNFormatFloatStop ($str, &$args)
{
	if ($args[0] == 'left' || $args[0] == 'right')
		return '<div style="float: ' . $args[0] . ';">' . $str . '</div>';

	return null;
}

function	yNFormatGoogleStop ($str, &$args)
{
	if ($args['wrap'])
		return null;

	return '<b><span style="color: #003366;">G</span><span style="color: #CC3333;">o</span><span style="color: #FFCC00;">o</span><span style="color: #003366;">g</span><span style="color: #66CC00;">l</span><span style="color: #CC3300;">e</span> :</b> <a href="http://www.google.fr/search?hl=fr&amp;ie=UTF-8&amp;oe=UTF-8&amp;q=' . rawurlencode ($str) . '&amp;meta=lr%3Dlang_fr" target="_blank">' . $str . '</a>';
}

function	yNFormatGoogleWrap ($flag, &$args)
{
	$args['wrap'] = true;
}

function	yNFormatHtmlInit ($tag, &$args)
{
	$args['tag'] = $tag;
}

function	yNFormatHtmlStop ($str, &$args)
{
	switch ($args['tag'])
	{
		case '[code]':
			if ($args['div'])
				return null;

			$tag = 'code';
			break;

		case '[em]':
			if ($args['div'])
				return null;

			$tag = 'em';
			break;

		case '[li]':
			$tag = 'li';
			break;

		case '[ul]':
			$tag = 'ul';
			break;

		default:
			return null;
	}

	return '<' . $tag . '>' . $str . '</' . $tag . '>';
}

function	yNFormatHtmlWrap ($flag, &$args)
{
	if ($flag)
		$args['div'] = true;
}

function	yNFormatImageStop ($str, &$args)
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

function	yNFormatLeftStop ($str)
{
	return '<div style="text-align: left;">' . $str . '</div>';
}

function	yNFormatLineStop ()
{
	return '<hr />';
}

function	yNFormatMediaStop ($str, &$args)
{
	if (preg_match ('@^[0-9A-Za-z]+://@', $str))
		$src = $str;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $str))
		$src = 'http://' . $str;
	else
		return null;

	if (isset ($args[0]) && isset ($args[1]))
		return '<embed autostart="false" height="' . max (min ($args[1], 768), 1) . '" src="' . $src . '" width="' . max (min ($args[0], 1024), 1) . '" />';

	return '<embed autostart="false" height="120" src="' . $src . '" width="200" />';
}

function	yNFormatMirariColorStop ($str, &$args)
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

function	yNFormatMirariListInit ($tag, &$args)
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

function	yNFormatMirariListStep ($tag, $str, &$args)
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

function	yNFormatMirariListStop ($str, &$args)
{
	yNFormatMirariListStep ('', $str, $args);

	while ($args['level']--)
		$args['out'] .= '</li></' . array_pop ($args['stack']) . '>';

	return $args['out'];
}

function	yNFormatMirariTableInit ($tag, &$args)
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

function	yNFormatMirariTableStep ($tag, $str, &$args)
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

function	yNFormatMirariTableStop ($str, &$args)
{
	yNFormatMirariTableStep ('', $str, $args);

	$out = '';

	if ($args['cols'] > 0)
	{
		$out = '<table cellspacing="0" cellpadding="2" class="ymltab"' . (is_numeric ($args[0]) ? (' style="width: ' . max (min ($args[0], 100), 5) . '%;">') : '>');

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

function	yNFormatModoStop ($str)
{
	return '<div class="modo">' . $str . '</div>';
}

function	yNFormatNone ()
{
	return '';
}

function	yNFormatPngStop ($str, &$args)
{
	if (preg_match ('@^[0-9A-Za-z]+://@', $str))
		$src = $str;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $str))
		$src = 'http://' . $str;
	else
		return null;

	return '<span style="width: '. max (min ($args[0], 1024), 1) .'px; height: ' . max (min ($args[1], 768), 1) . 'px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' . $src . '\');"><img style=\"filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' . $src . '\'); filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);\" src=\'' . $src . '\' width="' . max (min ($args[0], 1024), 1) . '" height="' . max (min ($args[1], 768), 1) . '" border="0" /></span>';
}

function	yNFormatPreStop ($str)
{
	return '<pre>' . str_replace (array ("\r\n", "\r", "\n"), '<br />', $str) . '</pre>';
}

function	yNFormatRightStop ($str)
{
	return '<div style="text-align: right;">' . $str . '</div>';
}

function	yNFormatSlapStop ($str, &$args)
{
	return '!slap ' . $args[0] . '<br /><span style="color: #990099;">&bull; FIXME slaps ' . $args[0] . ' around a bit with a large trout !</span><br />';
}

function	yNFormatSmileyStop ($str, &$args)
{
	if (is_numeric ($args[0]) && $args[0] > 0 && $args[0] < 6)
		$i = $args[0];
	else
		$i = 1;

	return '<table border="0" cellpadding="0" cellspacing="0" style="margin: 3px;"><tr><td align="center"><img src="' . $GLOBALS['gfx'] . '/pancarte/h.gif" alt="" style="vertical-align: bottom;"/></td></tr><tr><td class="panneau" align="center">' . $str . '</td></tr><tr><td align=center><img src="' . $GLOBALS['gfx'] . '/pancarte/b' . $i . '.gif" alt="" /></td></tr></table>';
}

function	yNFormatSondageStop ($str, &$args)
{
	$s = $args[0];

	require ('sond.php');

	return $sondINC;
}

function	yNFormatSourceStop ($str, &$args)
{
	return '<a href="javascript:popup(\'source.php?s=' . $args[0] . '\', \'800\', \'600\');"><img src="' . $GLOBALS['gfx'] . '/source.gif" alt="" align="middle" /> Source</a>';
}

function	yNFormatSpanInit ($tag, &$args)
{
	$tags = array
	(
		'[b]'			=> ' style="font-weight: bold;"',
		'[font=(i)]'	=> ' style="font-size: ' . max (min ($args[0], 300), 50) . '%;"',		
		'[i]'			=> ' style="font-style: italic;"',
		'[s]'			=> ' style="text-decoration: line-through;"',
		'[spoiler]'		=> ' class="spoiler" onmouseout="this.className = \'spoiler\';" onmouseover="this.className = \'spoiler2\';"',
		'[sub]'			=> ' style="vertical-align: sub;"',
		'[sup]'			=> ' style="vertical-align: super;"',
		'[u]'			=> ' style="text-decoration: underline;"'
	);

	$args['attr'] = isset ($tags[$tag]) ? $tags[$tag] : '';
}

function	yNFormatSpanStop ($str, &$args)
{
	if ($args['div'])
		return '<div ' . $args['attr'] . '>' . $str . '</div>';

	return '<span ' . $args['attr'] . '>' . $str . '</span>';
}

function	yNFormatSpanWrap ($flag, &$args)
{
	if ($flag)
		$args['div'] = true;
}

function	yNFormatTableInit ($tag, &$args)
{
	$args = $args + array
	(
		'col'	=> 0,
		'cols'	=> 0,
		'row'	=> array (),
		'rows'	=> array (),
		'tag'	=> $tag
	);
}

function	yNFormatTableStep ($tag, $str, &$args)
{
	$args['col']++;
	$args['row'][] = $str;

	if ($tag == '[-]')
	{
		$args['cols'] = max ($args['cols'], $args['col']);
		$args['col'] = 0;
		$args['rows'][] = $args['row'];
		$args['row'] = array ();
	}
}

function	yNFormatTableStop ($str, &$args)
{
	yNFormatTableStep ('[-]', $str, $args);

	$out = '';

	if ($args['cols'] > 0)
	{
		if ($args['tag'] == '[itable]')
			$out = '<table border="0" cellspacing="0" cellpadding="2">';
		else
			$out = '<table cellspacing="0" cellpadding="2" class="ymltab">';

		foreach ($args['rows'] as $row)
		{
			$out .= '<tr valign="top">';
			$i = 0;

			foreach ($row as $col)
			{
				$out .= '<td>' . trim ($col) . '</td>';
				$i++;
			}

			if ($i < $args['cols'])
				$out .= '<td colspan="' . ($args['cols'] - $i) . '"></td>';

			$out .= '</tr>';
		}

		$out .= '</table>';
	}

	return $out;
}

function	yNFormatTiwikiStop ($str, &$args)
{
	if ($args['wrap'])
		return null;

	return '<a href="http://www.tiwiki.org/' . $str . '" target="_blank">' . $str . ' <img src="' . $GLOBALS['gfx'] . '/external.png" alt="TiWiki:' . $str . '" /></a>';
}

function	yNFormatTiwikiWrap ($flag, &$args)
{
	$args['wrap'] = true;
}

function	yNFormatUrlInit ($tag, &$args)
{
	if (substr ($tag, 0, 5) == '[urli')
		$args['attr'] = '';
	else
 		$args['attr'] = 'target="_blank"';
}

function	yNFormatUrlStop ($str, &$args)
{
	$target = isset ($args[0]) ? $args[0] : $str;

	if (preg_match ('@^[0-9A-Za-z]+://@', $target))
		$href = $target;
	else if (preg_match ('@^[-0-9A-Za-z]+(\\.[-0-9A-Za-z]+)+@', $target))
		$href = 'http://' . $target;
	else
		return null;

	return '<a href="' . $href . '"' . $args['attr'] . '>' . $str . '</a>';
}

function	yNFormatyncMdStop ($str)
{
	return '<div style="border: 1px solid #003366; border-right: 0; border-top: 0; border-bottom: 0; margin-left: 10px; padding-left: 5px;">' . $str . '</div>';
}

?>
