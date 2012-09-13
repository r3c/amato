<?php

/*
** Internal constants.
*/
define ('YML_ACTION_ALONE',		0);
define ('YML_ACTION_BEGIN',		1);
define ('YML_ACTION_BREAK',		2);
define ('YML_ACTION_END',		3);

define ('YML_DECODE_CHARACTER',	0);
define ('YML_DECODE_PARAM',		1);

define ('YML_PARAM_BEGIN',		'(');
define ('YML_PARAM_END',		')');
define ('YML_PARAM_ESCAPE',		'\\');

define ('YML_TOKEN_END',		'|');
define ('YML_TOKEN_ESCAPE',		'\\');
define ('YML_TOKEN_PARAM',		',');
define ('YML_TOKEN_SCOPE',		';');

/*
** Compile tag parsing rules.
** $rules:	tag parsing rules
** $params:	parameters character classes
** return:	compiled parsing rules
*/
function	ymlCompile ($rules, $params)
{
	// Build character classes
	$classes = array ();

	foreach ($params as $class => $expression)
	{
		if (strlen ($expression) < 1)
			throw new Exception ('invalid character class "' . $class . '"');

		switch ($expression[0])
		{
			case '-':
				$mode = false;

				break;

			case '+':
				$mode = true;

				break;

			default:
				throw new Exception ('invalid character class mode "' . $expression[0] . '"');
		}

		$characters = array ();
		$length = strlen ($expression);

		for ($j = 1; $j < $length; ++$j)
		{
			if ($j + 2 < $length && $expression[$j + 1] == '-')
			{
				$lower = ord ($expression[$j]);
				$upper = ord ($expression[$j + 2]);

				for ($ord = $lower; $ord <= $upper; ++$ord)
					$characters[chr ($ord)] = true;

				$j += 2;
			}
			else
				$characters[$expression[$j]] = true;
		}

		$classes[$class] = array ($mode, array_keys ($characters));
	}

	// Process rules
	$actions = array (YML_ACTION_ALONE => '*', YML_ACTION_BEGIN => '+', YML_ACTION_BREAK => '/', YML_ACTION_END => '-');
	$parser = array (null, array ());
	$wrong = false;

	foreach ($rules as $name => $rule)
	{
		// Browse defined tag patterns
		foreach ($rule['tags'] as $tag => $action)
		{
			// Build parsing tree and decoding array
			$decode = array ();
			$length = strlen ($tag);
			$node =& $parser[0];
			$pos = 0;

			for ($i = 0; $i < $length; ++$i)
			{
				unset ($branch);
if (substr ($tag, 0, 4) == '[box') echo "tag $tag, character $tag[$i]<br />";
				if ($tag[$i] == YML_PARAM_BEGIN)
				{
					for ($j = ++$i; $i < $length && $tag[$i] != YML_PARAM_END; )
						++$i;

					$class = substr ($tag, $j, $i - $j);

					if (!isset ($classes[$class]))
						throw new Exception ('undefined or invalid character class "' . $class . '"');

					$branch = array (&$node, $pos, null);

					if (!$classes[$class][0])
					{
						$target =& $wrong;

						if (isset ($node['']))
							throw new Exception ('ambiguous default transition of parameter #' . $pos . ' for tag "' . $tag . '" in rule "' . $name . '"');

						$node[''] =& $branch;
					}
					else
						$target =& $branch;

					foreach ($classes[$class][1] as $character)
					{
						if (isset ($node[$character]))
							throw new Exception ('ambiguous character "' . $character . '" of parameter #' . $pos . ' for tag "' . $tag . '" in rule "' . $name . '"');

						$node[$character] =& $target;
					}

					$decode[] = array (YML_DECODE_PARAM, $pos++);
				}
				else
				{
					if ($tag[$i] == YML_PARAM_ESCAPE && $i + 1 < $length)
						++$i;

					$character = $tag[$i];
					$decode[] = array (YML_DECODE_CHARACTER, $character);

					if (!isset ($node[$character]))
{
						$node[$character] = array (null, null, null);
if (substr ($tag, 0, 4) == '[box'){echo "create new branch for char $character<br />"; var_dump($node[$character]);}} else if (substr ($tag, 0, 4) == '[box'){echo "reuse branch for char $character<br />"; var_dump ($node[$character]);}
					$branch =& $node[$character];

					if ($branch === $wrong || $branch[1] !== null)
						throw new Exception ('ambiguous character "' . $character . '" at position #' . $i . ' for tag "' . $tag . '" in rule "' . $name . '"');

					$node =& $branch[0];
				}
			}

			// Register terminal node
			if (isset ($branch))
			{
				if (!isset ($actions[$action]))
					throw new Exception ('undefined action "' . $action . '" for tag "' . $tag . '" in rule "' . $name . '"');

				if ($branch[2] !== null)
					throw new Exception ('conflict for tag "' . $tag . '" in rule "' . $name . '"');

				$branch[2] = $actions[$action] . $name;
			}

			// Register decoding array
			if (!isset ($rule['decode']) || !$rule['decode'])
			{
				if (!isset ($parser[1][$action]))
					$parser[1][$action] = array ();

				$decodeAction =& $parser[1][$action];

				if (!isset ($decodeAction[$name]))
					$decodeAction[$name] = array ();

				$decodeName =& $decodeAction[$name];

				if (!isset ($decodeName[$pos]))
					$decodeName[$pos] = $decode;
			}
		}
	}

	return $parser;
}

/*
** Decode tokenized string to plain format.
** $token:	tokenized string
** $parser:	compiled parsing rules
** return:	plain string
*/
function	ymlDecode ($token, $parser)
{
	$parsed = ymlParse ($token);

	if ($parsed === null)
		return null;

	list ($scopes, $clean) = $parsed;

	$index = 0;

	foreach ($scopes as $scope)
	{
		list ($delta, $action, $name, $params) = $scope;

		$count = count ($params);
		$index += $delta;

		if (!isset ($parser[1][$action]))
			continue;

		$decodeAction =& $parser[1][$action];

		if (!isset ($decodeAction[$name]))
			continue;

		$decodeName =& $decodeAction[$name];

		if (!isset ($decodeName[$count]))
			continue;

		$decode =& $decodeName[$count];
		$tag = '';

		foreach ($decode as $item)
		{
			switch ($item[0])
			{
				case YML_DECODE_CHARACTER:
					$tag .= $item[1];

					break;

				case YML_DECODE_PARAM:
					$tag .= $item[1] < $count ? $params[$item[1]] : '';

					break;
			}
		}

		$clean = substr_replace ($clean, $tag, $index, 0);
		$index += strlen ($tag);
	}

	return $clean;
}

/*
** Encode plain string to tokenized format.
** $plain:	plain string
** $parser:	compiled parsing rules
** return:	tokenized string
*/
function	ymlEncode ($plain, $parser)
{
	$cursors = array ();
	$escape = array (YML_TOKEN_PARAM => true, YML_TOKEN_SCOPE => true, YML_TOKEN_END => true, YML_TOKEN_ESCAPE => true);
	$index = 0;
	$length = strlen ($plain);
	$tree =& $parser[0];
	$token = '1';

	// Parse entire string
	if (isset ($tree))
	{
		for ($i = 0; $i < $length; ++$i)
		{
			$trail = 0;

			array_push ($cursors, array (&$tree, array ()));

			foreach ($cursors as &$cursor)
			{
				if (isset ($cursor[0]))
				{
					$node =& $cursor[0];

					// Find next branch depending on matching mode
					if (isset ($node['']))
					{
						if (!isset ($node[$plain[$i]]))
							$branch =& $node[''];
						else
							unset ($branch);
					}
					else
					{
						if (isset ($node[$plain[$i]]))
							$branch =& $node[$plain[$i]];
						else
							unset ($branch);
					}

					// Invalidate cursor on missing branch
					if (!isset ($branch))
						unset ($cursor[0]);

					// Follow branch to next node
					else
					{
						// Capture parameter if we're parsing one
						if ($branch[1] !== null)
						{
							$pos = $branch[1];

							if (!isset ($cursor[1][$pos]))
								$cursor[1][$pos] = '';

							$cursor[1][$pos] .= $plain[$i];
						}

						// Move cursor to non-terminal node
						if ($branch[2] === null)
							$cursor[0] =& $branch[0];

						// Emit action for terminal node
						else
						{
							// Append tokenized action to string
							$tagLength = count ($cursors) - $trail;
							$tagStart = $i - $tagLength + 1;

							$token .= YML_TOKEN_SCOPE . ($tagStart - $index);
							$index = $tagStart;

							foreach (str_split ($branch[2]) as $character)
							{
								if (isset ($escape[$character]))
									$token .= YML_TOKEN_ESCAPE;

								$token .= $character;
							}

							foreach ($cursor[1] as $param)
							{
								$token .= YML_TOKEN_PARAM;

								foreach (str_split ($param) as $character)
								{
									if (isset ($escape[$character]))
										$token .= YML_TOKEN_ESCAPE;

									$token .= $character;
								}
							}

							// Remove match from string
							$length -= $tagLength;
							$plain = substr_replace ($plain, '', $tagStart, $tagLength);
							$i = $tagStart - 1;

							// Clear cursors array
							$cursors = array ();
						}
					}
				}

				++$trail;
			}

			while (count ($cursors) > 0 && !isset ($cursors[0][0]))
				array_shift ($cursors);
		}
	}

	return $token . YML_TOKEN_END . $plain;
}

/*
** Parse tokenized string into (scopes, text) array.
** $token:	tokenized string
** return:	(scopes, text) array, null on parsing error.
*/
function	ymlParse ($token)
{
	$actions = array ('*' => YML_ACTION_ALONE, '+' => YML_ACTION_BEGIN, '/' => YML_ACTION_BREAK, '-' => YML_ACTION_END);
	$escape = array (YML_TOKEN_PARAM => true, YML_TOKEN_SCOPE => true, YML_TOKEN_END => true);
	$length = strlen ($token);
	$scopes = array ();

	// Parse version
	for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
		++$i;

	$version = (int)substr ($token, 0, $i);

	// Parse header
	while ($i < $length && $token[$i] == YML_TOKEN_SCOPE)
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
		for ($j = $i; $i < $length && !isset ($escape[$token[$i]]); ++$i)
		{
			if ($token[$i] == YML_TOKEN_ESCAPE && $i + 1 < $length)
				++$i;
		}

		if ($i > $j)
			$name = substr ($token, $j, $i - $j);
		else
			continue;

		// Parse params
		for ($params = array (); $i < $length && $token[$i] == YML_TOKEN_PARAM; )
		{
			$param = '';

			for ($j = ++$i; $i < $length && !isset ($escape[$token[$i]]); ++$i)
			{
				if ($token[$i] == YML_TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$param .= $token[$i];
			}

			$params[] = substr ($token, $j, $i - $j);
		}

		$scopes[] = array ($delta, $action, $name, $params);
	}

	if ($i >= $length || $token[$i++] != YML_TOKEN_END)
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

	list ($scopes, $clean) = $parsed;

	// Apply scopes on plain text
	$index = 0;
	$stack = array ();
	$uses = array ();

	foreach ($scopes as $scope)
	{
		list ($delta, $action, $name, $params) = $scope;

		$index += $delta;

		if (!isset ($modifiers[$name]))
			continue;

		$modifier = $modifiers[$name];

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
						continue 2;

					++$uses[$name];
				}

				// Browse pending tags with lower precedence
				for ($touch = count ($stack); $touch > 0 && $level > $stack[$touch - 1][3]; )
					--$touch;

				$close = $action == YML_ACTION_ALONE ? $touch : $touch + 1;

				// Call initializer and push modifier to stack
				if (isset ($modifier['start']))
					$modifier['start'] ($name, $params);

				array_splice ($stack, $touch, 0, array (array
				(
					$name,
					$index,
					$params,
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
					continue 2;

				$close = $touch;

				break;

			default:
				continue 2;
		}

		// Close crossed modifiers
		for ($i = count ($stack) - 1; $i >= $close; --$i)
		{
			$closed =& $stack[$i];
			$length = $index - $closed[1];

			$body = substr ($clean, $closed[1], $length);

			if (isset ($closed[5]))
				$body = $closed[5] ($closed[0], $closed[2], $body);

			$clean = substr_replace ($clean, $body, $closed[1], $length);
			$index = $closed[1] + strlen ($body);
		}

		// Update modifiers indices
		for ($i = count ($stack) - 1; $i >= $touch; --$i)
			$stack[$i][1] = $index;

		// Finalize action effect
		switch ($action)
		{
			// Remove tag from the stack
			case YML_ACTION_ALONE:
			case YML_ACTION_END:
				array_splice ($stack, $touch, 1);

				break;

			// Call step function
			case YML_ACTION_BREAK:
				$broken =& $stack[$touch];

				if (isset ($broken[4]))
					$broken[4] ($broken[0], $broken[2], substr ($clean, $broken[1], $index - $broken[1]));

				$broken[1] = $index;

				break;
		}
	}

	return $clean;
}

?>
