<?php

function assert_token_equal ($context, $tags, $plain, $tags_expected, $plain_expected)
{
	assert_test_equal ($plain, $plain_expected, $context . ' plain');
	assert_test_equal (count ($tags), count ($tags_expected), $context . ' number of tags');

	for ($i = 0; $i < count ($tags); ++$i)
	{
		list ($id, $matches) = $tags[$i];
		list ($id_expected, $matches_expected) = $tags_expected[$i];

		assert_test_equal ($id, $id_expected, $context . ' tag #' . $i . ' id');
		assert_test_equal (count ($matches), count ($matches_expected), $context . ' tag #' . $i . ' number of matches');

		for ($j = 0; $j < count ($matches); ++$j)
		{
			if (!isset ($matches_expected[$j][1]))
				$matches_expected[$j][1] = array ();

			list ($offset, $captures) = $matches[$j];
			list ($offset_expected, $captures_expected) = $matches_expected[$j];

			assert_test_equal ($offset, $offset_expected, $context . ' tag #' . $i . ' match #' . $j . ' offset');
			assert_test_equal (count ($captures), count ($captures_expected), $context . ' tag #' . $i . ' match #' . $j . ' number of captures');

			foreach ($captures_expected as $key => $value)
			{
				assert_test_true (isset ($captures[$key]), $context . ' tag #' . $i . ' match #' . $j . ' capture[' . $key . '] isset');
				assert_test_equal ($captures[$key], $value, $context . ' tag #' . $i . ' match #' . $j . ' capture[' . $key . ']');
			}
		}
	}
}

?>
