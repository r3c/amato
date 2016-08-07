<?php

require_once ('../src/amato.php');
require_once ('assert/test.php');
require_once ('assert/token.php');

Amato\autoload ();

function test_encoder ($plain_expected, $chains_expected)
{
	static $encoders;

	if (!isset ($encoders))
	{
		$encoders = array
		(
			'compact'	=> new Amato\CompactEncoder (),
			'concat'	=> new Amato\ConcatEncoder (),
			'json'		=> new Amato\JSONEncoder (),
			'sleep'		=> new Amato\SleepEncoder ()
		);
	}

	foreach ($chains_expected as &$chain)
	{
		foreach ($chain[1] as &$marker)
		{
			if (!isset ($marker[1]))
				$marker[1] = array ();
		}
	}

	foreach ($encoders as $name => $encoder)
	{
		$context = '[encoder \'' . $name . '\']';
		$token = $encoder->encode ($plain_expected, $chains_expected);

		assert_test_true ($token !== null, $context . '[token is null]');

		list ($plain, $chains) = $encoder->decode ($token);

		assert_token_equal ($plain, $chains, $plain_expected, $chains_expected, $context);
	}
}

assert_options (ASSERT_BAIL, true);

mb_internal_encoding ('utf-8');

test_encoder ('Hello, World!', array ());
test_encoder ('Hello, World!', array (array ('b', array (array (0), array (13)))));
test_encoder ('ABCDE', array (array ('b', array (array (1), array (2))), array ('i', array (array (3), array (4)))));
test_encoder ('ABCDE', array (array ('b', array (array (1), array (4))), array ('i', array (array (2), array (3)))));
test_encoder ('ABCDE', array (array ('b', array (array (1), array (3))), array ('i', array (array (2), array (4)))));
test_encoder ('X', array (array ('b', array (array (0, array ('a' => 'a', 'b' => '')), array (1)))));

echo 'OK';

?>
