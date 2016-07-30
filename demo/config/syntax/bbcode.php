<?php

/*
** Syntax configuration: [markup id => [markup definitions]]
**   .0:	tag type
**   .1:	matching pattern
**   .2:	optional attributes
**   .3:	optional pre-convert callback
**   .4:	optional pre-revert callback
*/

$syntax = array
(
	'.' => array
	(
		array (Amato\Tag::ALONE, "\n")
	),
	'a' => array
	(
		array (Amato\Tag::ALONE, '<u:https?://[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>'),
		array (Amato\Tag::ALONE, '<u:www.[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>'), // How to distinguish from previous pattern? They could be merged if some group + options syntax is allowed
		array (Amato\Tag::ALONE, '[url]<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>[/url]'),
		array (Amato\Tag::START, '[url=<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>]'),
		array (Amato\Tag::START, '[urli=<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>]', array ('i' => 1)),
		array (Amato\Tag::STOP, '[/url]'),
		array (Amato\Tag::STOP, '[/urli]', array ('i' => 1))
	),
	'b' => array
	(
		array (Amato\Tag::START, '[b]'),
		array (Amato\Tag::STOP, '[/b]')
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
	'u' => array
	(
		array (Amato\Tag::START, '[u]'),
		array (Amato\Tag::STOP, '[/u]')
	)
/*
	'align' => array
	(
		array (Amato\Tag::START, '[align=center]', array ('w' => 'c')),
		array (Amato\Tag::START, '[align=left]', array ('w' => 'l')),
		array (Amato\Tag::START, '[align=right]', array ('w' => 'r')),
		array (Amato\Tag::STOP, '[/align]')

	),
	'box' => array
	(
		array (Amato\Tag::START, "[box=<t:( -\\^-\xFF){1,}>]"),
		array (Amato\Tag::STOP, '[/box]')
	),
	'c' => array
	(
		array (Amato\Tag::START, '[center]'),
		array (Amato\Tag::STOP, '[/center]')
	),
	'color' => array
	(
		array (Amato\Tag::START, '[color=<h:(0-9A-Fa-f){3}>]'),
		array (Amato\Tag::START, '[color=<h:(0-9A-Fa-f){6}>]'),
		array (Amato\Tag::START, '[color=#<h:(0-9A-Fa-f){3}>]'),
		array (Amato\Tag::START, '[color=#<h:(0-9A-Fa-f){6}>]'),
		array (Amato\Tag::STOP, '[/color]')
	),
	'font' => array
	(
		array (Amato\Tag::START, '[font=<p:(0-9){1,}>]'),
		array (Amato\Tag::STOP, '[/font]')
	),
	'img' => array
	(
		array (Amato\Tag::ALONE, '[img=<p:(0-9){1,}>]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]', array ('s' => 1)),
		array (Amato\Tag::ALONE, '[img]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]')
	),
	'list' => array
	(
		array (Amato\Tag::START, '[list]'),
		array (Amato\Tag::STEP, '[#]', array ('t' => 'o')),
		array (Amato\Tag::STEP, '[*]', array ('t' => 'u')),
		array (Amato\Tag::STOP, '[/list]')
	),
	'poll' => array
	(
		array (Amato\Tag::ALONE, '[sondage=<i:(0-9){1,}>]')
	),
	'pre' => array
	(
		array (Amato\Tag::START, '[pre]', null, $bbcode_check (array ('default' => true)), $bbcode_touch (array ('default' => false))),
		array (Amato\Tag::START, '[/pre]', null, $bbcode_check (array ('default' => false)), $bbcode_touch (array ('default' => true)))
	),
	'quote' => array
	(
		array (Amato\Tag::START, '[quote]'),
		array (Amato\Tag::STOP, '[/quote]'),
		array (Amato\Tag::START, '[cite]'),
		array (Amato\Tag::STOP, '[/cite]')
	),
	'ref' => array
	(
		array (Amato\Tag::ALONE, './<t:(0-9){1,10}>-<p:(0-9){1,10}>', null, null, null, 'amato_syntax_bbcode_ref_convert'),
		array (Amato\Tag::ALONE, './<p:(0-9){1,10}>', null, null, null, 'amato_syntax_bbcode_ref_convert')
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
		array (Amato\Tag::ALONE, '##<n:(0-9A-Za-z){1,}>##', array ('t' => 'c')),
		array (Amato\Tag::ALONE, '#<n:(0-9A-Za-z){1,}>#', array ('t' => 'n'))
	),
	'spoil' => array
	(
		array (Amato\Tag::START, '[spoiler]'),
		array (Amato\Tag::STOP, '[/spoiler]')

	),
	'src' => array
	(
		array (Amato\Tag::START, '[source=<l:(0-9a-zA-Z){1,}>]', null, $bbcode_check (array ('default' => true)), $bbcode_touch (array ('default' => false))),
		array (Amato\Tag::STOP, '[/source]', null, $bbcode_check (array ('default' => false)), $bbcode_touch (array ('default' => true))),

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

	)
*/
);

function amato_syntax_bbcode_ref_convert ($action, $flag, &$captures, $context)
{
	if (!isset ($captures['t']))
		$captures['t'] = 1;
}

?>
