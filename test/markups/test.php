<?php

/*
** String parsing rules for each available tag, as name => properties
**   .limit:		optional allowed number of uses of this tag, default is 100
**   .onConvert:	optional convert callback as ($action, $flag, $captures,
**					$custom) -> bool, ignore match if false is returned
**   .onRevert:		optional revert callback as ($action, $flag, $captures,
**					$custom) -> bool, ignore match if false is returned
**   .tags:			tag patterns list, as [pattern => [context => instruction]]
**     .0:			tag action
**     .1:			optional tag flag identifier, null if none
**     .2:          optional context switch command
*/
$markup = array
(
	'.'		=> array
	(
//		'limit'	=> 100,
		'tags'	=> array
		(
			"\n\n"	=> array ('default' => array (Umen\Action::ALONE))
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'**'	=> array ('default' => array (Umen\Action::STOP, null, '-b'), 'default;!b' => array (Umen\Action::START, null, '+b')),
			'[b]'	=> array ('default' => array (Umen\Action::START)),
			'[/b]'	=> array ('default' => array (Umen\Action::STOP))
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'//'	=> array ('default' => array (Umen\Action::STOP, null, '-i'), 'default;!i' => array (Umen\Action::START, null, '+i'))
		)
	),
	'pre'	=> array
	(
		'tags'	=> array
		(
			'[[['	=> array ('default' => array (Umen\Action::START, null, '-default')),
			']]]'	=> array ('' => array (Umen\Action::STOP, null, '+default'))
		)
	),

/*
	'0'		=> array
	(
		'tags'	=> array
		(
			'[0]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/0]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'1'		=> array
	(
		'tags'	=> array
		(
			'[1]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/1]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'2'		=> array
	(
		'tags'	=> array
		(
			'[2]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/2]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'3'		=> array
	(
		'tags'	=> array
		(
			'[3]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/3]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'4'		=> array
	(
		'tags'	=> array
		(
			'[4]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/4]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'5'		=> array
	(
		'tags'	=> array
		(
			'[5]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/5]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'6'		=> array
	(
		'tags'	=> array
		(
			'[6]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/6]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'7'		=> array
	(
		'tags'	=> array
		(
			'[7]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/7]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'8'		=> array
	(
		'tags'	=> array
		(
			'[8]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/8]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'9'		=> array
	(
		'tags'	=> array
		(
			'[9]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/9]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'10'	=> array
	(
		'tags'	=> array
		(
			'[10]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/10]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'11'	=> array
	(
		'tags'	=> array
		(
			'[11]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/11]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'12'	=> array
	(
		'tags'	=> array
		(
			'[12]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/12]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'13'	=> array
	(
		'tags'	=> array
		(
			'[13]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/13]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'14'	=> array
	(
		'tags'	=> array
		(
			'[14]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/14]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'15'	=> array
	(
		'tags'	=> array
		(
			'[15]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/15]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'a'		=> array
	(
		'tags'	=> array
		(
			'<u:https{,1}://(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'	=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => 'h'),
			'<u:www.(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'			=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => 'w'),
			'[url]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\)*){1,}>[/url]'	=> array ('actions' => array ('-' => Umen\Action::ALONE)),
			'[url=<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'		=> array ('actions' => array ('-' => Umen\Action::START)),
			'[urli=<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'		=> array ('actions' => array ('-' => Umen\Action::START), 'flag' => 'i'),
			'[/url]'													=> array ('actions' => array ('+' => Umen\Action::STOP)),
			'[/urli]'													=> array ('actions' => array ('+' => Umen\Action::STOP), 'flag' => 'i')
		)
	),
	'align'	=> array
	(
		'tags'	=> array
		(
			'[align=center]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => 'c'),
			'[align=left]'		=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => 'l'),
			'[align=right]'		=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => 'r'),
			'[/align]'			=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'[b]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/b]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'box'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[box=<t:(0-9A-Za-zÀ-ÿ ){1,}>]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/box]'						=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'c'		=> array
	(
		'tags'	=> array
		(
			'[center]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/center]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'cmd'	=> array
	(
		'onInverse'	=> 'umenMarkupCmdInverse',
		'tags'		=> array
		(
			'[yncMd:159]'	=> array ('actions' => array ('-' => Umen\Action::START)),
			'[/yncMd:159]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'color'	=> array
	(
		'tags'	=> array
		(
			'[color=<h:(0-9A-Fa-f){3}>]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => '3'),
			'[color=<h:(0-9A-Fa-f){6}>]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => '6'),
			'[color=#<h:(0-9A-Fa-f){3}>]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => '#3'),
			'[color=#<h:(0-9A-Fa-f){6}>]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START), 'flag' => '#6'),
			'[/color]'						=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'em'	=> array
	(
		'tags'	=> array
		(
			'[em]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/em]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'flash'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[flash=<x:(0-9){1,}>,<y:(0-9){1,}>]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'	=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => 's'),
			'[flash]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'								=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	),
	'font'	=> array
	(
		'tags'	=> array
		(
			'[font=<p:(0-9){1,}>]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/font]'				=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'hr'	=> array
	(
		'tags'	=> array
		(
			'[hr]'	=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'[i]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/i]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'img'	=> array
	(
		'limit'	=> 100,
		'tags'	=> array
		(
			'[img=<p:(0-9){1,}>]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'	=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => 's'),
			'[img]<u:(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'				=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	),
	'list'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[list]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[#]'		=> array ('actions' => array ('+' => Umen\Action::STEP), 'flag' => 'o'),
			'[*]'		=> array ('actions' => array ('+' => Umen\Action::STEP), 'flag' => 'u'),
			'[/list]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'modo'	=> array
	(
		'limit'		=> 20,
		'onConvert'	=> 'umenMarkupModoConvert',
		'tags'		=> array
		(
			'[modo]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/modo]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'poll'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[sondage=<i:(0-9){1,}>]'	=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	),
	'pre'	=> array
	(
		'tags'	=> array
		(
			'[pre]'		=> array ('actions' => array ('-' => Umen\Action::START), 'switch' => 'pre'),
			'[/pre]'	=> array ('actions' => array ('pre+' => Umen\Action::STOP), 'switch' => '')
		)
	),
	'quote'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[quote]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/quote]'	=> array ('actions' => array ('+' => Umen\Action::STOP)),
			'[cite]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/cite]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'ref'	=> array
	(
		'tags'	=> array
		(
			'./<n:(0-9){1,}>'	=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	),
	's'		=> array
	(
		'tags'	=> array
		(
			'[s]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/s]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'slap'	=> array
	(
		'tags'	=> array
		(
			'!slap <u:(0-9A-Za-zÀ-ÿ ){1,}>'	=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	),
	'smile'	=> array
	(
		'tags'	=> array
		(
			':D'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '0'),
			':\\('						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '1'),
			':o'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '2'),
			':)'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '3'),
			':p'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '4'),
			';)'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '5'),
			'=)'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '6'),
			'%)'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '7'),
			':|'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '8'),
			':S'						=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => '9'),
			'##<n:(0-9A-Za-z){1,}>##'	=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => 'c'),
			'#<n:(0-9A-Za-z){1,}>#'		=> array ('actions' => array ('-' => Umen\Action::ALONE), 'flag' => 'n')
		)
	),
	'spoil'	=> array
	(
		'tags'	=> array
		(
			'[spoiler]'		=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/spoiler]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'src'	=> array
	(
		'limit'	=> 10,
		'tags'	=> array
		(
			'[source=<l:(0-9a-zA-Z){1,}>]'	=> array ('actions' => array ('-' => Umen\Action::START), 'switch' => 'src'),
			'[/source]'						=> array ('actions' => array ('src+' => Umen\Action::STOP), 'switch' => '')
		)
	),
	'sub'	=> array
	(
		'tags'	=> array
		(
			'[sub]'		=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/sub]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'sup'	=> array
	(
		'tags'	=> array
		(
			'[sup]'		=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/sup]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'table'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[table]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[^]'		=> array ('actions' => array ('+' => Umen\Action::STEP), 'flag' => 'h'),
			'[|]'		=> array ('actions' => array ('+' => Umen\Action::STEP), 'flag' => 'c'),
			'[-]'		=> array ('actions' => array ('+' => Umen\Action::STEP), 'flag' => 'r'),
			'[/table]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'u'		=> array
	(
		'tags'	=> array
		(
			'[u]'	=> array ('actions' => array ('-' => Umen\Action::START, '+' => Umen\Action::START)),
			'[/u]'	=> array ('actions' => array ('+' => Umen\Action::STOP))
		)
	),
	'uni'	=> array
	(
		'tags'	=> array
		(
			'&amp;#<c:(a-z0-9){1,}>;'	=> array ('actions' => array ('-' => Umen\Action::ALONE, '+' => Umen\Action::ALONE))
		)
	),
	'yt'	=> array
	(
		'tags'	=> array
		(
			'[youtube]<i:(-0-9A-Za-z_){1,}>[/youtube]'																			=> array ('actions' => array ('-' => Umen\Action::ALONE)),
			'[youtube]http://www.youtube.com/watch?v=<i:(-0-9A-Za-z_){1,}>[/youtube]'											=> array ('actions' => array ('-' => Umen\Action::ALONE)),
			'[youtube]http://www.youtube.com/watch?v=<i:(-0-9A-Za-z_){1,}>(&#)(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){}[/youtube]'	=> array ('actions' => array ('-' => Umen\Action::ALONE))
		)
	)
*/
);

function	umenMarkupTestSometagConvert ($action, $flag, $captures, $custom)
{
	return false;
}

?>
