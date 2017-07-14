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
	'ambiguous' => array
	(
		array (Amato\Tag::START, '{{|'),
		array (Amato\Tag::STEP, '|'),
		array (Amato\Tag::STOP, '}}')
	),
	'anchor' => array
	(
		array (Amato\Tag::ALONE, '<(https?%://|www\\.)[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>'),
		array (Amato\Tag::ALONE, '[url]<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>[/url]'),
		array (Amato\Tag::START, '[url=<[-0-9A-Za-z._~%:/?%#@!$%%&\'*+,;=(%)*]+:u>]'),
		array (Amato\Tag::STOP, '[/url]')
	),
	'bold' => array
	(
		array (Amato\Tag::START, '[b]'),
		array (Amato\Tag::STOP, '[/b]'),
		array (Amato\Tag::FLIP, '__')
	),
	'callback'	=> array
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
	'default' => array
	(
		array (Amato\Tag::START, '[size=big]', array ('p' => '200')),
		array (Amato\Tag::START, '[size=<[0-9]+:p>]'),
		array (Amato\Tag::STOP, '[/size]')
	),
	'hr' => array
	(
		array (Amato\Tag::ALONE, '[hr]')
	),
	'italic' => array
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
	'param' => array
	(
		array (Amato\Tag::ALONE, '[paramA]', array ('bold' => '1')),
		array (Amato\Tag::ALONE, '[paramB]', array ('bold' => '2')),
		array (Amato\Tag::ALONE, '[paramC<.:c>]', array ('bold' => '3')),
		array (Amato\Tag::ALONE, '[paramD<.:c>]'),
		array (Amato\Tag::ALONE, '[paramE]')
	),
	'pre' => array
	(
		array (Amato\Tag::ALONE, '[pre]<.*:b>[/pre]')
	),
	'reverse' => array
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
	)
);

Amato\autoload ();

function test_converter ($markup, $markers_expected, $plain_expected, $canonical = null, $unstable = false, $iso_8859_1 = false)
{
	global $syntax;

	$encoder = new Amato\CompactEncoder ();
	$scanner = new Amato\PregScanner ('\\', $iso_8859_1 ? '' : 'u');

	$converters = array
	(
		'tag'	=> new Amato\TagConverter ($encoder, $scanner, $syntax)
	);

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
test_converter ('[b]Hello, World![/b]', array (array ('bold', array (0, 14))), 'Hello, World!');
test_converter ('A[b]B[/b]C[i]D[/i]E', array (array ('bold', array (1, 3)), array ('italic', array (5, 7))), 'ABCDE', 'A[b]B[/b]C_D_E');
test_converter ('A[b]B[i]C[/i]D[/b]E', array (array ('bold', array (1, 7)), array ('italic', array (3, 5))), 'ABCDE', 'A[b]B_C_D[/b]E');
test_converter ('A[b]B[i]C[/b]D[/i]E', array (array ('bold', array (1, 5)), array ('italic', array (3, 7))), 'ABCDE', 'A[b]B_C[/b]D_E');
test_converter ('_italic_', array (array ('italic', array (0, 7))), 'italic');
test_converter ('__bold__', array (array ('bold', array (0, 5))), 'bold', '[b]bold[/b]');
test_converter ('[b]bold__', array (array ('bold', array (0, 5))), 'bold', '[b]bold[/b]');
test_converter ('__bold[/b]', array (array ('bold', array (0, 5))), 'bold', '[b]bold[/b]');
test_converter ("##A##B##C\n\n", array (array ('list', array (0, 2, 4, 6))), 'ABC');
test_converter ("####A\n\n", array (array ('list', array (0, 1, 3))), 'A');
test_converter ('http://www.mirari.fr/', array (array ('anchor', array (array (0, array ('u' => 'http://www.mirari.fr/'))))), '');
test_converter ('www.mirari.fr', array (array ('anchor', array (array (0, array ('u' => 'www.mirari.fr'))))), '');

// Nested tags
test_converter ('[b]_plain_[/b]', array (array ('bold', array (0, 8)), array ('italic', array (1, 7))), 'plain');
test_converter ('_[b]plain[/b]_', array (array ('italic', array (0, 8)), array ('bold', array (1, 7))), 'plain');

// Consecutive tags
test_converter ('[b]A[/b]_B_', array (array ('bold', array (0, 2)), array ('italic', array (3, 5))), 'AB');
test_converter ('[b]A_[/b]B_', array (array ('bold', array (0, 3)), array ('italic', array (2, 5))), 'AB', '[b]A_[/b]B_');

// Parameters
test_converter ('[url=http://domain.ext]link[/url]', array (array ('anchor', array (array (0, array ('u' => 'http://domain.ext')), 5))), 'link');
test_converter ('[size=big]text[/size]', array (array ('default', array (array (0, array ('p' => '200')), 5))), 'text');
test_converter ('[size=200]text[/size]', array (array ('default', array (array (0, array ('p' => '200')), 5))), 'text', '[size=big]text[/size]');
test_converter ('[size=50]text[/size]', array (array ('default', array (array (0, array ('p' => '50')), 5))), 'text');
test_converter ('[paramA]', array (array ('param', array (array (0, array ('bold' => '1'))))), '');
test_converter ('[paramB]', array (array ('param', array (array (0, array ('bold' => '2'))))), '');
test_converter ('[paramCx]', array (array ('param', array (array (0, array ('bold' => '3', 'c' => 'x'))))), '');
test_converter ('[paramDy]', array (array ('param', array (array (0, array ('c' => 'y'))))), '');
test_converter ('[paramE]', array (array ('param', array (0))), '');

// Failed matches
test_converter ('[b]', array (), '[b]', '\\[b]');
test_converter ('[/b]', array (), '[/b]');
test_converter ('[b]Text', array (), '[b]Text', '\\[b]Text');
test_converter ('Text[/b]', array (), 'Text[/b]');
test_converter ('[i]Text[/b]', array (), '[i]Text[/b]', '\\[i]Text[/b]');
test_converter ('[b][b]Text[/b]', array (array ('bold', array (0, 8))), '[b]Text', '[b]\\[b]Text[/b]');
test_converter ('[i][b]Text[/b]', array (array ('bold', array (3, 8))), '[i]Text', '\\[i][b]Text[/b]');

// Overlapping matches
test_converter ('[url]http://google.fr[/url]', array (array ('anchor', array (array (0, array ('u' => 'http://google.fr'))))), '', 'http://google.fr');
test_converter ('[url=http://google.fr]test[/url]', array (array ('anchor', array (array (0, array ('u' => 'http://google.fr')), 5))), 'test');
test_converter ('[url]\http://google.fr[/url]', array (), '[url]http://google.fr[/url]');
test_converter ('[url=\http://google.fr]test[/url]', array (), '[url=http://google.fr]test[/url]');

// Crossed matches
test_converter ('[b][pre]text[/b][/pre]', array (array ('pre', array (array (3, array ('b' => 'text[/b]'))))), '[b]', '\\[b][pre]text[/b][/pre]');
test_converter ('[pre][b]text[/pre][/b]', array (array ('pre', array (array (0, array ('b' => '[b]text'))))), '[/b]');

// Charset
$markers = array (array ('hr', array (6)), array ('bold', array (12, 19)), array ('italic', array (25, 33)), array ('bold', array (55, 65)));
$markup = 'Voilà [hr] une [b]chaîne[/b] qui _devrait_ être convertie sans [b]problèmes[/b].';
$plain = 'Voilà  une chaîne qui devrait être convertie sans problèmes.';

foreach (array ('ascii' => false, 'iso-8859-1' => true, 'utf-8' => false) as $charset => $iso_8851_1)
{
	mb_internal_encoding ($charset);
	test_converter (mb_convert_encoding ($markup, $charset, $native), $markers, mb_convert_encoding ($plain, $charset, $native), null, false, $iso_8851_1);
}

mb_internal_encoding ($native);

// Escape sequences
test_converter ('\\', array (), '\\', '\\\\');
test_converter ('\\\\', array (), '\\');
test_converter ('\\ \\', array (), '\\ \\', '\\\\ \\\\');
test_converter ('\\\\\\\\', array (), '\\\\');
test_converter ('\[pre]X[/pre]', array (), '[pre]X[/pre]');
test_converter ('\[b][/b]', array (), '[b][/b]');
test_converter ('[b]\[/b]', array (), '[b][/b]', '\\[b][/b]');
test_converter ('\[b]\[/b]', array (), '[b][/b]', '\\[b][/b]');
test_converter ('[b]some\[b]bold\[/b]text[/b]', array (array ('bold', array (0, 20))), 'some[b]bold[/b]text');
test_converter ('\[b]some[b]bold[/b]text\[/b]', array (array ('bold', array (7, 12))), '[b]someboldtext[/b]');
test_converter ('\__italic__', array (array ('italic', array (1, 8))), '_italic_', '\\__italic_\\_');
test_converter ('_\_italic__', array (array ('italic', array (0, 8))), '_italic_', '_\\_italic_\\_');
test_converter ('\___bold__', array (array ('bold', array (1, 6))), '_bold', '\\_[b]bold[/b]');

// Convert callbacks
test_converter ('[c=0]abc[/c=0]', array (), '[c=0]abc[/c=0]', '\\[c=0]abc[/c=0]');
test_converter ('[c=1]abc[/c=0]', array (), '[c=1]abc[/c=0]', '\\[c=1]abc[/c=0]');
test_converter ('[c=0]abc[/c=1]', array (), '[c=0]abc[/c=1]', '\\[c=0]abc[/c=1]');
test_converter ('[c=1]abc[/c=1]', array (array ('callback', array (array (0, array ('n' => '1', 'extra' => '5')), array (4, array ('n' => '1', 'extra' => '7'))))), 'abc');
test_converter ('[c=2]abc[/c=2]', array (array ('callback', array (array (0, array ('n' => '1', 'extra' => '5')), array (4, array ('n' => '1', 'extra' => '7'))))), 'abc', '[c=1]abc[/c=1]');

// Revert callbacks
test_converter ('[r=0]abc[/r=0]', array (array ('reverse', array (array (0, array ('n' => '0')), array (4, array ('n' => '0'))))), 'abc', 'abc', true);
test_converter ('[r=0]abc[/r=1]', array (array ('reverse', array (array (0, array ('n' => '0')), array (4, array ('n' => '1'))))), 'abc', 'abc[/r=1]', true);
test_converter ('[r=1]abc[/r=0]', array (array ('reverse', array (array (0, array ('n' => '1')), array (4, array ('n' => '0'))))), 'abc', '[r=1]abc', true);
test_converter ('[r=1]abc[/r=1]', array (array ('reverse', array (array (0, array ('n' => '1')), array (4, array ('n' => '1'))))), 'abc');
test_converter ('[r=2]abc[/r=2]', array (array ('reverse', array (array (0, array ('n' => '2')), array (4, array ('n' => '2'))))), 'abc', '[r=1]abc[/r=1]', true);

// Ambiguous matches
/*test_converter ('{{|A|B{{|C|D}}E}}', array
(
	array ('ambiguous', 0, true, false),
	array ('ambiguous', 1, false, false),
	array ('ambiguous', 2, true, false),
	array ('ambiguous', 3, false, false),
	array ('ambiguous', 4, false, true),
	array ('ambiguous', 5, false, true)
), 'ABCDE');*/

echo 'OK';

?>
