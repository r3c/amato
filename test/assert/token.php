<?php

function assert_token_equal ($plain, $markers, $plain_expected, $markers_expected, $context)
{
	assert_test_equal ($plain, $plain_expected, $context . '[plain]');
	assert_test_equal (count ($markers), count ($markers_expected), $context . '[number of markers]');

	for ($i = 0; $i < count ($markers); ++$i)
	{
		if (!isset ($markers_expected[$i][4]))
			$markers_expected[$i][4] = array ();

		list ($id, $offset, $is_first, $is_last, $params) = $markers[$i];
		list ($id_expected, $offset_expected, $is_first_expected, $is_last_expected, $params_expected) = $markers_expected[$i];

		$context_marker = $context . '[marker #' . $i . ']';

		assert_test_equal ($id, $id_expected, $context_marker . '[id]');
		assert_test_equal ($offset, $offset_expected, $context_marker . '[offset]');
		assert_test_equal ($is_first, $is_first_expected, $context_marker . '[is_first]');
		assert_test_equal ($is_last, $is_last_expected, $context_marker . '[is_last]');
		assert_test_equal ($params, $params_expected, $context_marker . '[params]');
	}
}

?>
