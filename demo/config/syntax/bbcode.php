<?php

$syntax = array
(
	'.' => array
	(
		array (Amato\Tag::ALONE, "\n")
	),
	'a' => array
	(
		array (Amato\Tag::ALONE, '<https?%://[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=()*]+:u>'),
		array (Amato\Tag::ALONE, '<www.[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=()*]+:u>'),
		array (Amato\Tag::ALONE, '[url]<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=()*]+:u>[/url]'),
		array (Amato\Tag::START, '[url=<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=()*]+:u>]'),
		array (Amato\Tag::START, '[urli=<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=()*]+:u>]', array ('i' => '')),
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
		array (Amato\Tag::START, '[b]'),
		array (Amato\Tag::STOP, '[/b]')
	),
	'box' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[box=<[^][\\0-\\n]+:t>]"),
		array (Amato\Tag::STOP, "[/box]<\n?#\n>")
	),
	'c' => array
	(
		array (Amato\Tag::START, '[center]'),
		array (Amato\Tag::STOP, '[/center]')
	),
	'code' => array
	(
		array (Amato\Tag::ALONE, "<\n?#\n>[code=<[0-9a-zA-Z]+:l>]<.*?:b>[/code]<\n?#\n>")

	),
	'color' => array
	(
		array (Amato\Tag::START, '[color=<[0-9A-Fa-f]{3}:h>]'),
		array (Amato\Tag::START, '[color=<[0-9A-Fa-f]{6}:h>]'),
		array (Amato\Tag::START, '[color=#<[0-9A-Fa-f]{3}:h>]'),
		array (Amato\Tag::START, '[color=#<[0-9A-Fa-f]{6}:h>]'),
		array (Amato\Tag::STOP, '[/color]')
	),
	'font' => array
	(
		array (Amato\Tag::START, '[font=<[0-9]+:p>]'),
		array (Amato\Tag::STOP, '[/font]')
	),
	'hr' => array
	(
		array (Amato\Tag::ALONE, '[hr]')
	),
	'i' => array
	(
		array (Amato\Tag::START, '[i]'),
		array (Amato\Tag::STOP, '[/i]')
	),
	'img' => array
	(
		array (Amato\Tag::ALONE, '[img=<[0-9]+:p>]<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>[/img]', array ('s' => 1)),
		array (Amato\Tag::ALONE, '[img]<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=()*]+:u>[/img]')
	),
	'list' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[list]"),
		array (Amato\Tag::STEP, '[#]', array ('t' => 'o')),
		array (Amato\Tag::STEP, '[*]', array ('t' => 'u')),
		array (Amato\Tag::STEP, "\n", array ('t' => 'n')),
		array (Amato\Tag::STOP, "[/list]<\n?#\n>")
	),
	'pre' => array
	(
		array (Amato\Tag::ALONE, "<\n?#\n>[pre]<.*?:b>[/pre]<\n?#\n>")
	),
	'quote' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[quote]"),
		array (Amato\Tag::STOP, "[/quote]<\n?#\n>")
	),
	'ref' => array
	(
		array (Amato\Tag::ALONE, './<[0-9]{1,10}:t>-<[0-9]{1,10}:p>', null, 'amato_syntax_bbcode_ref_convert'),
		array (Amato\Tag::ALONE, './<[0-9]{1,10}:p>', null, 'amato_syntax_bbcode_ref_convert')
	),
	's' => array
	(
		array (Amato\Tag::START, '[s]'),
		array (Amato\Tag::STOP, '[/s]')
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
		array (Amato\Tag::ALONE, '##<[0-9A-Za-z]+:n>##', array ('t' => 'c')),
		array (Amato\Tag::ALONE, '#<[0-9A-Za-z]+:n>#', array ('t' => 'n'))
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
		array (Amato\Tag::START, "<\n?#\n>[table]"),
		array (Amato\Tag::STEP, '[^]', array ('t' => 'h')),
		array (Amato\Tag::STEP, '[|]', array ('t' => 'c')),
		array (Amato\Tag::STEP, '[-]', array ('t' => 'r')),
		array (Amato\Tag::STOP, "[/table]<\n?#\n>")
	),
	'u' => array
	(
		array (Amato\Tag::START, '[u]'),
		array (Amato\Tag::STOP, '[/u]')
	)
);

function amato_syntax_bbcode_ref_convert ($action, $flag, &$captures, $context)
{
	if (!isset ($captures['t']))
		$captures['t'] = 1;
}

?>
