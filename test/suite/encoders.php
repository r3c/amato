<?php

require_once ('../src/amato.php');
require_once ('assert/test.php');
require_once ('assert/token.php');

Amato\autoload ();

function test_encoder ($plain_expected, $markers_expected)
{
	static $encoders;

	if (!isset ($encoders))
	{
		$encoders = array
		(
			'compact'	=> new Amato\CompactEncoder (),
			'json'		=> new Amato\JSONEncoder (),
			'sleep'		=> new Amato\SleepEncoder ()
		);
	}

	foreach ($markers_expected as &$marker)
	{
		if (!isset ($marker[4]))
			$marker[4] = array ();
	}

	foreach ($encoders as $name => $encoder)
	{
		$context = '[plain \'' . str_replace ("\n", ' ', $plain_expected) . '\'][encoder \'' . $name . '\']';
		$token = $encoder->encode ($plain_expected, $markers_expected);

		assert_test_true ($token !== null, $context . '[token is null]');

		list ($plain, $markers) = $encoder->decode ($token);

		assert_token_equal ($plain, $markers, $plain_expected, $markers_expected, $context);
	}
}

assert_options (ASSERT_BAIL, true);

mb_internal_encoding ('utf-8');

test_encoder ('Hello, World!', array ());
test_encoder ('Hello, World!', array (array ('b', 0, true, false), array ('b', 13, false, true)));
test_encoder ('ABCDE', array (array ('b', 1, true, false), array ('b', 2, true, false), array ('i', 3, true, false), array ('i', 4, false, true)));
test_encoder ('ABCDE', array (array ('b', 1, true, false), array ('i', 2, true, false), array ('i', 3, false, true), array ('b', 4, false, true)));
test_encoder ('ABCDE', array (array ('b', 1, true, false), array ('i', 2, true, false), array ('b', 3, false, true), array ('i', 4, false, true)));
test_encoder ('X', array (array ('b', 0, true, false, array ('a' => 'a', 'b' => '')), array ('b', 1, false, true)));

echo 'OK';

?>
