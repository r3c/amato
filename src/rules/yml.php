<?php

require_once ('src/mapa.php');

/*
** String parsing parameters character classes, as name => characters.
*/
$mapaClassesYML = array
(
	'alnum'	=> '+0-9A-Za-z',
	'any'	=> '-',
	'hex'	=> '+0-9A-Fa-f',
	'int'	=> '+0-9',
	'text'	=> "-\r\n[]",
	'url'	=> '+0-9A-Za-z-._~:/?#@!$&\'()*+,;='
);

/*
** String parsing rules for each available tag, as name => properties
**   .decode:	optional flag to disable tag decoding
**   .tags:		matching tag patterns, as pattern => behavior
**     .0:	tag type
**     .1:	optional custom identifier
*/
$mapaRulesYML = array
(
	'!'		=> array
	(
		'tags'	=> array
		(
			'\\\\(any)'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'.'		=> array
	(
		'tags'	=> array
		(
			"\r\n"	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'0'		=> array
	(
		'tags'	=> array
		(
			'[0]'	=> array (MAPA_TYPE_BEGIN),
			'[/0]'	=> array (MAPA_TYPE_END)
		)
	),
	'1'		=> array
	(
		'tags'	=> array
		(
			'[1]'	=> array (MAPA_TYPE_BEGIN),
			'[/1]'	=> array (MAPA_TYPE_END)
		)
	),
	'2'		=> array
	(
		'tags'	=> array
		(
			'[2]'	=> array (MAPA_TYPE_BEGIN),
			'[/2]'	=> array (MAPA_TYPE_END)
		)
	),
	'3'		=> array
	(
		'tags'	=> array
		(
			'[3]'	=> array (MAPA_TYPE_BEGIN),
			'[/3]'	=> array (MAPA_TYPE_END)
		)
	),
	'4'		=> array
	(
		'tags'	=> array
		(
			'[4]'	=> array (MAPA_TYPE_BEGIN),
			'[/4]'	=> array (MAPA_TYPE_END)
		)
	),
	'5'		=> array
	(
		'tags'	=> array
		(
			'[5]'	=> array (MAPA_TYPE_BEGIN),
			'[/5]'	=> array (MAPA_TYPE_END)
		)
	),
	'6'		=> array
	(
		'tags'	=> array
		(
			'[6]'	=> array (MAPA_TYPE_BEGIN),
			'[/6]'	=> array (MAPA_TYPE_END)
		)
	),
	'7'		=> array
	(
		'tags'	=> array
		(
			'[7]'	=> array (MAPA_TYPE_BEGIN),
			'[/7]'	=> array (MAPA_TYPE_END)
		)
	),
	'8'		=> array
	(
		'tags'	=> array
		(
			'[8]'	=> array (MAPA_TYPE_BEGIN),
			'[/8]'	=> array (MAPA_TYPE_END)
		)
	),
	'9'		=> array
	(
		'tags'	=> array
		(
			'[9]'	=> array (MAPA_TYPE_BEGIN),
			'[/9]'	=> array (MAPA_TYPE_END)
		)
	),
	'10'	=> array
	(
		'tags'	=> array
		(
			'[10]'	=> array (MAPA_TYPE_BEGIN),
			'[/10]'	=> array (MAPA_TYPE_END)
		)
	),
	'11'	=> array
	(
		'tags'	=> array
		(
			'[11]'	=> array (MAPA_TYPE_BEGIN),
			'[/11]'	=> array (MAPA_TYPE_END)
		)
	),
	'12'	=> array
	(
		'tags'	=> array
		(
			'[12]'	=> array (MAPA_TYPE_BEGIN),
			'[/12]'	=> array (MAPA_TYPE_END)
		)
	),
	'13'	=> array
	(
		'tags'	=> array
		(
			'[13]'	=> array (MAPA_TYPE_BEGIN),
			'[/13]'	=> array (MAPA_TYPE_END)
		)
	),
	'14'	=> array
	(
		'tags'	=> array
		(
			'[14]'	=> array (MAPA_TYPE_BEGIN),
			'[/14]'	=> array (MAPA_TYPE_END)
		)
	),
	'15'	=> array
	(
		'tags'	=> array
		(
			'[15]'	=> array (MAPA_TYPE_BEGIN),
			'[/15]'	=> array (MAPA_TYPE_END)
		)
	),
	'a'		=> array
	(
		'tags'	=> array
		(
//			'http://(url*)'		=> array (MAPA_TYPE_SINGLE),
			'[url](url*)[/url]'	=> array (MAPA_TYPE_SINGLE),
			'[url=(url*)]'		=> array (MAPA_TYPE_BEGIN),
			'[urli=(url*)]'		=> array (MAPA_TYPE_BEGIN, 'i'),
			'[/url]'			=> array (MAPA_TYPE_END),
			'[/urli]'			=> array (MAPA_TYPE_END, 'i')
		)
	),
	'align'	=> array
	(
		'tags'	=> array
		(
			'[align=center]'	=> array (MAPA_TYPE_BEGIN, 'c'),
			'[align=left]'		=> array (MAPA_TYPE_BEGIN, 'l'),
			'[align=right]'		=> array (MAPA_TYPE_BEGIN, 'r'),
			'[/align]'			=> array (MAPA_TYPE_END)
		)
	),
	'b'		=> array
	(
		'tags'	=> array
		(
			'[b]'	=> array (MAPA_TYPE_BEGIN),
			'[/b]'	=> array (MAPA_TYPE_END)
		)
	),
	'box'	=> array
	(
		'tags'	=> array
		(
			'[box=(text*)]'	=> array (MAPA_TYPE_BEGIN),
			'[/box]'		=> array (MAPA_TYPE_END)
		)
	),
	'c'		=> array
	(
		'tags'	=> array
		(
			'[center]'	=> array (MAPA_TYPE_BEGIN),
			'[/center]'	=> array (MAPA_TYPE_END)
		)
	),
	'cmd'	=> array
	(
		'decode'	=> false,
		'tags'		=> array
		(
			'[yncMd:159]'	=> array (MAPA_TYPE_BEGIN),
			'[/yncMd:159]'	=> array (MAPA_TYPE_END)
		)
	),
	'color'	=> array
	(
		'tags'	=> array
		(
			'[color=(hex*)]'	=> array (MAPA_TYPE_BEGIN),
			'[color=#(hex*)]'	=> array (MAPA_TYPE_BEGIN, '#'),
			'[/color]'			=> array (MAPA_TYPE_END)
		)
	),
	'em'	=> array
	(
		'tags'	=> array
		(
			'[em]'	=> array (MAPA_TYPE_BEGIN),
			'[/em]'	=> array (MAPA_TYPE_END)
		)
	),
	'flash'	=> array
	(
		'tags'	=> array
		(
			'[flash](url*)[/flash]'					=> array (MAPA_TYPE_SINGLE),
			'[flash=(int*),(int*)](url*)[/flash]'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'font'	=> array
	(
		'tags'	=> array
		(
			'[font=(int*)]'	=> array (MAPA_TYPE_BEGIN),
			'[/font]'		=> array (MAPA_TYPE_END)
		)
	),
	'hr'	=> array
	(
		'tags'	=> array
		(
			'[hr]'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'i'		=> array
	(
		'tags'	=> array
		(
			'[i]'	=> array (MAPA_TYPE_BEGIN),
			'[/i]'	=> array (MAPA_TYPE_END)
		)
	),
	'img'	=> array
	(
		'tags'	=> array
		(
			'[img=(int*)](url*)[/img]'	=> array (MAPA_TYPE_SINGLE),
			'[img](url*)[/img]'			=> array (MAPA_TYPE_SINGLE)
		)
	),
	'list'	=> array
	(
		'tags'	=> array
		(
			'[list]'	=> array (MAPA_TYPE_BEGIN),
			'#'			=> array (MAPA_TYPE_BETWEEN, 'o'),
			'*'			=> array (MAPA_TYPE_BETWEEN, 'u'),
			'[/list]'	=> array (MAPA_TYPE_END)
		)
	),
	'modo'	=> array
	(
		'tags'	=> array
		(
			'[modo]'	=> array (MAPA_TYPE_BEGIN),
			'[/modo]'	=> array (MAPA_TYPE_END)
		)
	),
	'poll'	=> array
	(
		'tags'	=> array
		(
			'[sondage=(int*)]'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'pre'	=> array
	(
		'tags'	=> array
		(
			'[pre]'		=> array (MAPA_TYPE_LITERAL, '1'),
			'[/pre]'	=> array (MAPA_TYPE_LITERAL, '2')
		)
	),
	'quote'	=> array
	(
		'tags'	=> array
		(
			'[cite]'	=> array (MAPA_TYPE_BEGIN),
			'[/cite]'	=> array (MAPA_TYPE_END),
			'[quote]'	=> array (MAPA_TYPE_BEGIN),
			'[/quote]'	=> array (MAPA_TYPE_END)
		)
	),
	'ref'	=> array
	(
		'tags'	=> array
		(
			'./(int*)'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	's'		=> array
	(
		'tags'	=> array
		(
			'[s]'	=> array (MAPA_TYPE_BEGIN),
			'[/s]'	=> array (MAPA_TYPE_END)
		)
	),
	'slap'	=> array
	(
		'tags'	=> array
		(
			'!slap (text*)'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'smile'	=> array
	(
		'tags'	=> array
		(
			':D'			=> array (MAPA_TYPE_SINGLE, '0'),
			':\\('			=> array (MAPA_TYPE_SINGLE, '1'),
			':o'			=> array (MAPA_TYPE_SINGLE, '2'),
			':)'			=> array (MAPA_TYPE_SINGLE, '3'),
			':p'			=> array (MAPA_TYPE_SINGLE, '4'),
			';)'			=> array (MAPA_TYPE_SINGLE, '5'),
			'=)'			=> array (MAPA_TYPE_SINGLE, '6'),
			'%)'			=> array (MAPA_TYPE_SINGLE, '7'),
			':|'			=> array (MAPA_TYPE_SINGLE, '8'),
			':S'			=> array (MAPA_TYPE_SINGLE, '9'),
			'##(alnum*)##'	=> array (MAPA_TYPE_SINGLE, 'c'),
			'#(alnum*)#'	=> array (MAPA_TYPE_SINGLE, 'n')
		)
	),
	'spoil'	=> array
	(
		'tags'	=> array
		(
			'[spoiler]'		=> array (MAPA_TYPE_BEGIN),
			'[/spoiler]'	=> array (MAPA_TYPE_END)
		)
	),
	'src'	=> array
	(
		'tags'	=> array
		(
			'[source=(int*)]'	=> array (MAPA_TYPE_SINGLE)
		)
	),
	'sub'	=> array
	(
		'tags'	=> array
		(
			'[sub]'		=> array (MAPA_TYPE_BEGIN),
			'[/sub]'	=> array (MAPA_TYPE_END)
		)
	),
	'sup'	=> array
	(
		'tags'	=> array
		(
			'[sup]'		=> array (MAPA_TYPE_BEGIN),
			'[/sup]'	=> array (MAPA_TYPE_END)
		)
	),
	'u'		=> array
	(
		'tags'	=> array
		(
			'[u]'	=> array (MAPA_TYPE_BEGIN),
			'[/u]'	=> array (MAPA_TYPE_END)
		)
	)
);

?>
