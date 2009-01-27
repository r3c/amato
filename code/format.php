<?php

define ('FORMAT_ESCAPE',	'\\');
define ('FORMAT_START',		'(');
define ('FORMAT_STOP',		')');

/*
** Transform tags and parameters lists into faster hashmaps
** $modifiers	: tags list
** $args		: arguments list
** return		: compiled format structure
*/
function	formatCompile ($modifiers, $args)
{
	$format = array
	(
		array (),
		array (),
		array (),
		array ()
	);

	$modifiers[]['tags'][FORMAT_ESCAPE] = null;
	$tag = 0;

	foreach ($modifiers as $id => $modifier)
	{
		// Transform tag expressions
		foreach ($modifier['tags'] as $expr => $type)
		{
			// Build tag characters tree
			$node =& $format[0];
			$len = strlen ($expr);
			$arg = 0;

			for ($i = 0; $i < $len; ++$i)
			{
				if ($expr[$i] == FORMAT_START)
				{
					for ($j = ++$i; $i < $len && $expr[$i] != FORMAT_STOP; )
						++$i;

					foreach (str_split ($args[substr ($expr, $j, $i - $j)]) as $char)
						$node[$char] = array (&$node, $arg);

					++$arg;
				}
				else
				{
					if (!isset ($node[$expr[$i]]))
						$node[$expr[$i]] = array (null, null);

					$node =& $node[$expr[$i]][0];
				}
			}

			// Build tag information structure
			if ($type !== null)
			{
				$format[1][$tag] = array ($id, $type, $expr);
				$node = $tag++;
			}
			else
				$node = -1;
		}

		// Transform tag options
		$format[2][$id] = array
		(
			isset ($modifier['flag']) ? $modifier['flag'] : 0,
			isset ($modifier['init']) ? $modifier['init'] : null,
			isset ($modifier['step']) ? $modifier['step'] : null,
			isset ($modifier['stop']) ? $modifier['stop'] : null,
			isset ($modifier['wrap']) ? $modifier['wrap'] : null
		);

		// Transform tag limits
		$format[3][$id] = isset ($modifier['limit']) ? $modifier['limit'] : PHP_INT_MAX;
	}

	return $format;
}

/*
** Parse string and transform defined tags using callback functions
** $str		: input string
** $format	: compiled format structure
** return	: formatted string
*/
function	formatString ($str, $format, $charset = 'utf-8')
{
	$str = htmlspecialchars ($str, ENT_COMPAT, $charset);
	$len = strlen ($str);

	$limit = $format[3];
	$opts =& $format[2];
	$tags =& $format[1];
	$tree =& $format[0];	

	$count = 0;
	$stack = array ();

	// Parse entire string
	for ($i = 0; $i < $len; ++$i)
	{
		// Browse through available tags if needed
		if (isset ($tree[$str[$i]]))
		{
			// Search for tag matching current string
			$args = array ();
			$node =& $tree;

			for ($j = $i; is_array ($node); ++$j)
			{
				if ($node[$str[$j]][1] !== null)
					$args[$node[$str[$j]][1]] .= $str[$j];

				$node =& $node[$str[$j]][0];
			}

			// Matching tag has been found
			if (isset ($node))
			{
				// Tag is an escape character
				if ($node == -1)
					$str = substr ($str, 0, $i) . substr ($str, $j);

				// Tag is a modifier
				else
				{
					list ($id, $type, $expr) = $tags[$node];

					switch ($type)
					{
						// Standalone tag
						case 0:
							// Check if limit has been reached
							if ($limit[$id] == 0)
								break;

							$limit[$id]--;

							// Call stop function and get replacement
							$sStr = $opts[$id][3] ($expr, $args);

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
							if ($limit[$id] == 0)
								break;

							$limit[$id]--;

							// Call wrap functions for previous tags
							for ($k = $count; $k--; )
								if ($opts[$stack[$k][0]][4])
									$opts[$stack[$k][0]][4] ($opts[$id][0], $stack[$k][3]);

							// Call init function if available
							if ($opts[$id][1])
								$opts[$id][1] ($expr, $args);

							// Push tag on the stack
							$stack[] = array ($id, $i, $j, $args);
							++$count;

							// Move cursor to end of tag
							$i = $j - 1;
							break;

						// Inner or ending tag
						case 2:
						case 3:
							// Find last matching starting tag in stack
							for ($k = $count; $k > 0 && $stack[$k - 1][0] != $id; )
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
							if ($type == 3)
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
							if ($type == 2)
							{
								$s =& $stack[$k - 1];

								$opts[$s[0]][2] ($expr, substr ($str, $s[2], $i - $s[2]), $s[3]);
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
				}
			}
		}
	}

	return nl2br ($str);
}

?>
