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
$syntax = array
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
	)
);

function	umenMarkupTestSometagConvert ($action, $flag, $captures, $custom)
{
	return false;
}

?>
