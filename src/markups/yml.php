<?php

define ('YML_TYPE_ALONE',	0);
define ('YML_TYPE_BEGIN',	1);
define ('YML_TYPE_BETWEEN',	2);
define ('YML_TYPE_END',		3);
define ('YML_TYPE_LITERAL',	4);
define ('YML_TYPE_RESUME',	5);
define ('YML_TYPE_SWITCH',	6);

$ymlContext = array
(
	YML_TYPE_ALONE		=> array (UMEN_ACTION_ALONE, UMEN_ACTION_ALONE),
	YML_TYPE_BEGIN		=> array (UMEN_ACTION_START, UMEN_ACTION_START),
	YML_TYPE_BETWEEN	=> array (null, UMEN_ACTION_STEP),
	YML_TYPE_END		=> array (null, UMEN_ACTION_STOP),
	YML_TYPE_LITERAL	=> array (UMEN_ACTION_LITERAL, UMEN_ACTION_LITERAL),
	YML_TYPE_RESUME		=> array (UMEN_ACTION_START, UMEN_ACTION_STEP),
	YML_TYPE_SWITCH		=> array (UMEN_ACTION_START, UMEN_ACTION_STOP)
);

/*
** String parsing rules for each available tag, as name => properties
**   .limit:	optional allowed number of uses of this tag, default is 100
**   .tags:		tag patterns list, as pattern => (type, flag)
*/
$ymlMarkup = array
(
	'.'		=> array
	(
//		'limit'	=> 100,
		'tags'	=> array
		(
			"\n"	=> array (YML_TYPE_ALONE)
		)
	),
	'0'		=> array
	(
		'tags'	=> array
		(
			'[0]'	=> array (YML_TYPE_BEGIN),
			'[/0]'	=> array (YML_TYPE_END)
		)
	),
	'1'		=> array
	(
		'tags'	=> array
		(
			'[1]'	=> array (YML_TYPE_BEGIN),
			'[/1]'	=> array (YML_TYPE_END)
		)
	),
	'2'		=> array
	(
		'tags'	=> array
		(
			'[2]'	=> array (YML_TYPE_BEGIN),
			'[/2]'	=> array (YML_TYPE_END)
		)
	),
	'3'		=> array
	(
		'tags'	=> array
		(
			'[3]'	=> array (YML_TYPE_BEGIN),
			'[/3]'	=> array (YML_TYPE_END)
		)
	),
	'4'		=> array
	(
		'tags'	=> array
		(
			'[4]'	=> array (YML_TYPE_BEGIN),
			'[/4]'	=> array (YML_TYPE_END)
		)
	),
	'5'		=> array
	(
		'tags'	=> array
		(
			'[5]'	=> array (YML_TYPE_BEGIN),
			'[/5]'	=> array (YML_TYPE_END)
		)
	),
	'6'		=> array
	(
		'tags'	=> array
		(
			'[6]'	=> array (YML_TYPE_BEGIN),
			'[/6]'	=> array (YML_TYPE_END)
		)
	),
	'7'		=> array
	(
		'tags'	=> array
		(
			'[7]'	=> array (YML_TYPE_BEGIN),
			'[/7]'	=> array (YML_TYPE_END)
		)
	),
	'8'		=> array
	(
		'tags'	=> array
		(
			'[8]'	=> array (YML_TYPE_BEGIN),
			'[/8]'	=> array (YML_TYPE_END)
		)
	),
	'9'		=> array
	(
		'tags'	=> array
		(
			'[9]'	=> array (YML_TYPE_BEGIN),
			'[/9]'	=> array (YML_TYPE_END)
		)
	),
	'10'	=> array
	(
		'tags'	=> array
		(
			'[10]'	=> array (YML_TYPE_BEGIN),
			'[/10]'	=> array (YML_TYPE_END)
		)
	),
	'11'	=> array
	(
		'tags'	=> array
		(
			'[11]'	=> array (YML_TYPE_BEGIN),
			'[/11]'	=> array (YML_TYPE_END)
		)
	),
	'12'	=> array
	(
		'tags'	=> array
		(
			'[12]'	=> array (YML_TYPE_BEGIN),
			'[/12]'	=> array (YML_TYPE_END)
		)
	),
	'13'	=> array
	(
		'tags'	=> array
		(
			'[13]'	=> array (YML_TYPE_BEGIN),
			'[/13]'	=> array (YML_TYPE_END)
		)
	),
	'14'	=> array
	(
		'tags'	=> array
		(
			'[14]'	=> array (YML_TYPE_BEGIN),
			'[/14]'	=> array (YML_TYPE_END)
		)
	),
	'15'	=> array
	(
		'tags'	=> array
		(
			'[15]'	=> array (YML_TYPE_BEGIN),
			'[/15]'	=> array (YML_TYPE_END)
		)
	),
	'a'		=> array
	(
		'tags'	=> array
		(
			'<https{,1}://(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'	=> array (YML_TYPE_ALONE, 'h'),
			'<www.(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>'			=> array (YML_TYPE_ALONE, 'w'),
			'[url]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\)*){1,}>[/url]'		=> array (YML_TYPE_ALONE),
			'[url=<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'			=> array (YML_TYPE_BEGIN),
			'[urli=<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>]'			=> array (YML_TYPE_BEGIN, 'i'),
			'[/url]'													=> array (YML_TYPE_END),
			'[/urli]'													=> array (YML_TYPE_END, 'i')
		)
	),
	'align'	=> array
	(
		'tags'	=> array
		(
			'[align=center]'	=> array (YML_TYPE_BEGIN, 'c'),
			'[align=left]'		=> array (YML_TYPE_BEGIN, 'l'),
			'[align=right]'		=> array (YML_TYPE_BEGIN, 'r'),
			'[/align]'			=> array (YML_TYPE_END)
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'[b]'	=> array (YML_TYPE_BEGIN),
			'[/b]'	=> array (YML_TYPE_END)
		)
	),
	'box'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[box=<(fixme)>]'	=> array (YML_TYPE_BEGIN),
			'[/box]'			=> array (YML_TYPE_END)
		)
	),
	'c'		=> array
	(
		'tags'	=> array
		(
			'[center]'	=> array (YML_TYPE_BEGIN),
			'[/center]'	=> array (YML_TYPE_END)
		)
	),
	'cmd'	=> array
	(
		'decode'	=> false, // FIXME
		'tags'		=> array
		(
			'[yncMd:159]'	=> array (YML_TYPE_BEGIN),
			'[/yncMd:159]'	=> array (YML_TYPE_END)
		)
	),
	'color'	=> array
	(
		'tags'	=> array
		(
			'[color=<(0-9A-Fa-f){3}>]'	=> array (YML_TYPE_BEGIN, '3'),
			'[color=<(0-9A-Fa-f){6}>]'	=> array (YML_TYPE_BEGIN, '6'),
			'[color=#<(0-9A-Fa-f){3}>]'	=> array (YML_TYPE_BEGIN, '#3'),
			'[color=#<(0-9A-Fa-f){6}>]'	=> array (YML_TYPE_BEGIN, '#6'),
			'[/color]'					=> array (YML_TYPE_END)
		)
	),
	'em'	=> array
	(
		'tags'	=> array
		(
			'[em]'	=> array (YML_TYPE_BEGIN),
			'[/em]'	=> array (YML_TYPE_END)
		)
	),
	'flash'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[flash]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'							=> array (YML_TYPE_ALONE),
			'[flash=<(0-9){1,}>,<(0-9){1,}>]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/flash]'	=> array (YML_TYPE_ALONE)
		)
	),
	'font'	=> array
	(
		'tags'	=> array
		(
			'[font=<(0-9){1,}>]'	=> array (YML_TYPE_BEGIN),
			'[/font]'				=> array (YML_TYPE_END)
		)
	),
	'hr'	=> array
	(
		'tags'	=> array
		(
			'[hr]'	=> array (YML_TYPE_ALONE)
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'[i]'	=> array (YML_TYPE_BEGIN),
			'[/i]'	=> array (YML_TYPE_END)
		)
	),
	'img'	=> array
	(
		'limit'	=> 50,
		'tags'	=> array
		(
			'[img=<(0-9){1,}>]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'	=> array (YML_TYPE_ALONE),
			'[img]<(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){1,}>[/img]'				=> array (YML_TYPE_ALONE)
		)
	),
	'list'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[list]'	=> array (YML_TYPE_BEGIN),
			'[#]'		=> array (YML_TYPE_BETWEEN, 'o'),
			'[*]'		=> array (YML_TYPE_BETWEEN, 'u'),
			'[/list]'	=> array (YML_TYPE_END)
		)
	),
	'modo'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[modo]'	=> array (YML_TYPE_BEGIN),
			'[/modo]'	=> array (YML_TYPE_END)
		)
	),
	'poll'	=> array
	(
		'limit'	=> 5,
		'tags'	=> array
		(
			'[sondage=<(0-9){1,}>]'	=> array (YML_TYPE_ALONE)
		)
	),
	'pre'	=> array
	(
		'tags'	=> array
		(
			'[pre]'		=> array (YML_TYPE_LITERAL, '1'),
			'[/pre]'	=> array (YML_TYPE_LITERAL, '2')
		)
	),
	'quote'	=> array
	(
		'limit'	=> 20,
		'tags'	=> array
		(
			'[cite]'	=> array (YML_TYPE_BEGIN, 'c'),
			'[/cite]'	=> array (YML_TYPE_END, 'c'),
			'[quote]'	=> array (YML_TYPE_BEGIN),
			'[/quote]'	=> array (YML_TYPE_END)
		)
	),
	'ref'	=> array
	(
		'tags'	=> array
		(
			'./<(0-9){1,}>'	=> array (YML_TYPE_ALONE)
		)
	),
	's'		=> array
	(
		'tags'	=> array
		(
			'[s]'	=> array (YML_TYPE_BEGIN),
			'[/s]'	=> array (YML_TYPE_END)
		)
	),
	'slap'	=> array
	(
		'tags'	=> array
		(
			'!slap <(fixme)>'	=> array (YML_TYPE_ALONE)
		)
	),
	'smile'	=> array
	(
		'tags'	=> array
		(
			':D'					=> array (YML_TYPE_ALONE, '0'),
			':\\('					=> array (YML_TYPE_ALONE, '1'),
			':o'					=> array (YML_TYPE_ALONE, '2'),
			':)'					=> array (YML_TYPE_ALONE, '3'),
			':p'					=> array (YML_TYPE_ALONE, '4'),
			';)'					=> array (YML_TYPE_ALONE, '5'),
			'=)'					=> array (YML_TYPE_ALONE, '6'),
			'%)'					=> array (YML_TYPE_ALONE, '7'),
			':|'					=> array (YML_TYPE_ALONE, '8'),
			':S'					=> array (YML_TYPE_ALONE, '9'),
			'##<(0-9A-Za-z){1,}>##'	=> array (YML_TYPE_ALONE, 'c'),
			'#<(0-9A-Za-z){1,}>#'	=> array (YML_TYPE_ALONE, 'n')
		)
	),
	'spoil'	=> array
	(
		'tags'	=> array
		(
			'[spoiler]'		=> array (YML_TYPE_BEGIN),
			'[/spoiler]'	=> array (YML_TYPE_END)
		)
	),
	'src'	=> array
	(
		'limit'	=> 10,
		'tags'	=> array
		(
			'[source=<(0-9){1,}>]'	=> array (YML_TYPE_ALONE)
		)
	),
	'sub'	=> array
	(
		'tags'	=> array
		(
			'[sub]'		=> array (YML_TYPE_BEGIN),
			'[/sub]'	=> array (YML_TYPE_END)
		)
	),
	'sup'	=> array
	(
		'tags'	=> array
		(
			'[sup]'		=> array (YML_TYPE_BEGIN),
			'[/sup]'	=> array (YML_TYPE_END)
		)
	),
	'table'	=> array
	(
		'limit'	=> 200,
		'tags'	=> array
		(
			'[table]'	=> array (YML_TYPE_BEGIN),
			'[^]'		=> array (YML_TYPE_BETWEEN, 'h'),
			'[|]'		=> array (YML_TYPE_BETWEEN, 'c'),
			'[-]'		=> array (YML_TYPE_BETWEEN, 'r'),
			'[/table]'	=> array (YML_TYPE_END)
		)
	),
	'u'		=> array
	(
		'tags'	=> array
		(
			'[u]'	=> array (YML_TYPE_BEGIN),
			'[/u]'	=> array (YML_TYPE_END)
		)
	),
	'uni'	=> array
	(
		'tags'	=> array
		(
			'&amp;#<(a-z0-9){1,}>;'	=> array (YML_TYPE_ALONE)
		)
	),
	'yt'	=> array
	(
		'tags'	=> array
		(
			'[youtube]<(-0-9A-Za-z_){1,}>[/youtube]'																			=> array (YML_TYPE_ALONE),
			'[youtube]http://www.youtube.com/watch?v=<(-0-9A-Za-z_){1,}>[/youtube]'												=> array (YML_TYPE_ALONE),
			'[youtube]http://www.youtube.com/watch?v=<(-0-9A-Za-z_){1,}>(&#)(-0-9A-Za-z._~:/?#@!$%&\'*+,;=(\\)*){}[/youtube]'	=> array (YML_TYPE_ALONE)
		)
	)
);

?>
