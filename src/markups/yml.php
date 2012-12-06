<?php

/*
** String parsing rules for each available tag, as name => properties
**   .limit:	optional allowed number of uses of this tag, default is 100
**   .tags:		tag patterns list, as pattern => options
**		.actions:	actions for context conditions as condition => action
**		.flag:		tag flag
**		.switch:	context switch name
*/
$ymlMarkup = array
(
	'.'		=> array
	(
//		'limit'	=> 100,
		'tags'	=> array
		(
			"\n"	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'0'		=> array
	(
		'tags'	=> array
		(
			'[0]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/0]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'1'		=> array
	(
		'tags'	=> array
		(
			'[1]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/1]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'2'		=> array
	(
		'tags'	=> array
		(
			'[2]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/2]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'3'		=> array
	(
		'tags'	=> array
		(
			'[3]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/3]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'4'		=> array
	(
		'tags'	=> array
		(
			'[4]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/4]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'5'		=> array
	(
		'tags'	=> array
		(
			'[5]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/5]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'6'		=> array
	(
		'tags'	=> array
		(
			'[6]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/6]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'7'		=> array
	(
		'tags'	=> array
		(
			'[7]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/7]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'8'		=> array
	(
		'tags'	=> array
		(
			'[8]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/8]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'9'		=> array
	(
		'tags'	=> array
		(
			'[9]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/9]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'10'	=> array
	(
		'tags'	=> array
		(
			'[10]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/10]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'11'	=> array
	(
		'tags'	=> array
		(
			'[11]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/11]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'12'	=> array
	(
		'tags'	=> array
		(
			'[12]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/12]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'13'	=> array
	(
		'tags'	=> array
		(
			'[13]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/13]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'14'	=> array
	(
		'tags'	=> array
		(
			'[14]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/14]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'15'	=> array
	(
		'tags'	=> array
		(
			'[15]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/15]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'a'		=> array
	(
		'tags'	=> array
		(
			'<https{,1}://(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => 'h'),
			'<www.(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'			=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => 'w'),
			'[url]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\)*){1,}>[/url]'		=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE)),
			'[url=<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'			=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[urli=<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'			=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => 'i'),
			'[/url]'													=> array ('actions' => array ('+' => UMEN_ACTION_STOP)),
			'[/urli]'													=> array ('actions' => array ('+' => UMEN_ACTION_STOP), 'flag' => 'i')
		)
	),
	'align'	=> array
	(
		'tags'	=> array
		(
			'[align=center]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => 'c'),
			'[align=left]'		=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => 'l'),
			'[align=right]'		=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => 'r'),
			'[/align]'			=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'[b]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/b]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'box'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[box=<(0-9A-Za-zÀ-ÿ ){1,}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/box]'						=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'c'		=> array
	(
		'tags'	=> array
		(
			'[center]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/center]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'cmd'	=> array
	(
		'onInverse'	=> 'umenMarkupCmdInverse',
		'tags'		=> array
		(
			'[yncMd:159]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/yncMd:159]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'color'	=> array
	(
		'tags'	=> array
		(
			'[color=<(0-9A-Fa-f){3}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => '3'),
			'[color=<(0-9A-Fa-f){6}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => '6'),
			'[color=#<(0-9A-Fa-f){3}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => '#3'),
			'[color=#<(0-9A-Fa-f){6}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'flag' => '#6'),
			'[/color]'					=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'em'	=> array
	(
		'tags'	=> array
		(
			'[em]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/em]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'flash'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[flash=<(0-9){1,}>,<(0-9){1,}>]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => 's'),
			'[flash]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'							=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'font'	=> array
	(
		'tags'	=> array
		(
			'[font=<(0-9){1,}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/font]'				=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'hr'	=> array
	(
		'tags'	=> array
		(
			'[hr]'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'[i]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/i]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'img'	=> array
	(
		'limit'	=> 100,
		'tags'	=> array
		(
			'[img=<(0-9){1,}>]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => 's'),
			'[img]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'				=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'list'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[list]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[#]'		=> array ('actions' => array ('+' => UMEN_ACTION_STEP), 'flag' => 'o'),
			'[*]'		=> array ('actions' => array ('+' => UMEN_ACTION_STEP), 'flag' => 'u'),
			'[/list]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'modo'	=> array
	(
		'limit'		=> 20,
		'onConvert'	=> 'umenMarkupModoConvert',
		'tags'		=> array
		(
			'[modo]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/modo]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'poll'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[sondage=<(0-9){1,}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'pre'	=> array
	(
		'tags'	=> array
		(
			'[pre]'		=> array ('actions' => array ('-' => UMEN_ACTION_START), 'switch' => 'pre'),
			'[/pre]'	=> array ('actions' => array ('pre+' => UMEN_ACTION_STOP), 'switch' => '')
		)
	),
	'quote'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[quote]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/quote]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP)),
			'[cite]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/cite]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'ref'	=> array
	(
		'tags'	=> array
		(
			'./<(0-9){1,}>'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	's'		=> array
	(
		'tags'	=> array
		(
			'[s]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/s]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'slap'	=> array
	(
		'tags'	=> array
		(
			'!slap <(0-9A-Za-zÀ-ÿ ){1,}>'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'smile'	=> array
	(
		'tags'	=> array
		(
			':D'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '0'),
			':\\('					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '1'),
			':o'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '2'),
			':)'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '3'),
			':p'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '4'),
			';)'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '5'),
			'=)'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '6'),
			'%)'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '7'),
			':|'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '8'),
			':S'					=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => '9'),
			'##<(0-9A-Za-z){1,}>##'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => 'c'),
			'#<(0-9A-Za-z){1,}>#'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE), 'flag' => 'n')
		)
	),
	'spoil'	=> array
	(
		'tags'	=> array
		(
			'[spoiler]'		=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/spoiler]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'src'	=> array
	(
		'limit'	=> 10,
		'tags'	=> array
		(
			'[source=<(0-9a-zA-Z){1,}>]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START), 'switch' => 'src'),
			'[/source]'						=> array ('actions' => array ('src+' => UMEN_ACTION_STOP), 'switch' => '')
		)
	),
	'sub'	=> array
	(
		'tags'	=> array
		(
			'[sub]'		=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/sub]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'sup'	=> array
	(
		'tags'	=> array
		(
			'[sup]'		=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/sup]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'table'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[table]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[^]'		=> array ('actions' => array ('+' => UMEN_ACTION_STEP), 'flag' => 'h'),
			'[|]'		=> array ('actions' => array ('+' => UMEN_ACTION_STEP), 'flag' => 'c'),
			'[-]'		=> array ('actions' => array ('+' => UMEN_ACTION_STEP), 'flag' => 'r'),
			'[/table]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'u'		=> array
	(
		'tags'	=> array
		(
			'[u]'	=> array ('actions' => array ('-' => UMEN_ACTION_START, '+' => UMEN_ACTION_START)),
			'[/u]'	=> array ('actions' => array ('+' => UMEN_ACTION_STOP))
		)
	),
	'uni'	=> array
	(
		'tags'	=> array
		(
			'&amp;#<(a-z0-9){1,}>;'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	),
	'yt'	=> array
	(
		'tags'	=> array
		(
			'[youtube]<(-0-9A-Za-z_){1,}>[/youtube]'																			=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE)),
			'[youtube]http://www.youtube.com/watch?v=<(-0-9A-Za-z_){1,}>[/youtube]'												=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE)),
			'[youtube]http://www.youtube.com/watch?v=<(-0-9A-Za-z_){1,}>(&#)(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){}[/youtube]'	=> array ('actions' => array ('-' => UMEN_ACTION_ALONE, '+' => UMEN_ACTION_ALONE))
		)
	)
);

function	umenMarkupCmdInverse ($context, $action, $flag, $captures)
{
	return false;
}

function	umenMarkupModoConvert ($context, $action, $flag, $captures)
{
	if ($action !== UMEN_ACTION_STOP)
		return true;

	return $context['user']['level'] >= 2;
}

?>
