<?php

$tags_pattern_scheme = '[+.0-9A-Za-z]{1,16}%://';
$tags_pattern_url = '([!%#$%%&\'()*+,./0-9%:;=?@_~-]|\\pL){1,}';
$tags = array
(
	'.' => array
	(
		array (Amato\Tag::ALONE, "\n")
	),
	'a' => array
	(
		array (Amato\Tag::ALONE, "[url]<$tags_pattern_scheme$tags_pattern_url:u>[/url]"),
		array (Amato\Tag::ALONE, "<https?%://$tags_pattern_url:u>"),
		array (Amato\Tag::ALONE, "<www\\.$tags_pattern_url:u>"),
		array (Amato\Tag::START, "[url=<$tags_pattern_scheme$tags_pattern_url:u>]"),
		array (Amato\Tag::STOP, '[/url]')
	),
	'align' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[align=center]", array ('w' => 'c')),
		array (Amato\Tag::START, "<\n?#\n>[align=left]", array ('w' => 'l')),
		array (Amato\Tag::START, "<\n?#\n>[align=justify]", array ('w' => 'j')),
		array (Amato\Tag::START, "<\n?#\n>[align=right]", array ('w' => 'r')),
		array (Amato\Tag::STOP, "<\n?#\n>[/align]")
	),
	'b' => array
	(
		array (Amato\Tag::START, '[b]'),
		array (Amato\Tag::STOP, '[/b]')
	),
	'c' => array
	(
		array (Amato\Tag::START, '[color=<%#?#><[0-9A-Fa-f]{3}:h>]'),
		array (Amato\Tag::START, '[color=<%#?#><[0-9A-Fa-f]{6}:h>]'),
		array (Amato\Tag::STOP, '[/color]')
	),
	'center' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[center]"),
		array (Amato\Tag::STOP, "<\n?#\n>[/center]")
	),
	'code' => array
	(
		array (Amato\Tag::ALONE, "<\n?#\n>[code=<[0-9a-zA-Z]+:l>]<.*?:b>[/code]")
	),
	'emoji' => array
	(
		array (Amato\Tag::ALONE, ':D', array ('n' => 'grin'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ':\\(', array ('n' => 'sad'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ':o', array ('n' => 'embarrassed'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ':)', array ('n' => 'smile'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ':p', array ('n' => 'tongue'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ';)', array ('n' => 'wink'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, '=)', array ('n' => 'happy'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, '%)', array ('n' => 'cheeky'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ':|', array ('n' => 'neutral'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, ':S', array ('n' => 'sorry'), 'amato_tag_bbcode_emoji_convert'),
		array (Amato\Tag::ALONE, '#<[0-9A-Za-z]+:n>#', 'amato_tag_bbcode_emoji_convert')
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
		array (Amato\Tag::ALONE, "[img=<[0-9]+:p>]<https?%://$tags_pattern_url:u>[/img]"),
		array (Amato\Tag::ALONE, "[img]<https?%://$tags_pattern_url:u>[/img]")
	),
	'list' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[list]"),
		array (Amato\Tag::STEP, "<\n?#\n>[#]", array ('t' => 'o')),
		array (Amato\Tag::STEP, "<\n?#\n>[*]", array ('t' => 'u')),
		array (Amato\Tag::STOP, "<\n?#\n>[/list]")
	),
	'pre' => array
	(
		array (Amato\Tag::ALONE, "<\n?#\n>[pre]<.*?:b>[/pre]")
	),
	'quote' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[quote]"),
		array (Amato\Tag::STOP, "<\n?#\n>[/quote]")
	),
	's' => array
	(
		array (Amato\Tag::START, '[s]'),
		array (Amato\Tag::STOP, '[/s]')
	),
	'spoil' => array
	(
		array (Amato\Tag::START, '[spoiler]'),
		array (Amato\Tag::STOP, '[/spoiler]')

	),
	'table' => array
	(
		array (Amato\Tag::START, "<\n?#\n>[table]"),
		array (Amato\Tag::STEP, "<\n?#\n>[|]", array ('t' => 'c')),
		array (Amato\Tag::STEP, "<\n?#\n>[^]", array ('t' => 'h')),
		array (Amato\Tag::STEP, "<\n?#\n>[-]", array ('t' => 'r')),
		array (Amato\Tag::STOP, "<\n?#\n>[/table]")
	),
	'u' => array
	(
		array (Amato\Tag::START, '[u]'),
		array (Amato\Tag::STOP, '[/u]')
	)
);

function amato_tag_bbcode_emoji_convert ($type, &$params, $context)
{
	return in_array ($params['n'], array ('grin', 'sad', 'embarrassed', 'smile', 'tongue', 'wink', 'happy', 'cheeky', 'neutral', 'sorry'));
}

?>
