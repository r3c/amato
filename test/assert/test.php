<?php

function _assert_test_dump($value)
{
    return mb_convert_encoding(preg_replace('/\\s+/m', ' ', var_export($value, true)), 'utf-8');
}

function assert_test_equal($result, $expected, $context)
{
    if (is_scalar($expected)) {
        assert_test_true(is_scalar($result), $context . '[is scalar]');
        assert($result === $expected, $context . ' ' . _assert_test_dump($result) . ' === ' . _assert_test_dump($expected));
    } elseif (is_array($expected)) {
        assert_test_true(is_array($result), $context . '[is array]');
        assert_test_equal(count($result), count($expected), $context . '[number of values]');

        foreach ($expected as $key => $value) {
            $context_element = $context . '[key \'' . $key . '\']';

            assert_test_true(isset($result[$key]), $context_element . '[isset]');
            assert_test_equal($result[$key], $value, $context_element . '[value]');
        }
    } else {
        assert(false, $context . ' known type');
    }
}

function assert_test_true($result, $context)
{
    assert($result, _assert_test_dump($context) . ' ' . _assert_test_dump($result) . ' is true');
}
