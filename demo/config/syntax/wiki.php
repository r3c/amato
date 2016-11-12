<?php

$syntax = array
(
	'.' => array
	(
		array (Amato\Tag::ALONE, "\n")
	),
	'a' => array
	(
		array (Amato\Tag::ALONE, '<u:https?://[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>'),
		array (Amato\Tag::ALONE, '<u:www.[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>'),
		array (Amato\Tag::ALONE, '[url]<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>[/url]'),
		array (Amato\Tag::START, '[url=<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>]'),
		array (Amato\Tag::START, '[urli=<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>]', array ('i' => '')),
		array (Amato\Tag::STOP, '[/url]'),
		array (Amato\Tag::STOP, '[/urli]', array ('i' => ''))
	),
	'align' => array
	(
		array (Amato\Tag::START, '[align=center]', array ('w' => 'c')),
		array (Amato\Tag::START, '[align=left]'),
		array (Amato\Tag::START, '[align=right]', array ('w' => 'r')),
		array (Amato\Tag::STOP, '[/align]')

	),
	'b' => array
	(
		array (Amato\Tag::FLIP, '**')
	),
	'box' => array
	(
		array (Amato\Tag::START, "[box=<t:[^][\\0-\\n]+>]"),
		array (Amato\Tag::STOP, '[/box]')
	),
	'c' => array
	(
		array (Amato\Tag::START, '[center]'),
		array (Amato\Tag::STOP, '[/center]')
	),
	'code' => array
	(
		array (Amato\Tag::ALONE, '[code=<l:[0-9a-zA-Z]+>]<b:.*>[/code]')

	),
	'color' => array
	(
		array (Amato\Tag::START, '[color=<h:[0-9A-Fa-f]{3}>]'),
		array (Amato\Tag::START, '[color=<h:[0-9A-Fa-f]{6}>]'),
		array (Amato\Tag::START, '[color=#<h:[0-9A-Fa-f]{3}>]'),
		array (Amato\Tag::START, '[color=#<h:[0-9A-Fa-f]{6}>]'),
		array (Amato\Tag::STOP, '[/color]')
	),
	'font' => array
	(
		array (Amato\Tag::START, '[font=<p:[0-9]+>]'),
		array (Amato\Tag::STOP, '[/font]')
	),
	'hr' => array
	(
		array (Amato\Tag::ALONE, '[hr]')
	),
	'i' => array
	(
		array (Amato\Tag::FLIP, '//')
	),
	'img' => array
	(
		array (Amato\Tag::ALONE, '[img=<p:[0-9]+>]<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>[/img]', array ('s' => 1)),
		array (Amato\Tag::ALONE, '[img]<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>[/img]')
	),
	'list' => array
	(
		array (Amato\Tag::PULSE, "\n##", array ('t' => 'o')),
		array (Amato\Tag::PULSE, "\n**", array ('t' => 'u')),
		array (Amato\Tag::PULSE, "##", array ('t' => 'o')),
		array (Amato\Tag::PULSE, "**", array ('t' => 'u')),
		array (Amato\Tag::STOP, "\n")
	),
	'pre' => array
	(
		array (Amato\Tag::ALONE, ":::\n<b:.*>\n:::")
	),
	'quote' => array
	(
		array (Amato\Tag::START, '[quote]'),
		array (Amato\Tag::STOP, '[/quote]')
	),
	'ref' => array
	(
		array (Amato\Tag::ALONE, './<t:[0-9]{1,10}>-<p:[0-9]{1,10}>', null, 'amato_syntax_bbcode_ref_convert'),
		array (Amato\Tag::ALONE, './<p:[0-9]{1,10}>', null, 'amato_syntax_bbcode_ref_convert')
	),
	's' => array
	(
		array (Amato\Tag::FLIP, '--')
	),
	'smile' => array
	(
		array (Amato\Tag::ALONE, ':D', array ('t' => '0')),
		array (Amato\Tag::ALONE, ':\\(', array ('t' => '1')),
		array (Amato\Tag::ALONE, ':o', array ('t' => '2')),
		array (Amato\Tag::ALONE, ':)', array ('t' => '3')),
		array (Amato\Tag::ALONE, ':p', array ('t' => '4')),
		array (Amato\Tag::ALONE, ';)', array ('t' => '5')),
		array (Amato\Tag::ALONE, '=)', array ('t' => '6')),
		array (Amato\Tag::ALONE, '%)', array ('t' => '7')),
		array (Amato\Tag::ALONE, ':|', array ('t' => '8')),
		array (Amato\Tag::ALONE, ':S', array ('t' => '9')),
		array (Amato\Tag::ALONE, '##<n:[0-9A-Za-z]+>##', array ('t' => 'c')),
		array (Amato\Tag::ALONE, '#<n:[0-9A-Za-z]+>#', array ('t' => 'n'))
	),
	'spoil' => array
	(
		array (Amato\Tag::START, '[spoiler]'),
		array (Amato\Tag::STOP, '[/spoiler]')

	),
	'sub' => array
	(
		array (Amato\Tag::START, '[sub]'),
		array (Amato\Tag::STOP, '[/sub]')
	),
	'sup' => array
	(
		array (Amato\Tag::START, '[sup]'),
		array (Amato\Tag::STOP, '[/sup]')
	),
	'table' => array
	(
		array (Amato\Tag::START, '[table]'),
		array (Amato\Tag::STEP, '[^]', array ('t' => 'h')),
		array (Amato\Tag::STEP, '[|]', array ('t' => 'c')),
		array (Amato\Tag::STEP, '[-]', array ('t' => 'r')),
		array (Amato\Tag::STOP, '[/table]')
	),
	'u' => array
	(
		array (Amato\Tag::FLIP, '__')
	)
);

function amato_syntax_bbcode_ref_convert ($action, $flag, &$params, $context)
{
	if (!isset ($params['t']))
		$params['t'] = 1;
}

?>
