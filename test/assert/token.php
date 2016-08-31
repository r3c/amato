<?php

function assert_token_equal ($plain, $groups, $plain_expected, $groups_expected, $context)
{
	assert_test_equal ($plain, $plain_expected, $context . '[plain]');
	assert_test_equal (count ($groups), count ($groups_expected), $context . '[number of groups]');

	for ($i = 0; $i < count ($groups); ++$i)
	{
		list ($id, $markers) = $groups[$i];
		list ($id_expected, $markers_expected) = $groups_expected[$i];

		$context_group = $context . '[group #' . $i . ']';

		assert_test_equal ($id, $id_expected, $context_group . '[id]');
		assert_test_equal (count ($markers), count ($markers_expected), $context_group . '[number of markers]');

		for ($j = 0; $j < count ($markers); ++$j)
		{
			if (!isset ($markers_expected[$j][1]))
				$markers_expected[$j][1] = array ();

			list ($offset, $captures) = $markers[$j];
			list ($offset_expected, $captures_expected) = $markers_expected[$j];

			$context_marker = $context_group . '[marker #' . $j . ']';

			assert_test_equal ($offset, $offset_expected, $context_marker . '[offset]');
			assert_test_equal ($captures, $captures_expected, $context_marker . '[captures]');
		}
	}
}

?>
