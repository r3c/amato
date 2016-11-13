<?php

require_once ('../src/amato.php');
require_once ('assert/test.php');
require_once ('assert/token.php');

/*
** Map of available tags and associated conversion definitions:
** - [tag id => [definition]]
** -- definition: (type, pattern, defaults?, convert?)
** --- pattern: plain1<pattern:name>plain2<pattern#default>plain3
** --- defaults: [name => value]
*/
$syntax = array
(
	'a' => array
	(
		array (Amato\Tag::ALONE, '<(https?%://|www\\.)[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>'),
		array (Amato\Tag::ALONE, '[url]<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>[/url]'),
		array (Amato\Tag::START, '[url=<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>]'),
		array (Amato\Tag::STOP, '[/url]')
	),
	'b' => array
	(
		array (Amato\Tag::START, '[b]'),
		array (Amato\Tag::STOP, '[/b]'),
		array (Amato\Tag::FLIP, '__')
	),
	'c'	=> array
	(
		array (Amato\Tag::START, '[c=<[012]:n>]', null, function ($type, &$params, $context)
		{
			$params['extra'] = 5;

			if ((int)$params['n'] === 2)
				$params['n'] = 1;

			return (int)$params['n'] !== 0;
		}),
		array (Amato\Tag::STOP, '[/c=<[012]:n>]', null, function ($type, &$params, $context)
		{
			$params['extra'] = 7;

			if ((int)$params['n'] === 2)
				$params['n'] = 1;

			return (int)$params['n'] !== 0;
		})
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
	'pre' => array
	(
		array (Amato\Tag::ALONE, '[pre]<.*:b>[/pre]')
	),
	'r' => array
	(
		array (Amato\Tag::START, '[r=<[012]:n>]', null, null, function ($type, &$params, $context)
		{
			if ((int)$params['n'] === 2)
				$params['n'] = 1;

			return (int)$params['n'] !== 0;
		}),
		array (Amato\Tag::STOP, '[/r=<[012]:n>]', null, null, function ($type, &$params, $context)
		{
			if ((int)$params['n'] === 2)
				$params['n'] = 1;

			return (int)$params['n'] !== 0;
		})
	),
	's' => array
	(
		array (Amato\Tag::START, '[size=big]', array ('p' => '200')),
		array (Amato\Tag::START, '[size=<[0-9]+:p>]'),
		array (Amato\Tag::STOP, '[/size]')
	)
);

Amato\autoload ();

function test_converter ($markup, $markers_expected, $plain_expected, $canonical = null, $unstable = false)
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
		$context = '[markup \'' . str_replace ("\n", ' ', $markup) . '\'][converter \'' . $name . '\']';

		// Convert once and assert result
		$token1 = $converter->convert ($markup);

		list ($plain1, $markers1) = $encoder->decode ($token1);

		assert_token_equal ($plain1, $markers1, $plain_expected, $markers_expected, $context . '[first decode]');

		$markup_revert1 = $converter->revert ($token1);

		assert_test_equal ($markup_revert1, $canonical !== null ? $canonical : $markup, $context . '[canonical revert]');

		// Stop test here if markup is flagged as unstable
		if ($unstable)
			continue;

		// Convert twice and assert again
		$token2 = $converter->convert ($markup_revert1);

		list ($plain2, $markers2) = $encoder->decode ($token2);

		assert_token_equal ($plain2, $markers2, $plain_expected, $markers_expected, $context . '[second decode]');

		$markup_revert2 = $converter->revert ($token2);

		// Compare first and second reverts
		assert_test_equal ($markup_revert2, $markup_revert1, $context . '[stable revert]');
	}
}

assert_options (ASSERT_BAIL, true);

$native = 'utf-8';

mb_internal_encoding ($native);

// Basic tests
test_converter ('Hello, World!', array (), 'Hello, World!');
test_converter ('[b]Hello, World![/b]', array (array ('b', 0, true, false), array ('b', 13, false, true)), 'Hello, World!');
test_converter ('A[b]B[/b]C[i]D[/i]E', array (array ('b', 1, true, false), array ('b', 2, false, true), array ('i', 3, true, false), array ('i', 4, false, true)), 'ABCDE', 'A[b]B[/b]C_D_E');
test_converter ('A[b]B[i]C[/i]D[/b]E', array (array ('b', 1, true, false), array ('i', 2, true, false), array ('i', 3, false, true), array ('b', 4, false, true)), 'ABCDE', 'A[b]B_C_D[/b]E');
test_converter ('A[b]B[i]C[/b]D[/i]E', array (array ('b', 1, true, false), array ('i', 2, true, false), array ('b', 3, false, true), array ('i', 4, false, true)), 'ABCDE', 'A[b]B_C[/b]D_E');
test_converter ('_italic_', array (array ('i', 0, true, false), array ('i', 6, false, true)), 'italic');
test_converter ('__bold__', array (array ('b', 0, true, false), array ('b', 4, false, true)), 'bold', '[b]bold[/b]');
test_converter ('[b]bold__', array (array ('b', 0, true, false), array ('b', 4, false, true)), 'bold', '[b]bold[/b]');
test_converter ('__bold[/b]', array (array ('b', 0, true, false), array ('b', 4, false, true)), 'bold', '[b]bold[/b]');
test_converter ("##A##B##C\n\n", array (array ('list', 0, true, false), array ('list', 1, false, false), array ('list', 2, false, false), array ('list', 3, false, true)), 'ABC');
test_converter ("####A\n\n", array (array ('list', 0, true, false), array ('list', 0, false, false), array ('list', 1, false, true)), 'A');
test_converter ('http://www.mirari.fr/', array (array ('a', 0, true, true, array ('u' => 'http://www.mirari.fr/'))), '');
test_converter ('www.mirari.fr', array (array ('a', 0, true, true, array ('u' => 'www.mirari.fr'))), '');

// Nested tags
test_converter ('[b]_plain_[/b]', array (array ('b', 0, true, false), array ('i', 0, true, false), array ('i', 5, false, true), array ('b', 5, false, true)), 'plain');
test_converter ('_[b]plain[/b]_', array (array ('i', 0, true, false), array ('b', 0, true, false), array ('b', 5, false, true), array ('i', 5, false, true)), 'plain');

// Consecutive tags
test_converter ('[b]A[/b]_B_', array (array ('b', 0, true, false), array ('b', 1, false, true), array ('i', 1, true, false), array ('i', 2, false, true)), 'AB');
test_converter ('[b]A_[/b]B_', array (array ('b', 0, true, false), array ('i', 1, true, false), array ('b', 1, false, true), array ('i', 2, false, true)), 'AB', '[b]A_[/b]B_');

// Parameters
test_converter ('[url=http://domain.ext]link[/url]', array (array ('a', 0, true, false, array ('u' => 'http://domain.ext')), array ('a', 4, false, true)), 'link');
test_converter ('[size=big]text[/size]', array (array ('s', 0, true, false, array ('p' => '200')), array ('s', 4, false, true)), 'text');
test_converter ('[size=200]text[/size]', array (array ('s', 0, true, false, array ('p' => '200')), array ('s', 4, false, true)), 'text', '[size=big]text[/size]');
test_converter ('[size=50]text[/size]', array (array ('s', 0, true, false, array ('p' => '50')), array ('s', 4, false, true)), 'text');

// Failed matches
test_converter ('[b]', array (), '[b]', '\\[b]');
test_converter ('[/b]', array (), '[/b]');
test_converter ('[b]Text', array (), '[b]Text', '\\[b]Text');
test_converter ('Text[/b]', array (), 'Text[/b]');
test_converter ('[i]Text[/b]', array (), '[i]Text[/b]', '\\[i]Text[/b]');
test_converter ('[b][b]Text[/b]', array (array ('b', 0, true, false), array ('b', 7, false, true)), '[b]Text', '[b]\\[b]Text[/b]');
test_converter ('[i][b]Text[/b]', array (array ('b', 3, true, false), array ('b', 7, false, true)), '[i]Text', '\\[i][b]Text[/b]');

// Overlapping matches
test_converter ('[url]http://google.fr[/url]', array (array ('a', 0, true, true, array ('u' => 'http://google.fr'))), '', 'http://google.fr');
test_converter ('[url=http://google.fr]test[/url]', array (array ('a', 0, true, false, array ('u' => 'http://google.fr')), array ('a', 4, false, true)), 'test');

// Crossed matches
test_converter ('[b][pre]text[/b][/pre]', array (array ('b', 0, true, false), array ('b', 9, false, true)), '[pre]text[/pre]');
test_converter ('[pre][b]text[/pre][/b]', array (array ('pre', 0, true, true, array ('b' => '[b]text'))), '[/b]');

// Charset
$markers = array (array ('hr', 6, true, true), array ('b', 11, true, false), array ('b', 17, false, true), array ('i', 22, true, false), array ('i', 29, false, true), array ('b', 50, true, false), array ('b', 59, false, true));
$markup = 'Voilà [hr] une [b]chaîne[/b] qui _devrait_ être convertie sans [b]problèmes[/b].';
$plain = 'Voilà  une chaîne qui devrait être convertie sans problèmes.';

foreach (array ('ascii', 'iso-8859-1', 'utf-8') as $charset)
{
	mb_internal_encoding ($charset);
	test_converter (mb_convert_encoding ($markup, $charset, $native), $markers, mb_convert_encoding ($plain, $charset, $native));
}

mb_internal_encoding ($native);

// Escape sequences
test_converter ('\\', array (), '\\', '\\\\');
test_converter ('\\\\', array (), '\\');
test_converter ('\\ \\', array (), '\\ \\', '\\\\ \\\\');
test_converter ('\\\\\\\\', array (), '\\\\');
test_converter ('\[b][/b]', array (), '[b][/b]');
test_converter ('[b]\[/b]', array (), '[b][/b]', '\\[b][/b]');
test_converter ('\[b]\[/b]', array (), '[b][/b]', '\\[b][/b]');
test_converter ('[b]some\[b]bold\[/b]text[/b]', array (array ('b', 0, true, false), array ('b', 19, false, true)), 'some[b]bold[/b]text');
test_converter ('\[b]some[b]bold[/b]text\[/b]', array (array ('b', 7, true, false), array ('b', 11, false, true)), '[b]someboldtext[/b]');
test_converter ('\__italic__', array (array ('i', 1, true, false), array ('i', 7, false, true)), '_italic_', '\\__italic_\\_');
test_converter ('_\_italic__', array (array ('i', 0, true, false), array ('i', 7, false, true)), '_italic_', '_\\_italic_\\_');
test_converter ('\___bold__', array (array ('b', 1, true, false), array ('b', 5, false, true)), '_bold', '\\_[b]bold[/b]');

// Convert callbacks
test_converter ('[c=0]abc[/c=0]', array (), '[c=0]abc[/c=0]', '\\[c=0]abc[/c=0]');
test_converter ('[c=1]abc[/c=0]', array (), '[c=1]abc[/c=0]', '\\[c=1]abc[/c=0]');
test_converter ('[c=0]abc[/c=1]', array (), '[c=0]abc[/c=1]', '\\[c=0]abc[/c=1]');
test_converter ('[c=1]abc[/c=1]', array (array ('c', 0, true, false, array ('n' => '1', 'extra' => '5')), array ('c', 3, false, true, array ('n' => '1', 'extra' => '7'))), 'abc');
test_converter ('[c=2]abc[/c=2]', array (array ('c', 0, true, false, array ('n' => '1', 'extra' => '5')), array ('c', 3, false, true, array ('n' => '1', 'extra' => '7'))), 'abc', '[c=1]abc[/c=1]');

// Revert callbacks
test_converter ('[r=0]abc[/r=0]', array (array ('r', 0, true, false, array ('n' => '0')), array ('r', 3, false, true, array ('n' => '0'))), 'abc', 'abc', true);
test_converter ('[r=0]abc[/r=1]', array (array ('r', 0, true, false, array ('n' => '0')), array ('r', 3, false, true, array ('n' => '1'))), 'abc', 'abc[/r=1]', true);
test_converter ('[r=1]abc[/r=0]', array (array ('r', 0, true, false, array ('n' => '1')), array ('r', 3, false, true, array ('n' => '0'))), 'abc', '[r=1]abc', true);
test_converter ('[r=1]abc[/r=1]', array (array ('r', 0, true, false, array ('n' => '1')), array ('r', 3, false, true, array ('n' => '1'))), 'abc');
test_converter ('[r=2]abc[/r=2]', array (array ('r', 0, true, false, array ('n' => '2')), array ('r', 3, false, true, array ('n' => '2'))), 'abc', '[r=1]abc[/r=1]', true);

echo 'OK';

?>
