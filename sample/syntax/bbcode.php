<?php

/*
** String parsing rules for each available tag, as name => properties
**   .limit:		optional allowed number of uses of this tag, default is 100
**   .onConvert:	optional convert callback as ($action, $flag, $captures,
**					$custom) -> bool, ignore match if false is returned
**   .onRevert:		optional revert callback as ($action, $flag, $captures,
**					$custom) -> bool, ignore match if false is returned
**   .tags:			tag patterns list, as pattern => [expression => action]
**		[0]:		action tag type
**		[1]:		optional action flag, null if none
**		[2]:		optional action switch expression
*/
$syntax = array
(
	'.'		=> array
	(
		'limit'	=> 0,
		'tags'	=> array
		(
			"\n"	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'0'		=> array
	(
		'tags'	=> array
		(
			'[0]'	=> array ('default' => array (Umen\Action::START)),
			'[/0]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'1'		=> array
	(
		'tags'	=> array
		(
			'[1]'	=> array ('default' => array (Umen\Action::START)),
			'[/1]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'2'		=> array
	(
		'tags'	=> array
		(
			'[2]'	=> array ('default' => array (Umen\Action::START)),
			'[/2]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'3'		=> array
	(
		'tags'	=> array
		(
			'[3]'	=> array ('default' => array (Umen\Action::START)),
			'[/3]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'4'		=> array
	(
		'tags'	=> array
		(
			'[4]'	=> array ('default' => array (Umen\Action::START)),
			'[/4]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'5'		=> array
	(
		'tags'	=> array
		(
			'[5]'	=> array ('default' => array (Umen\Action::START)),
			'[/5]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'6'		=> array
	(
		'tags'	=> array
		(
			'[6]'	=> array ('default' => array (Umen\Action::START)),
			'[/6]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'7'		=> array
	(
		'tags'	=> array
		(
			'[7]'	=> array ('default' => array (Umen\Action::START)),
			'[/7]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'8'		=> array
	(
		'tags'	=> array
		(
			'[8]'	=> array ('default' => array (Umen\Action::START)),
			'[/8]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'9'		=> array
	(
		'tags'	=> array
		(
			'[9]'	=> array ('default' => array (Umen\Action::START)),
			'[/9]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'10'	=> array
	(
		'tags'	=> array
		(
			'[10]'	=> array ('default' => array (Umen\Action::START)),
			'[/10]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'11'	=> array
	(
		'tags'	=> array
		(
			'[11]'	=> array ('default' => array (Umen\Action::START)),
			'[/11]'	=> array ('v11' => array (Umen\Action::STOP))
		)
	),
	'12'	=> array
	(
		'tags'	=> array
		(
			'[12]'	=> array ('default' => array (Umen\Action::START)),
			'[/12]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'13'	=> array
	(
		'tags'	=> array
		(
			'[13]'	=> array ('default' => array (Umen\Action::START)),
			'[/13]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'14'	=> array
	(
		'tags'	=> array
		(
			'[14]'	=> array ('default' => array (Umen\Action::START)),
			'[/14]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'15'	=> array
	(
		'tags'	=> array
		(
			'[15]'	=> array ('default' => array (Umen\Action::START)),
			'[/15]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'a'		=> array
	(
		'tags'	=> array
		(
			'<u:https{,1}://(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'	=> array ('default' => array (Umen\Action::ALONE, 'h')),
			'<u:www.(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'			=> array ('default' => array (Umen\Action::ALONE, 'w')),
			'[url]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\)*){1,}>[/url]'	=> array ('default' => array (Umen\Action::ALONE)),
			'[url=<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'		=> array ('default;!a' => array (Umen\Action::START)),
			'[urli=<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'		=> array ('default;!a' => array (Umen\Action::START, 'i')),
			'[/url]'													=> array ('default' => array (Umen\Action::STOP)),
			'[/urli]'													=> array ('default' => array (Umen\Action::STOP, 'i'))
		)
	),
	'align'	=> array
	(
		'tags'	=> array
		(
			'[align=center]'	=> array ('default' => array (Umen\Action::START, 'c')),
			'[align=left]'		=> array ('default' => array (Umen\Action::START, 'l')),
			'[align=right]'		=> array ('default' => array (Umen\Action::START, 'r')),
			'[/align]'			=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'[b]'	=> array ('default' => array (Umen\Action::START)),
			'[/b]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'box'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			"[box=<t:( -\\^-\xFF){1,}>]"	=> array ('default' => array (Umen\Action::START)),
			'[/box]'						=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'c'		=> array
	(
		'tags'	=> array
		(
			'[center]'	=> array ('default' => array (Umen\Action::START)),
			'[/center]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'cmd'	=> array
	(
		'onRevert'	=> 'umenMarkupCmdRevert',
		'tags'		=> array
		(
			'[yncMd:159]'	=> array ('default;!cmd' => array (Umen\Action::START)),
			'[/yncMd:159]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'color'	=> array
	(
		'tags'	=> array
		(
			'[color=<h:(0-9A-Fa-f){3}>]'	=> array ('default' => array (Umen\Action::START, '3')),
			'[color=<h:(0-9A-Fa-f){6}>]'	=> array ('default' => array (Umen\Action::START, '6')),
			'[color=#<h:(0-9A-Fa-f){3}>]'	=> array ('default' => array (Umen\Action::START, '#3')),
			'[color=#<h:(0-9A-Fa-f){6}>]'	=> array ('default' => array (Umen\Action::START, '#6')),
			'[/color]'						=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'em'	=> array
	(
		'tags'	=> array
		(
			'[em]'	=> array ('default' => array (Umen\Action::START)),
			'[/em]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'flash'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[flash=<x:(0-9){1,}>,<y:(0-9){1,}>]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'	=> array ('default' => array (Umen\Action::ALONE, 's')),
			'[flash]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'								=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'font'	=> array
	(
		'tags'	=> array
		(
			'[font=<p:(0-9){1,}>]'	=> array ('default' => array (Umen\Action::START)),
			'[/font]'				=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'hr'	=> array
	(
		'tags'	=> array
		(
			'[hr]'	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'[i]'	=> array ('default' => array (Umen\Action::START)),
			'[/i]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'img'	=> array
	(
		'limit'	=> 100,
		'tags'	=> array
		(
			'[img=<p:(0-9){1,}>]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'	=> array ('default' => array (Umen\Action::ALONE, 's')),
			'[img]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'				=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'list'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[list]'	=> array ('default' => array (Umen\Action::START)),
			'[#]'		=> array ('default' => array (Umen\Action::STEP, 'o')),
			'[*]'		=> array ('default' => array (Umen\Action::STEP, 'u')),
			'[/list]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'modo'	=> array
	(
		'limit'		=> 20,
		'onConvert'	=> 'umenMarkupModoConvert',
		'tags'		=> array
		(
			'[modo]'	=> array ('default' => array (Umen\Action::START)),
			'[/modo]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'poll'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[sondage=<i:(0-9){1,}>]'	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'pre'	=> array
	(
		'tags'	=> array
		(
			'[pre]'		=> array ('default' => array (Umen\Action::START, null, '-default')),
			'[/pre]'	=> array ('' => array (Umen\Action::STOP, null, '+default'))
		)
	),
	'quote'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[quote]'	=> array ('default' => array (Umen\Action::START)),
			'[/quote]'	=> array ('default' => array (Umen\Action::STOP)),
			'[cite]'	=> array ('default' => array (Umen\Action::START)),
			'[/cite]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'ref'	=> array
	(
		'tags'	=> array
		(
			'./<n:(0-9){1,}>'	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	's'		=> array
	(
		'tags'	=> array
		(
			'[s]'	=> array ('default' => array (Umen\Action::START)),
			'[/s]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'slap'	=> array
	(
		'tags'	=> array
		(
			"!slap <u:(0-9A-Za-z ){1,}>"	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'smile'	=> array
	(
		'tags'	=> array
		(
			':D'						=> array ('default' => array (Umen\Action::ALONE, '0')),
			':\\('						=> array ('default' => array (Umen\Action::ALONE, '1')),
			':o'						=> array ('default' => array (Umen\Action::ALONE, '2')),
			':)'						=> array ('default' => array (Umen\Action::ALONE, '3')),
			':p'						=> array ('default' => array (Umen\Action::ALONE, '4')),
			';)'						=> array ('default' => array (Umen\Action::ALONE, '5')),
			'=)'						=> array ('default' => array (Umen\Action::ALONE, '6')),
			'%)'						=> array ('default' => array (Umen\Action::ALONE, '7')),
			':|'						=> array ('default' => array (Umen\Action::ALONE, '8')),
			':S'						=> array ('default' => array (Umen\Action::ALONE, '9')),
			'##<n:(0-9A-Za-z){1,}>##'	=> array ('default' => array (Umen\Action::ALONE, 'c')),
			'#<n:(0-9A-Za-z){1,}>#'		=> array ('default' => array (Umen\Action::ALONE, 'n'))
		)
	),
	'spoil'	=> array
	(
		'tags'	=> array
		(
			'[spoiler]'		=> array ('default' => array (Umen\Action::START)),
			'[/spoiler]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'src'	=> array
	(
		'limit'	=> 10,
		'tags'	=> array
		(
			'[source=<l:(0-9a-zA-Z){1,}>]'	=> array ('default' => array (Umen\Action::START, null, '-default')),
			'[/source]'						=> array ('' => array (Umen\Action::STOP, null, '+default'))
		)
	),
	'sub'	=> array
	(
		'tags'	=> array
		(
			'[sub]'		=> array ('default' => array (Umen\Action::START)),
			'[/sub]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'sup'	=> array
	(
		'tags'	=> array
		(
			'[sup]'		=> array ('default' => array (Umen\Action::START)),
			'[/sup]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'table'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[table]'	=> array ('default' => array (Umen\Action::START)),
			'[^]'		=> array ('default' => array (Umen\Action::STEP, 'h')),
			'[|]'		=> array ('default' => array (Umen\Action::STEP, 'c')),
			'[-]'		=> array ('default' => array (Umen\Action::STEP, 'r')),
			'[/table]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'u'		=> array
	(
		'tags'	=> array
		(
			'[u]'	=> array ('default' => array (Umen\Action::START)),
			'[/u]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'uni'	=> array
	(
		'tags'	=> array
		(
			'&amp;#<c:(a-z0-9){1,}>;'	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'yt'	=> array
	(
		'tags'	=> array
		(
			'[youtube]<i:(-0-9A-Za-z_){1,}>[/youtube]'																			=> array ('default' => array (Umen\Action::ALONE)),
			'[youtube]http://www.youtube.com/watch?v=<i:(-0-9A-Za-z_){1,}>[/youtube]'											=> array ('default' => array (Umen\Action::ALONE)),
			'[youtube]http://www.youtube.com/watch?v=<i:(-0-9A-Za-z_){1,}>(&#)(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){}[/youtube]'	=> array ('default' => array (Umen\Action::ALONE))
		)
	)
);

function	umenMarkupCmdRevert ($action, $flag, $captures, $context)
{
	return false;
}

function	umenMarkupModoConvert ($action, $flag, $captures, $context)
{
	if ($action !== Umen\Action::STOP)
		return true;

	return $context['user']['level'] >= 2;
}

?>
