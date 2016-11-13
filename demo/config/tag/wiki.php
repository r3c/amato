<?php

require (dirname (__FILE__) . '/bbcode.php');

$tags = array
(
	'b' => array
	(
		array (Amato\Tag::FLIP, '**')
	),
	'i' => array
	(
		array (Amato\Tag::FLIP, '//')
	),
	'list' => array
	(
		array (Amato\Tag::PULSE, "\n#", array ('t' => 'o')),
		array (Amato\Tag::PULSE, "\n*", array ('t' => 'u')),
		array (Amato\Tag::STEP, "#", array ('t' => 'o')),
		array (Amato\Tag::STEP, "*", array ('t' => 'u')),
		array (Amato\Tag::STOP, "\n")
	),
	'pre' => array
	(
		array (Amato\Tag::ALONE, ":::\n<.*:b>\n:::")
	),
	's' => array
	(
		array (Amato\Tag::FLIP, '--')
	),
	'u' => array
	(
		array (Amato\Tag::FLIP, '__')
	)
) + $tags;

?>
