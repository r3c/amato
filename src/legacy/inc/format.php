<?php

define ('FORMAT_ESCAPE',	'\\');
define ('FORMAT_START',		'(');
define ('FORMAT_STOP',		')');

/*
** Transform tags and parameters lists into faster hashmaps
** $modifiers	: modifiers list
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
			isset ($modifier['prec']) ? $modifier['prec'] : 1,
			isset ($modifier['init']) ? $modifier['init'] : null,
			isset ($modifier['step']) ? $modifier['step'] : null,
			isset ($modifier['stop']) ? $modifier['stop'] : null
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
	for ($i1 = 0; $i1 < $len; ++$i1)
	{
		// Browse through available tags if needed
		if (isset ($tree[$str[$i1]]))
		{
			// Search in tree for tag matching current string
			$args = array ();
			$node =& $tree;

			for ($j1 = $i1; is_array ($node); ++$j1)
			{
				if ($node[$str[$j1]][1] !== null)
					$args[$node[$str[$j1]][1]] .= $str[$j1];

				$node =& $node[$str[$j1]][0];
			}

			// Matching tag has been found
			if (isset ($node))
			{
				// Tag is an escape character, remove it
				if ($node == -1)
					$str = substr ($str, 0, $i1) . substr ($str, $j1);

				// Tag is a modifier, replace it
				else
				{
					list ($id1, $type, $expr) = $tags[$node];

					// Prepare action
					switch ($type)
					{
						case 0:
						case 1:
							// Check if limit has been reached for this tag
							if (!($cancel = $limit[$id1] == 0))
								--$limit[$id1];

							// Skip all opened tags with lower precedence
							for ($close = $count - 1; $close >= 0 && $opts[$id1][0] > $opts[$stack[$close][0]][0]; )
								--$close;

							$cross = $close + 1;
							break;

						case 2:
						case 3:
							// Browse stack for matching opened tag
							for ($close = $count - 1; $close >= 0 && $stack[$close][0] != $id1; )
								--$close;

							// Cancel when tag could not be found
							$cancel = $close < 0;
							$cross = $close + 1;
							break;
					}

					// Cancel procedure on invalid parameters
					if ($cancel)
						continue;

					// Close crossed tags
					for ($k = $count - 1; $k >= $cross; --$k)
					{
						list ($id2, $i2, $j2) = $stack[$k];

						$sStr = ($i1 > $j2 || $i2 < $j2) ? $opts[$id2][3] (substr ($str, $j2, $i1 - $j2), $stack[$k][3]) : '';

						if ($sStr === null)
							$sStr = substr ($str, $i2, $i1 - $i2);

						$sLen = strlen ($sStr);

						$str = substr ($str, 0, $i2) . $sStr . substr ($str, $i1);
						$len += $sLen - $i1 + $i2;

						$j1 = $j1 - $i1 + $i2 + $sLen;
						$i1 = $i2 + $sLen;
					}

					// Close current tag
					if ($type == 0 || $type == 3)
					{
						list ($id2, $i2, $j2, $args) = ($type == 0 ? array ($id1, $i1, $j1, $args) : $stack[$close]);

						$sStr = ($i1 > $j2 || $i2 < $j2) ? $opts[$id2][3] (substr ($str, $j2, $i1 - $j2), $args) : '';

						if ($sStr === null)
							$sStr = substr ($str, $i2, $j1 - $i2);

						$sLen = strlen ($sStr);

						$str = substr ($str, 0, $i2) . $sStr . substr ($str, $j1);
						$len += $sLen - $j1 + $i2;

						$j1 = $i2 + $sLen;
						$i1 = $j1 - 1;
					}

					// Restore crossed tags
					for ($k = $count - 1; $k >= $cross; --$k)
					{
						$stack[$k][1] = $j1;
						$stack[$k][2] = $j1;
					}

					// Finish action
					switch ($type)
					{
						// Push or insert tag on the stack
						case 1:
							// Call init function if available
							if ($opts[$id1][1])
								$opts[$id1][1] ($expr, $args);

							// Push tag on the stack
							array_splice ($stack, $cross, 0, array (array ($id1, $i1, $j1, $args)));
							++$count;

							// Move cursor to end of tag
							$i1 = $j1 - 1;
							break;

						// Call step function
						case 2:
							$s =& $stack[$close];

							$opts[$s[0]][2] ($expr, substr ($str, $s[2], $i1 - $s[2]), $s[3]);
							$s[2] = $j1;
							break;

						// Remove tag from the stack
						case 3:
							array_splice ($stack, $close, 1);
							--$count;
							break;
					}
				}
			}
		}
	}

	return nl2br ($str);
}

?>
