<?php

require_once('../src/amato.php');
require_once('assert/test.php');
require_once('assert/token.php');

/*
** Map of available tags and associated rendering parameters:
** - [tag id => (callback, level?)]
*/
$format = array(
    'a'		=> array('amato_render_anchor'),
    'b'		=> array(amato_render_direct('b')),
    'div'	=> array(amato_render_direct('div'), 2),
    'hr'	=> array(amato_render_alone('hr'), 2),
    'i'		=> array(amato_render_direct('i')),
    'list'	=> array('amato_render_list', 2),
    'u'		=> array(amato_render_direct('u'))
);

function amato_render_alone($id)
{
    return function () use ($id) {
        return '<' . $id . ' />';
    };
}

function amato_render_anchor($body, $params)
{
    return '<a href="' . $params['u'] . '">' . ($body ?: $params['u']) . '</a>';
}

function amato_render_direct($id)
{
    return function ($body, $params) use ($id) {
        return '<' . $id . '>' . $body . '</' . $id . '>';
    };
}

function amato_render_list($body, &$params, $closing)
{
    if (!isset($params['out'])) {
        $params['out'] = '';
    }

    $params['out'] .= '<li>' . $body . '</li>';

    if ($closing) {
        return '<ul>' . $params['out'] . '</ul>';
    }

    return '';
}

Amato\autoload();

function test_renderer($plain, $groups, $expected, $state = null)
{
    global $format;
    static $encoder;
    static $renderers;

    if (!isset($encoder)) {
        $encoder = new Amato\CompactEncoder();
    }

    if (!isset($renderers)) {
        $renderers = array(
            'format'	=> new Amato\FormatRenderer($encoder, $format, 'htmlspecialchars')
        );
    }

    foreach ($groups as &$group) {
        foreach ($group[1] as &$markers) {
            if (is_integer($markers)) {
                $markers = array($markers);
            }

            if (!isset($markers[1])) {
                $markers[1] = array();
            }
        }
    }

    $token = $encoder->encode($plain, $groups);

    foreach ($renderers as $name => $renderer) {
        $context = $name . ' renderer';

        assert_test_equal($renderer->render($token, $state), $expected, $context . ' render');
    }
}

header('Content-Type: text/plain; charset=UTF-8');

// Escape plain text
test_renderer('<>', array(), '&lt;&gt;');
test_renderer('<>', array(array('a', array(array(1, array('u' => 'http://abc'))))), '&lt;<a href="http://abc">http://abc</a>&gt;');

// Alone tag
test_renderer('', array(array('hr', array(0))), '<hr />');
test_renderer('ab', array(array('hr', array(1))), 'a<hr />b');

// Single tag
test_renderer('x', array(array('b', array(0, 2))), '<b>x</b>');
test_renderer('abc', array(array('b', array(0, 4))), '<b>abc</b>');
test_renderer('abc', array(array('b', array(1, 3))), 'a<b>b</b>c');

// Consecutive tags
test_renderer('ab', array(array('b', array(0, 2)), array('u', array(3, 5))), '<b>a</b><u>b</u>');

// Nested tags with same level
test_renderer('ABCD', array(array('b', array(0, 9)), array('i', array(2, 4)), array('u', array(5, 7))), '<b>A<i>B</i><u>C</u>D</b>');
test_renderer('ABCDE', array(array('b', array(0, 10)), array('i', array(2, 8)), array('u', array(4, 6))), '<b>A<i>B<u>C</u>D</i>E</b>');

// Nested tags with conflicting levels
test_renderer('abc', array(array('div', array(0, 6)), array('b', array(2, 4))), '<div>a<b>b</b>c</div>');
test_renderer('abc', array(array('b', array(0, 6)), array('div', array(2, 4))), '<b>a</b><div><b>b</b></div><b>c</b>');
test_renderer('ab', array(array('u', array(0, 4)), array('hr', array(2))), '<u>a</u><hr /><u>b</u>');

// Basic captures
test_renderer('', array(array('a', array(array(0, array('u' => 'http://www.lol.net'))))), '<a href="http://www.lol.net">http://www.lol.net</a>');
test_renderer('some.text', array(array('a', array(array(0, array('u' => 'http://www.lol.net')), 10))), '<a href="http://www.lol.net">some.text</a>');

// Steps
test_renderer('firstsecond', array(array('list', array(0, 6, 13))), '<ul><li>first</li><li>second</li></ul>');
test_renderer('firstsecond', array(array('b', array(0, 15)), array('list', array(1, 7, 14))), '<b></b><ul><li><b>first</b></li><li><b>second</b></li></ul><b></b>');

echo 'OK';
