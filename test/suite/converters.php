<?php

require_once ('../src/amato.php');
require_once ('assert/test.php');
require_once ('assert/token.php');

/*
** Map of available tags and associated conversion definitions:
** - [tag id => [definition]]
** -- definition: (type, pattern, defaults?, convert?, revert?)
** --- defaults: [name => value]
*/
$syntax = array
(
	/*
	** Pattern expression:
	** <expression:name>
	** (pattern)
	** [characters]
	** {} or {exact} or {min,max} or {min,max,default}
	*/
	'a' => array
	(
		array (Amato\Tag::ALONE, '<u:https?://[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>'),
		array (Amato\Tag::ALONE, '<u:www.[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>'), // How to distinguish from previous pattern? They could be merged if some group + options syntax is allowed
		array (Amato\Tag::ALONE, '[url]<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>[/url]'),
		array (Amato\Tag::START, '[url=<u:[-0-9A-Za-z._~:/?#@!$%%&\'*+,;=(%)*]+>]'),
		array (Amato\Tag::STOP, '[/url]')
	),
	'b' => array
	(
		array (Amato\Tag::FLIP, '__'),
		array (Amato\Tag::START, '[b]'),
		array (Amato\Tag::STOP, '[/b]')
	),
	'hr' => array
	(
		array (Amato\Tag::ALONE, '[hr]')
	),
	'i' => array
	(
		array (Amato\Tag::FLIP, '_'),
		array (Amato\Tag::START, '[i]'),
		array (Amato\Tag::STOP, '[/i]')
	),
	'list' => array
	(
		array (Amato\Tag::PULSE, '##'),
		array (Amato\Tag::STOP, "\n\n")
	),
	's' => array
	(
		array (Amato\Tag::START, '[size=<p:[0-9]+>]'),
		array (Amato\Tag::START, '[size=big]', array ('p' => 200)),
		array (Amato\Tag::STOP, '[/size]')
	)
);

Amato\autoload ();

function test_converter ($markup, $chains_expected, $plain_expected)
{
	global $syntax;
	static $converters;
	static $encoder;

	if (!isset ($encoder))
		$encoder = new Amato\CompactEncoder ();

	if (!isset ($converters))
	{
		$converters = array
		(
			'tag'	=> new Amato\TagConverter ($encoder, new Amato\PregScanner (), $syntax)
		);
	}

	foreach ($converters as $name => $converter)
	{
		$context = $name . ' converter';

		// Convert once and assert result
		$token1 = $converter->convert ($markup);

		list ($plain1, $chains1) = $encoder->decode ($token1);

		assert_token_equal ($context . ' first decode', $plain1, $chains1, $plain_expected, $chains_expected);

		$markup_revert1 = $converter->revert ($token1);

		// Convert twice and assert again
		$token2 = $converter->convert ($markup_revert1);

		list ($plain2, $chains2) = $encoder->decode ($token2);

		assert_token_equal ($context . ' second decode', $plain2, $chains2, $plain_expected, $chains_expected);

		$markup_revert2 = $converter->revert ($token2);

		// Compare first and second reverts
		assert_test_equal ($markup_revert2, $markup_revert1, $context . ' revert');
	}
}

assert_options (ASSERT_BAIL, true);

$charset = 'utf-8';

mb_internal_encoding ($charset);

// Basic tests
test_converter ('Hello, World!', array (), 'Hello, World!');
test_converter ('[b]Hello, World![/b]', array (array ('b', array (array (0), array (13)))), 'Hello, World!');
test_converter ('A[b]B[/b]C[i]D[/i]E', array (array ('b', array (array (1), array (2))), array ('i', array (array (3), array (4)))), 'ABCDE');
test_converter ('A[b]B[i]C[/i]D[/b]E', array (array ('b', array (array (1), array (4))), array ('i', array (array (2), array (3)))), 'ABCDE');
test_converter ('A[b]B[i]C[/b]D[/i]E', array (array ('b', array (array (1), array (3))), array ('i', array (array (2), array (4)))), 'ABCDE');
test_converter ('_italic_', array (array ('i', array (array (0), array (6)))), 'italic');
test_converter ('__bold__', array (array ('b', array (array (0), array (4)))), 'bold');
test_converter ('[b]bold__', array (array ('b', array (array (0), array (4)))), 'bold');
test_converter ('__bold[/b]', array (array ('b', array (array (0), array (4)))), 'bold');
test_converter ("##A##B##C\n\n", array (array ('list', array (array (0), array (1), array (2), array (3)))), 'ABC');

// Captures
test_converter ('[url=http://domain.ext]link[/url]', array (array ('a', array (array (0, array ('u' => 'http://domain.ext')), array (4)))), 'link');
test_converter ('[size=big]text[/size]', array (array ('s', array (array (0, array ('p' => '200')), array (4)))), 'text');
test_converter ('[size=50]text[/size]', array (array ('s', array (array (0, array ('p' => '50')), array (4)))), 'text');

// Failed matches
test_converter ('[b]', array (), '[b]');
test_converter ('[/b]', array (), '[/b]');
test_converter ('[b]Text', array (), '[b]Text');
test_converter ('Text[/b]', array (), 'Text[/b]');
test_converter ('[i]Text[/b]', array (), '[i]Text[/b]');
test_converter ('[b][b]Text[/b]', array (array ('b', array (array (0), array (7)))), '[b]Text');

// Overlapping matches
test_converter ('[url]http://google.fr[/url]', array (array ('a', array (array (0, array ('u' => 'http://google.fr'))))), '');
test_converter ('[url=http://google.fr]test[/url]', array (array ('a', array (array (0, array ('u' => 'http://google.fr')), array (4)))), 'test');

// Charset
$markup = 'Voilà [hr] une [b]chaîne[/b] qui [i]devrait[/i] être convertie sans [b]problèmes[/b].';
$plain = 'Voilà  une chaîne qui devrait être convertie sans problèmes.';
$tags = array (array ('hr', array (array (6))), array ('b', array (array (11), array (17))), array ('i', array (array (22), array (29))), array ('b', array (array (50), array (59))));

mb_internal_encoding ('iso-8859-1');
test_converter (mb_convert_encoding ($markup, 'iso-8859-1', $charset), $tags, mb_convert_encoding ($plain, 'iso-8859-1', $charset));

mb_internal_encoding ('utf-8');
test_converter (mb_convert_encoding ($markup, 'utf-8', $charset), $tags, mb_convert_encoding ($plain, 'utf-8', $charset));

// Escape sequences
test_converter ('\\\\', array (), '\\');
test_converter ('\\ \\', array (), '\\ \\');
test_converter ('\\\\\\\\', array (), '\\\\');
test_converter ('\[b][/b]', array (), '[b][/b]');
test_converter ('[b]\[/b]', array (), '[b][/b]');
test_converter ('\[b]\[/b]', array (), '[b][/b]');
test_converter ('[b]some\[b]bold\[/b]text[/b]', array (array ('b', array (array (0), array (19)))), 'some[b]bold[/b]text');
test_converter ('\[b]some[b]bold[/b]text\[/b]', array (array ('b', array (array (7), array (11)))), '[b]someboldtext[/b]');
test_converter ('\__italic__', array (array ('i', array (array (1), array (7)))), '_italic_');
test_converter ('_\_italic__', array (array ('i', array (array (0), array (7)))), '_italic_');

echo 'OK';

?>
