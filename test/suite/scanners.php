<?php

require_once('../src/amato.php');
require_once('assert/test.php');

Amato\autoload();

function test_scanner($plain, $patterns, $expected_sequences)
{
    global $syntax;
    static $scanners;

    if (!isset($scanners)) {
        $scanners = array(
            'preg'	=> function () {
                return new Amato\PregScanner();
            }
        );
    }

    foreach ($scanners as $name => $constructor) {
        $context = '[plain \'' . str_replace("\n", ' ', $plain) . '\'][scanner \'' . $name . '\']';
        $keys = array();

        $scanner = $constructor();

        foreach ($patterns as $id => $pattern) {
            list($key, $names) = $scanner->assign($pattern);

            $keys[$id] = $key;
        }

        $sequences = $scanner->find($plain);

        assert_test_equal(count($sequences), count($expected_sequences), $context . '[number of found sequences]');

        $i = 0;

        foreach ($sequences as $sequence) {
            $context_sequence = $context . '[sequence ' . $i . ']';

            list($key, $offset, $length, $captures) = $sequence;

            if (!isset($expected_sequences[$i][3])) {
                $expected_sequences[$i][3] = array();
            }

            if (!isset($expected_sequences[$i][4])) {
                $expected_sequences[$i][4] = mb_substr($plain, $offset, $length);
            }

            list($expected_id, $expected_offset, $expected_length, $expected_captures, $expected_build) = $expected_sequences[$i++];

            assert_test_true(isset($keys[$expected_id]), $context_sequence . '[registered key]');
            assert_test_equal($key, $keys[$expected_id], $context_sequence . '[key]');
            assert_test_equal($offset, $expected_offset, $context_sequence . '[offset]');
            assert_test_equal($length, $expected_length, $context_sequence . '[length]');
            assert_test_equal($captures, $expected_captures, $context_sequence . '[captures]');
            assert_test_equal($scanner->build($key, $captures), $expected_build, $context_sequence . '[build]');
        }
    }
}

assert_options(ASSERT_BAIL, true);

// No match
test_scanner('plain text', array(), array());

// Plain pattern
test_scanner('abc', array('b'), array(array(0, 1, 1)));
test_scanner('abbc', array('b'), array(array(0, 1, 1), array(0, 2, 1)));

// Pattern with default value
test_scanner('x[127]y', array('[<[0-9]+#127>]'), array(array(0, 1, 5)));
test_scanner('x[1987]y', array('[<[0-9]+#127>]'), array(array(0, 1, 6, array(), '[127]')));

// Pattern with named capture
test_scanner('abc[id=test]def', array('[id=<[a-z]+@i>]'), array(array(0, 3, 9, array('i' => 'test'))));
test_scanner("[[Hello,\nWorld!]]", array('[[<.*@b>]]'), array(array(0, 0, 17, array('b' => "Hello,\nWorld!"))));

// Test with different encodings
$restore = mb_internal_encoding();
$source = 'utf-8';

foreach (array('iso-8859-1', 'iso-8859-15', 'utf-8', 'windows-1252') as $encoding) {
    mb_internal_encoding($encoding);

    test_scanner(mb_convert_encoding('aéb', $encoding, $source), array('<\\pL@c>'), array(
        array(0, 0, 1, array('c' => 'a')),
        array(0, 1, 1, array('c' => mb_convert_encoding('é', $encoding, $source))),
        array(0, 2, 1, array('c' => 'b'))
    ));
}

mb_internal_encoding($restore);

echo 'OK';
