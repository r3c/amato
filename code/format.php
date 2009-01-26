<?php

define ('FORMAT_ESCAPE',	'\\');
define ('FORMAT_START',		'(');
define ('FORMAT_STOP',		')');

/*
** Transform tags and parameters lists into faster hashmaps
** $modifiers	: tags list
** $args		: arguments list
** return		: compiled transformations hashmap
*/
function	formatCompile ($modifiers, $args)
{
	$hash = array
	(
		array (),
		array (),
		array (),
		array ()
//--- NEW CODE ---
		,
		array (/*index	=> array (id, type, tag)*/),
		array (/*char	=> array (...)*/)
//--- NEW CODE ---
	);

	foreach ($modifiers as $id => $modifier)
	{
//--- NEW CODE ---
		// FIXME: Build $hash[4] and $hash[5]
//--- NEW CODE ---

		foreach ($modifier['tags'] as $tag => $type)
			if ($tag[0] != FORMAT_ESCAPE && $tag[0] != FORMAT_START)
				$hash[0][$tag[0]][] = array ($id, $type, $tag, strlen ($tag));

		$hash[2][$id] = array
		(
			isset ($modifier['flag']) ? $modifier['flag'] : 0,
			isset ($modifier['init']) ? $modifier['init'] : null,
			isset ($modifier['step']) ? $modifier['step'] : null,
			isset ($modifier['stop']) ? $modifier['stop'] : null,
			isset ($modifier['wrap']) ? $modifier['wrap'] : null
		);

		$hash[3][$id] = isset ($modifier['limit']) ? $modifier['limit'] : PHP_INT_MAX;
	}

	foreach ($args as $id => $chars)
		$hash[1][$id] = array_flip (str_split ($chars));

	$hash[0][FORMAT_ESCAPE] = 0;

	return $hash;
}

/*
** Parse string and transform defined tags using callback functions
** $str		: input string
** $hash	: compiled transformations hashmap
** return	: formatted string
*/
function	formatString ($str, $hash)
{
	$str = htmlspecialchars ($str, ENT_COMPAT, ENV_CHARSET);
	$len = strlen ($str);

	$args =& $hash[1];
	$opts =& $hash[2];
	$tags =& $hash[0];

	$new_chars =& $hash[5];
	$new_mods =& $hash[2];
	$new_tags =& $hash[4];

	$count = 0;
	$stack = array ();

	// Parse entire string
	for ($i = 0; $i < $len; ++$i)
	{

//--- NEW CODE ---
		// Browse through available tags if needed
		if (isset ($new_chars[$str[$i]]))
		{
			// Search for matching tag
			$tab =& $new_chars[$str[$i]];

			for ($j = $i; is_array ($tab); ++$j)
			{
				$ptr =& $tab;
				unset ($tab);
				$tab =& $ptr[$str[$j]];
				unset ($ptr);
			}

			// Matching tag has been found
			if (isset ($tab))
			{
				// Tag is an escape character
				if ($tab == -1)
					$str = substr ($str, 0, $i) . substr ($str, $i + 1);

				// Tag is a modifier
				else
				{
					list ($id, $way, $tag) = $new_tags[$tab];

					switch ($way)
					{
						//FIXME
					}
				}
			}

			unset ($tab);
		}
//--- NEW CODE ---

		// Some special character has been found
		if (isset ($tags[$str[$i]]))
		{
			// Escape character causes next one to be ignored
			if ($str[$i] == FORMAT_ESCAPE)
				$str = substr ($str, 0, $i) . substr ($str, $i + 1);

			// Browse tags starting with current character
			else
			{
				foreach ($tags[$str[$i]] as $tag)
				{
					$tArgs = array ();
					$tLen = $tag[3];
					$tStr = $tag[2];
					$j = $i;

					// Check if current tag matches
					for ($k = 0; $k < $tLen; ++$k)
					{
						if ($tStr[$k] == FORMAT_START)
						{
							for ($l = ++$k; $k < $tLen && $tStr[$k] != FORMAT_STOP; )
								++$k;

							$id = substr ($tStr, $l, $k - $l);

							for ($l = $j; $j < $len && isset ($args[$id][$str[$j]]); )
								++$j;

							if ($l == $j)
								break;

							$tArgs[] = substr ($str, $l, $j - $l);

							continue;
						}
						else if ($str[$j] != $tStr[$k])
							break;

						++$j;
					}

					// Current tag matches
					if ($k == $tLen)
					{
						switch ($tag[1])
						{
							// Standalone tag
							case 0:
								// Check if limit has been reached
								if ($hash[3][$tag[0]] == 0)
									break;

								$hash[3][$tag[0]]--;

								// Call stop function and get replacement
								$sStr = $opts[$tag[0]][3] ($tag[2], $tArgs);

								if ($sStr === null)
									$sStr = substr ($str, $i, $j - $i);

								// Update string and string length
								$sLen = strlen ($sStr);

								$str = substr ($str, 0, $i) . $sStr . substr ($str, $j);
								$len += $sLen + $i - $j;

								// Move cursor to end of tag
								$i = $j - 1;
								break;

							// Starting tag
							case 1:
								// Check if limit has been reached
								if ($hash[3][$tag[0]] == 0)
									break;

								$hash[3][$tag[0]]--;

								// Call wrap functions for previous tags
								for ($k = $count; $k--; )
									if ($opts[$stack[$k][0]][4])
										$opts[$stack[$k][0]][4] ($opts[$tag[0]][0], $stack[$k][3]);

								// Call init function if available
								if ($opts[$tag[0]][1])
									$opts[$tag[0]][1] ($tag[2], $tArgs);

								// Push tag on the stack
								$stack[] = array ($tag[0], $i, $j, $tArgs);
								++$count;

								// Move cursor to end of tag
								$i = $j - 1;
								break;

							// Inner or ending tag
							case 2:
							case 3:
								// Find last matching starting tag in stack
								for ($k = $count; $k > 0 && $stack[$k - 1][0] != $tag[0]; )
									--$k;

								// Exit if matching starting tag wasn't found
								if ($k == 0)
									break;

								// Call modifiers for all crossed tags
								for ($l = $count; $l-- > $k; )
								{
									$s =& $stack[$l];

									$sStr = ($i > $s[2] || $s[1] < $s[2]) ? $opts[$s[0]][3] (substr ($str, $s[2], $i - $s[2]), $s[3]) : '';

									if ($sStr === null)
										$sStr = substr ($str, $s[1], $i - $s[1]);

									$sLen = strlen ($sStr);

									$str = substr ($str, 0, $s[1]) . $sStr . substr ($str, $i);
									$len += $sLen - $i + $s[1];

									$j = $j - $i + $s[1] + $sLen;
									$i = $s[1] + $sLen;

									unset ($s);
								}

								// Call modifier for end tag
								if ($tag[1] == 3)
								{
									$s =& $stack[$k - 1];

									$sStr = ($i > $s[2] || $s[1] < $s[2]) ? $opts[$s[0]][3] (substr ($str, $s[2], $i - $s[2]), $s[3]) : '';

									if ($sStr === null)
										$sStr = substr ($str, $s[1], $j - $s[1]);

									$sLen = strlen ($sStr);

									$str = substr ($str, 0, $s[1]) . $sStr . substr ($str, $j);
									$len += $sLen - $j + $s[1];

									$j = $s[1] + $sLen;
									$i = $j - 1;

									unset ($s);
								}

								// Reopen crossed tags
								for ($l = $count; $l-- > $k; )
								{
									$stack[$l][1] = $j;
									$stack[$l][2] = $j;
								}

								// Call inline function
								if ($tag[1] == 2)
								{
									$s =& $stack[$k - 1];

									$opts[$s[0]][2] ($tag[2], substr ($str, $s[2], $i - $s[2]), $s[3]);
									$s[2] = $j;

									unset ($s);
								}

								// Remove tag from the stack
								else
								{
									array_splice ($stack, $k - 1, 1);
									--$count;
								}
								break;
						}

						// Exit loop when a replacement has been made
						break;
					}
				}
			}
		}
	}

	return nl2br ($str);
}

?>
