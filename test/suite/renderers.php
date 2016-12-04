<?php

require_once ('../src/amato.php');
require_once ('assert/test.php');
require_once ('assert/token.php');

/*
** Map of available tags and associated rendering parameters:
** - [tag id => (callback, level?)]
*/
$format = array
(
	'a'		=> array ('amato_render_anchor'),
	'b'		=> array (_amato_render_tag ('b')),
	'div'	=> array (_amato_render_tag ('div'), 2),
	'hr'	=> array (_amato_render_alone ('hr'), 2),
	'i'		=> array (_amato_render_tag ('i')),
	'list'	=> array ('amato_render_list', 2),
	'u'		=> array (_amato_render_tag ('u'))
);

function _amato_render_alone ($id)
{
	return function () use ($id)
	{
		return '<' . $id . ' />';
	};
}

function _amato_render_tag ($id)
{
	return function ($body, $params) use ($id)
	{
		return '<' . $id . '>' . $body . '</' . $id . '>';
	};
}

function amato_render_anchor ($body, $params)
{
	return '<a href="' . $params['u'] . '">' . ($body ?: $params['u']) . '</a>';
}

function amato_render_list ($body, $params, $closing)
{
	$out = $params->get ('out', '') . '<li>' . $body . '</li>';

	if ($closing)
		return '<ul>' . $out . '</ul>';

	$params['out'] = $out;

	return '';
}

Amato\autoload ();

function test_renderer ($plain, $markers, $expected, $context = null)
{
	global $format;
	static $encoder;
	static $renderers;

	if (!isset ($encoder))
		$encoder = new Amato\CompactEncoder ();

	if (!isset ($renderers))
	{
		$renderers = array
		(
			'format'	=> new Amato\FormatRenderer ($encoder, $format, 'htmlspecialchars')
		);
	}

	foreach ($markers as &$marker)
	{
		if (!isset ($marker[4]))
			$marker[4] = array ();
	}

	$token = $encoder->encode ($plain, $markers);

	foreach ($renderers as $name => $renderer)
	{
		$context = '[plain \'' . str_replace ("\n", ' ', $plain) . '\'][renderer \'' . $name . '\']';

		assert_test_equal ($renderer->render ($token, $context), $expected, $context);
	}
}

header ('Content-Type: text/plain; charset=UTF-8');

// Escape plain text
test_renderer ('<>', array (), '&lt;&gt;');
test_renderer ('<>', array (array ('a', 1, true, true, array ('u' => 'http://abc'))), '&lt;<a href="http://abc">http://abc</a>&gt;');

// Alone tag
test_renderer ('', array (array ('hr', 0, true, true)), '<hr />');
test_renderer ('ab', array (array ('hr', 1, true, true)), 'a<hr />b');

// Single tag
test_renderer ('x', array (array ('b', 0, true, false), array ('b', 1, false, true)), '<b>x</b>');
test_renderer ('abc', array (array ('b', 0, true, false), array ('b', 3, false, true)), '<b>abc</b>');
test_renderer ('abc', array (array ('b', 1, true, false), array ('b', 2, false, true)), 'a<b>b</b>c');

// Consecutive tags
test_renderer ('ab', array (array ('b', 0, true, false), array ('b', 1, false, true), array ('u', 1, true, false), array ('u', 2, false, true)), '<b>a</b><u>b</u>');

// Nested tags with same level
test_renderer ('ABCD', array (array ('b', 0, true, false), array ('i', 1, true, false), array ('i', 2, false, true), array ('u', 2, true, false), array ('u', 3, false, true), array ('b', 4, false, true)), '<b>A<i>B</i><u>C</u>D</b>');
test_renderer ('ABCDE', array (array ('b', 0, true, false), array ('i', 1, true, false), array ('u', 2, true, false), array ('u', 3, false, true), array ('i', 4, false, true), array ('b', 5, false, true)), '<b>A<i>B<u>C</u>D</i>E</b>');

// Nested tags with different levels
test_renderer ('abc', array (array ('div', 0, true, false), array ('b', 1, true, false), array ('b', 2, false, true), array ('div', 3, false, true)), '<div>a<b>b</b>c</div>');
test_renderer ('abc', array (array ('b', 0, true, false), array ('div', 1, true, false), array ('div', 2, false, true), array ('b', 3, false ,true)), '<b>a</b><div><b>b</b></div><b>c</b>');
test_renderer ('ab', array (array ('u', 0, true, false), array ('hr', 1, true, true), array ('u', 2, false, true)), '<u>a</u><hr /><u>b</u>');

// Basic parameters
test_renderer ('', array (array ('a', 0, true, true, array ('u' => 'http://www.lol.net'))), '<a href="http://www.lol.net">http://www.lol.net</a>');
test_renderer ('some.text', array (array ('a', 0, true, false, array ('u' => 'http://www.lol.net')), array ('a', 9, false, true)), '<a href="http://www.lol.net">some.text</a>');

// Steps
test_renderer ('firstsecond', array (array ('list', 0, true, false), array ('list', 5, false, false), array ('list', 11, false, true)), '<ul><li>first</li><li>second</li></ul>');
test_renderer ('firstsecond', array (array ('b', 0, true, false), array ('list', 0, true, false), array ('list', 5, false, false), array ('list', 11, false, true), array ('b', 11, false, true)), '<b></b><ul><li><b>first</b></li><li><b>second</b></li></ul><b></b>');

echo 'OK';

?>
