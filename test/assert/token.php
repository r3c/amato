<?php

function assert_token_dump ($input)
{
	return mb_convert_encoding (preg_replace ("/\s+/m", ' ', var_export ($input, true)), 'utf-8');
}

function assert_token_equal ($context, $tags, $plain, $tags_expected, $plain_expected)
{
	assert ($plain === $plain_expected, $context . ' - plain not equal: ' . assert_token_dump ($plain) . ' !== ' . assert_token_dump ($plain_expected));
	assert (count ($tags) === count ($tags_expected), $context . ' - number of tags: ' . count ($tags) . ' !== ' . count ($tags_expected));

	for ($i = 0; $i < count ($tags); ++$i)
	{
		list ($id, $matches) = $tags[$i];
		list ($id_expected, $matches_expected) = $tags_expected[$i];

		assert ($id === $id_expected, $context . ' - tag #' . $i . ' id: ' . $id . ' !== ' . $id_expected);
		assert (count ($matches) === count ($matches_expected), $context . ' - tag #' . $i . ' number of matches: ' . count ($matches) . ' !== ' . count ($matches_expected));

		for ($j = 0; $j < count ($matches); ++$j)
		{
			if (!isset ($matches_expected[$j][1]))
				$matches_expected[$j][1] = array ();

			list ($offset, $captures) = $matches[$j];
			list ($offset_expected, $captures_expected) = $matches_expected[$j];

			assert ($offset === $offset_expected, $context . ' - tag #' . $i . ' match #' . $j . ' offset: ' . $offset . ' !== ' . $offset_expected);
			assert (count ($captures) === count ($captures_expected), $context . ' - tag #' . $i . ' match #' . $j . ' number of captures: ' . count ($captures) . ' !== ' . count ($captures_expected));

			foreach ($captures_expected as $key => $value)
			{
				assert (isset ($captures[$key]), $context . ' - tag #' . $i . ' match #' . $j . ' capture[' . $key . ']: undefined');
				assert ($captures[$key] === $value, $context . ' - tag #' . $i . ' match #' . $j . ' capture[' . $key . ']: ' . assert_token_dump ($captures[$key]) . ' !== ' . assert_token_dump ($value));
			}
		}
	}
}

?>
