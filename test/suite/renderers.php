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
	'b'		=> array (amato_render_direct ('b')),
	'div'	=> array (amato_render_direct ('div'), 2),
	'hr'	=> array (amato_render_alone ('hr'), 2),
	'i'		=> array (amato_render_direct ('i')),
	'list'	=> array ('amato_render_list', 2),
	'u'		=> array (amato_render_direct ('u'))
);

function amato_render_alone ($id)
{
	return function ($captures, $markup, $closing) use ($id)
	{
		return '<' . $id . ' />';
	};
}

function amato_render_anchor ($captures, $markup, $closing, $state)
{
	return '<a href="' . $captures['u'] . '">' . ($markup ?: $captures['u']) . '</a>';
}

function amato_render_direct ($id)
{
	return function ($captures, $markup, $closing) use ($id)
	{
		return '<' . $id . '>' . $markup . '</' . $id . '>';
	};
}

function amato_render_list (&$captures, $markup, $closing, $state)
{
	if (!isset ($captures['out']))
		$captures['out'] = '';

	$captures['out'] .= '<li>' . $markup . '</li>';

	if ($closing)
		return '<ul>' . $captures['out'] . '</ul>';

	return '';
}

Amato\autoload ();

function test_renderer ($plain, $chains, $expected, $state = null)
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

	foreach ($chains as &$chain)
	{
		foreach ($chain[1] as &$markers)
		{
			if (!isset ($markers[1]))
				$markers[1] = array ();
		}
	}

	$token = $encoder->encode ($plain, $chains);

	foreach ($renderers as $name => $renderer)
	{
		$context = $name . ' renderer';

		assert_test_equal ($renderer->render ($token, $state), $expected, $context . ' render');
	}
}

header ('Content-Type: text/plain; charset=UTF-8');

// Escape plain text
test_renderer ('<>', array (), '&lt;&gt;');
test_renderer ('<>', array (array ('a', array (array (1, array ('u' => 'http://abc'))))), '&lt;<a href="http://abc">http://abc</a>&gt;');

// Alone tag
test_renderer ('', array (array ('hr', array (array (0)))), '<hr />');
test_renderer ('ab', array (array ('hr', array (array (1)))), 'a<hr />b');

// Single tag
test_renderer ('x', array (array ('b', array (array (0), array (1)))), '<b>x</b>');
test_renderer ('abc', array (array ('b', array (array (0), array (3)))), '<b>abc</b>');
test_renderer ('abc', array (array ('b', array (array (1), array (2)))), 'a<b>b</b>c');

// Consecutive tags
test_renderer ('ab', array (array ('b', array (array (0), array (1))), array ('u', array (array (1), array (2)))), '<b>a</b><u>b</u>');

// Nested tags with same level
test_renderer ('ABCD', array (array ('b', array (array (0), array (4))), array ('i', array (array (1), array (2))), array ('u', array (array (2), array (3)))), '<b>A<i>B</i><u>C</u>D</b>');
test_renderer ('ABCDE', array (array ('b', array (array (0), array (5))), array ('i', array (array (1), array (4))), array ('u', array (array (2), array (3)))), '<b>A<i>B<u>C</u>D</i>E</b>');

// Nested tags with conflicting levels
test_renderer ('abc', array (array ('div', array (array (0), array (3))), array ('b', array (array (1), array (2)))), '<div>a<b>b</b>c</div>');
test_renderer ('abc', array (array ('b', array (array (0), array (3))), array ('div', array (array (1), array (2)))), '<b>a</b><div><b>b</b></div><b>c</b>');
test_renderer ('ab', array (array ('u', array (array (0), array (2))), array ('hr', array (array (1)))), '<u>a</u><hr /><u>b</u>');

// Basic captures
test_renderer ('', array (array ('a', array (array (0, array ('u' => 'http://www.lol.net'))))), '<a href="http://www.lol.net">http://www.lol.net</a>');
test_renderer ('some.text', array (array ('a', array (array (0, array ('u' => 'http://www.lol.net')), array (9)))), '<a href="http://www.lol.net">some.text</a>');

// Steps
test_renderer ('firstsecond', array (array ('list', array (array (0), array (5), array (11)))), '<ul><li>first</li><li>second</li></ul>');
test_renderer ('firstsecond', array (array ('b', array (array (0), array (11))), array ('list', array (array (0), array (5), array (11)))), '<b></b><ul><li><b>first</b></li><li><b>second</b></li></ul><b></b>');

echo 'OK';

?>
