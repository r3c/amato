<?php

require (dirname (__FILE__) . '/bbcode.php');

$syntax = array
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
		array (Amato\Tag::PULSE, "\n#", array ('o' => '1')),
		array (Amato\Tag::PULSE, "\n##", array ('o' => '2')),
		array (Amato\Tag::PULSE, "\n###", array ('o' => '3')),
		array (Amato\Tag::PULSE, "\n####", array ('o' => '4')),
		array (Amato\Tag::PULSE, "\n*", array ('u' => '1')),
		array (Amato\Tag::PULSE, "\n**", array ('u' => '2')),
		array (Amato\Tag::PULSE, "\n***", array ('u' => '3')),
		array (Amato\Tag::PULSE, "\n****", array ('u' => '4')),
		array (Amato\Tag::STEP, '#', array ('o' => '1')),
		array (Amato\Tag::STEP, '##', array ('o' => '2')),
		array (Amato\Tag::STEP, '###', array ('o' => '3')),
		array (Amato\Tag::STEP, '####', array ('o' => '4')),
		array (Amato\Tag::STEP, '*', array ('u' => '1')),
		array (Amato\Tag::STEP, '**', array ('u' => '2')),
		array (Amato\Tag::STEP, '***', array ('u' => '3')),
		array (Amato\Tag::STEP, '****', array ('u' => '4')),
		array (Amato\Tag::STOP, "\n")
	),
	'pre' => array
	(
		array (Amato\Tag::ALONE, ":::\n<.*:b>\n:::")
	),
	's' => array
	(
		array (Amato\Tag::FLIP, '~~')
	),
	'u' => array
	(
		array (Amato\Tag::FLIP, '__')
	)
) + $syntax;

?>
