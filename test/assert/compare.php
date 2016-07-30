<?php

function _assert_test_dump ($value)
{
	return mb_convert_encoding (preg_replace ("/\s+/m", ' ', var_export ($value, true)), 'utf-8');
}

function assert_test_equal ($result, $expected, $context)
{
	assert ($result === $expected, $context . ' - not equal: ' . _assert_test_dump ($result) . ' !== ' . _assert_test_dump ($expected));
}

function assert_test_true ($result, $context)
{
	assert ($result, $context . ' - not true: ' . _assert_test_dump ($result));
}

?>
