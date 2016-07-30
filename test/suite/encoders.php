<?php

require_once ('../src/amato.php');
require_once ('assert/test.php');
require_once ('assert/token.php');

Amato\autoload ();

function test_encoder ($tags_expected, $plain_expected)
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

	foreach ($tags_expected as &$tag)
	{
		foreach ($tag[1] as &$marker)
		{
			if (!isset ($marker[1]))
				$marker[1] = array ();
		}
	}

	foreach ($encoders as $name => $encoder)
	{
		$context = $name . ' encoder';
		$token = $encoder->encode ($tags_expected, $plain_expected);

		assert ($token !== null, $context . ' - token is null');

		list ($tags, $plain) = $encoder->decode ($token);

		assert_token_equal ($context, $tags, $plain, $tags_expected, $plain_expected);
	}
}

assert_options (ASSERT_BAIL, true);

mb_internal_encoding ('utf-8');

test_encoder (array (), 'Hello, World!');
test_encoder (array (array ('b', array (array (0), array (13)))), 'Hello, World!');
test_encoder (array (array ('b', array (array (1), array (2))), array ('i', array (array (3), array (4)))), 'ABCDE');
test_encoder (array (array ('b', array (array (1), array (4))), array ('i', array (array (2), array (3)))), 'ABCDE');
test_encoder (array (array ('b', array (array (1), array (3))), array ('i', array (array (2), array (4)))), 'ABCDE');
test_encoder (array (array ('b', array (array (0, array ('a' => 'a', 'b' => '')), array (1)))), 'X');

echo 'OK';

?>
