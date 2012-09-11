<?php

/*
** Internal constants.
*/
define ('YML_ACTION_ALONE',		0);
define ('YML_ACTION_BEGIN',		1);
define ('YML_ACTION_BREAK',		2);
define ('YML_ACTION_END',		3);
define ('YML_ACTION_SKIP',		4);

define ('YML_CHAR_BLOCK',		';');
define ('YML_CHAR_ESCAPE',		'\\');
define ('YML_CHAR_FIELD',		',');
define ('YML_CHAR_HEADER',		'|');

define ('YML_PATTERN_ESCAPE',	'\\');

/*
** Compile tag rules into parsing tree.
** $rules:		tag rules structure
** $arguments:	tag pattern arguments
** return:		compiled tags
*/
function	ymlCompile ($rules, $arguments)
{
	$rules[''] = array ('patterns' => array (YML_PATTERN_ESCAPE => YML_ACTION_SKIP));
	$tags = array (null, array ());

	foreach ($rules as $name => $rule)
	{
		// Transform rule options
		$tags[1][$name] = array
		(
			'limit'		=> isset ($rule['limit']) ? (int)$rule['limit'] : PHP_INT_MAX,
			'nesting'	=> isset ($rule['nesting']) ? (int)$rule['nesting'] : 1
		);

		// Transform rule patterns
		foreach ($rule['patterns'] as $pattern => $action)
		{
			// Build parsing tree
			$index = 1;
			$length = strlen ($pattern);
			$node =& $tags[0];

			for ($i = 0; $i < $length; ++$i)
			{
				if ($pattern[$i] == YML_CHAR_BEGIN)
				{
					for ($j = ++$i; $i < $length && $pattern[$i] != YML_CHAR_END; )
						++$i;

					$branch = array (&$node, $index++, null);
					$type = substr ($pattern, $j, $i - $j);

					if (!isset ($arguments[$type]))
						return null; // FIXME

					foreach (str_split ($arguments[$type]) as $character)
						$node[$character] =& $branch;

					++$index;
				}
				else
				{
					$branch = array (null, 0, null);

					if (!isset ($node[$pattern[$i]]))
						$node[$pattern[$i]] = $branch;

					$node =& $branch[0];
				}
			}

			// Register terminal node
			if ($action !== null)
				$branch[2] = array ($name, $action, $pattern);
		}
	}

	return $tags;
}

/*
** Decode tokenized string to plain format.
** $token:	tokenized string
** $rules:	parsing rules
** return:	plain string
*/
function	ymlDecode ($token, $rules)
{
	$parsed = ymlParse ($token);

	if ($parsed === null)
		return null;

	list ($scopes, $text) = $parsed;

	$index = 0;

	foreach ($scopes as $scope)
	{
		list ($delta, $action, $name, $arguments) = $scope;

		$index += $delta;

		if (!isset ($rules[$name]) || !isset ($rules[$name]['decode']))
			continue;

		$tag = $rules[$name]['decode'] ($action, $arguments);
		$text = substr ($text, 0, $index) . $tag . substr ($text, $index);

		$index += strlen ($tag);
	}

	return $text;
}

/*
** Encode plain string to tokenized format.
** $plain:	plain string
** $tags:	compiled tags
** return:	tokenized string
*/
function	ymlEncode ($plain, $tags)
{
	$count = 0;
	$length = strlen ($plain);
	$options =& $tags[1];
	$stack = array ();
	$tree =& $tags[0];

	// FIXME: handle null tree case

	// Parse entire string
	for ($i1 = 0; $i1 < $length; ++$i1)
	{
		// Use parsing tree if we have a potential match
		if (isset ($tree[$plain[$i1]]))
		{
			// Browse parsing tree
			$arguments = array ();
			$node =& $tree;
echo "$i1: " . $plain[$i1] . " begins tree<br />";
			for ($j1 = $i1; $j1 < $length && isset ($node[$plain[$j1]]); ++$j1)
			{
				$branch = $node[$plain[$j1]];
echo " - eat " . $plain[$j1] . ", branch = (..., $branch[1], $branch[2])<br />";
				if ($branch[1] > 0)
				{
					if (!isset ($arguments[$branch[1] - 1]))
						$arguments[$branch[1] - 1] = '';

					$arguments[$branch[1] - 1] .= $plain[$j1];
				}

				$node =& $branch[0];
			}

			// Matched tag
if ($branch[2] === null) echo " - failed<br />";
			if ($branch[2] !== null)
			{
echo " - matched: " . $branch[2] . "<br />";
				list ($name, $action, $pattern) = $branch[2];

				switch ($action)
				{
					case YML_ACTION_ALONE:
					case YML_ACTION_BEGIN:
						// Check if limit has been reached for this tag
						if (!($cancel = $limit[$name] == 0))
							--$limit[$name];

						// Skip all opened tags with lower precedence
						for ($close = $count - 1; $close >= 0 && $options[$name]['nesting'] > $options[$stack[$close][0]]['nesting']; )
							--$close;

						$cross = $close + 1;

						break;

					case YML_ACTION_BREAK:
					case YML_ACTION_END:
						// Browse stack for matching opened tag
						for ($close = $count - 1; $close >= 0 && $stack[$close][0] != $name; )
							--$close;

						// Cancel when tag could not be found
						$cancel = $close < 0;
						$cross = $close + 1;

						break;

					case YML_ACTION_SKIP:
						$plain = substr ($plain, 0, $i1) . substr ($plain, $j1);
						$cancel = true;

						break;
				}

				// Cancel replacement if requested
				if ($cancel)
					continue;
global $_ymlCallbacks;
				// Close crossed tags
				for ($k = $count - 1; $k >= $cross; --$k)
				{
					list ($id2, $i2, $j2) = $stack[$k];

					$sStr = ($i1 > $j2 || $i2 < $j2) ? $_ymlCallbacks[$id2]['close'] (substr ($plain, $j2, $i1 - $j2), $stack[$k][3]) : '';

					if ($sStr === null)
						$sStr = substr ($plain, $i2, $i1 - $i2);

					$sLen = strlen ($sStr);

					$plain = substr ($plain, 0, $i2) . $sStr . substr ($plain, $i1);
					$length += $sLen - $i1 + $i2;

					$j1 = $j1 - $i1 + $i2 + $sLen;
					$i1 = $i2 + $sLen;
				}

				// Close current tag
				if ($action == YML_ACTION_ALONE || $action == YML_ACTION_END)
				{
					list ($id2, $i2, $j2, $args) = ($action == YML_ACTION_ALONE ? array ($id1, $i1, $j1, $args) : $stack[$close]);

					$sStr = ($i1 > $j2 || $i2 < $j2) ? $_ymlCallbacks[$id2]['close'] (substr ($plain, $j2, $i1 - $j2), $args) : '';

					if ($sStr === null)
						$sStr = substr ($plain, $i2, $j1 - $i2);

					$sLen = strlen ($sStr);

					$plain = substr ($plain, 0, $i2) . $sStr . substr ($plain, $j1);
					$length += $sLen - $j1 + $i2;

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
				switch ($action)
				{
					// Push or insert tag on the stack
					case YML_ACTION_BEGIN:
						// Call init function if available
						if (isset ($options[$name]['open']))
							$options[$name]['open'] ($expr, $args);

						// Push tag on the stack
						array_splice ($stack, $cross, 0, array (array ($name, $i1, $j1, $args)));

						++$count;

						// Move cursor to end of tag
						$i1 = $j1 - 1;

						break;

					// Call step function
					case YML_ACTION_BREAK:
						$s =& $stack[$close];

						if (isset ($options[$s[0]]['break']))
							$options[$s[0]]['break'] ($expr, substr ($plain, $s[2], $i1 - $s[2]), $s[3]);

						$s[2] = $j1;

						break;

					// Remove tag from the stack
					case YML_ACTION_END:
						array_splice ($stack, $close, 1);

						--$count;

						break;
				}
			}
		}
	}

	return $plain;
}

/*
** Parse tokenized string into (scopes, text) array.
** $token:	tokenized string
** return:	(scopes, text) array, null on parsing error.
*/
function	ymlParse ($token)
{
	$actions = array ('*' => YML_ACTION_ALONE, '+' => YML_ACTION_BEGIN, '/' => YML_ACTION_BREAK, '-' => YML_ACTION_END);
	$characters = array (YML_CHAR_FIELD => true, YML_CHAR_BLOCK => true, YML_CHAR_HEADER => true);
	$length = strlen ($token);
	$scopes = array ();

	// Parse version
	for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
		++$i;

	$version = (int)substr ($token, 0, $i);

	// Parse header
	while ($i < $length && $token[$i] == YML_CHAR_BLOCK)
	{
		++$i;

		// Parse delta
		for ($j = $i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
			++$i;

		if ($i > $j)
			$delta = (int)substr ($token, $j, $i - $j);
		else
			continue;

		// Parse action
		if ($i < $length && isset ($actions[$token[$i]]))
			$action = $actions[$token[$i++]];
		else
			continue;

		// Parse name
		for ($j = $i; $i < $length && !isset ($characters[$token[$i]]); ++$i)
		{
			if ($token[$i] == YML_CHAR_ESCAPE && $i + 1 < $length)
				++$i;
		}

		if ($i > $j)
			$name = substr ($token, $j, $i - $j);
		else
			continue;

		// Parse arguments
		for ($arguments = array (); $i < $length && $token[$i] == YML_CHAR_FIELD; )
		{
			$argument = '';

			for ($j = ++$i; $i < $length && !isset ($characters[$token[$i]]); ++$i)
			{
				if ($token[$i] == YML_CHAR_ESCAPE && $i + 1 < $length)
					++$i;

				$argument .= $token[$i];
			}

			$arguments[] = substr ($token, $j, $i - $j);
		}

		$scopes[] = array ($delta, $action, $name, $arguments);
	}

	if ($i >= $length || $token[$i++] != YML_CHAR_HEADER)
		return null;

	return array ($scopes, substr ($token, $i));
}

/*
** Render tokenized string.
** $token:		tokenized string
** $modifiers:	text modifiers
** return:		rendered string
*/
function	ymlRender ($token, $modifiers)
{
	// Parse tokenized string
	$parsed = ymlParse ($token);

	if ($parsed === null)
		return null;

	list ($scopes, $text) = $parsed;

	// Apply scopes on plain text
	$index = 0;
	$stack = array ();
	$uses = array ();

	foreach ($scopes as $scope)
	{
		list ($delta, $action, $name, $arguments) = $scope;

		$index += $delta;

		if (!isset ($modifiers[$name]))
			continue;

		$modifier = $modifiers[$name];
echo "modifier $action \"$name\" = $index: " . locate ($text, $index) . "<br />";
		// Initialize action effect
		switch ($action)
		{
			case YML_ACTION_ALONE:
			case YML_ACTION_BEGIN:
				// Get precedence level for this modifier
				if (isset ($modifier['level']))
					$level = $modifier['level'];
				else
					$level = 1;

				// Check usage limit for this modifier
				if (isset ($modifier['limit']))
				{
					if (!isset ($uses[$name]))
						$uses[$name] = 0;

					if ($uses[$name] >= $modifier['limit'])
						continue;

					++$uses[$name];
				}

				// Browse pending tags with lower precedence
				for ($touch = count ($stack); $touch > 0 && $level > $stack[$touch - 1][3]; )
					--$touch;

				$close = $action == YML_ACTION_ALONE ? $touch : $touch + 1;

				// Call initializer and push modifier to stack
				if (isset ($modifier['start']))
					$modifier['start'] ($arguments);

				array_splice ($stack, $touch, 0, array (array
				(
					$name,
					$index,
					$arguments,
					$level,
					isset ($modifier['step']) ? $modifier['step'] : null,
					isset ($modifier['stop']) ? $modifier['stop'] : null
				)));

				break;

			case YML_ACTION_BREAK:
			case YML_ACTION_END:
				// Search for matching tag in pending stack
				for ($touch = count ($stack) - 1; $touch >= 0 && $stack[$touch][0] != $name; )
					--$touch;

				if ($touch < 0)
					continue;

				$close = $touch;

				break;

			default:
				continue;
		}

		// Close crossed modifiers
		for ($i = count ($stack) - 1; $i >= $close; --$i)
		{
			$closed =& $stack[$i];
			$body = substr ($text, $closed[1], $index - $closed[1]);

			if (isset ($closed[5]))
				$body = $closed[5] ($body, $closed[2]);
echo "close \"$closed[0]\" = $closed[3] -&gt; $index: " . locate ($text, $closed[1], $index) . " -&gt; " . htmlspecialchars ($body) . "<br />";
			$length = strlen ($body);
			$text = substr ($text, 0, $closed[1]) . $body . substr ($text, $index);

			$index = $closed[1] + $length;
		}

		// Update modifiers indices
		for ($i = count ($stack) - 1; $i >= $touch; --$i)
{
echo "update \"" . $stack[$i][0] . "\" = $index: " . locate ($text, $index) . "<br />";
			$stack[$i][1] = $index;
}

		// Finalize action effect
		switch ($action)
		{
			// Remove tag from the stack
			case YML_ACTION_ALONE:
			case YML_ACTION_END:
echo "remove \"" . $stack[$touch][0] . "\"<br />";
				array_splice ($stack, $touch, 1);

				break;

			// Call step function
			case YML_ACTION_BREAK:
				$broken =& $stack[$touch];
echo "break on \"$broken[1]\"<br />";
				if (isset ($broken[4]))
					$broken[4] (substr ($text, $broken[1], $index - $broken[1]), $broken[2]);

				$broken[1] = $index;

				break;
		}
	}

	return $text;
}

?>
