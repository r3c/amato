<?php

/*
** Internal constants.
*/
define ('YML_ACTION_APPLY',		0);
define ('YML_ACTION_START',		1);
define ('YML_ACTION_STEP',		2);
define ('YML_ACTION_STOP',		3);

define ('YML_BRANCH_EMPTY',		0);
define ('YML_BRANCH_LOOP',		1);
define ('YML_BRANCH_NEXT',		2);

define ('YML_DECODE_CHARACTER',	0);
define ('YML_DECODE_PARAM',		1);

define ('YML_PATTERN_BEGIN',	'(');
define ('YML_PATTERN_END',		')');
define ('YML_PATTERN_ESCAPE',	'\\');

define ('YML_TOKEN_ESCAPE',		'\\');
define ('YML_TOKEN_PARAM',		',');
define ('YML_TOKEN_PLAIN',		'|');
define ('YML_TOKEN_SCOPE',		';');

define ('YML_TYPE_BEGIN',		0);
define ('YML_TYPE_BETWEEN',		1);
define ('YML_TYPE_END',			2);
define ('YML_TYPE_RESUME',		3);
define ('YML_TYPE_SINGLE',		4);
define ('YML_TYPE_SWITCH',		5);

$ymlConvert = array
(
	YML_TYPE_BEGIN		=> array (YML_ACTION_START, YML_ACTION_START),
	YML_TYPE_BETWEEN	=> array (null, YML_ACTION_STEP),
	YML_TYPE_END		=> array (null, YML_ACTION_STOP),
	YML_TYPE_RESUME		=> array (YML_ACTION_START, YML_ACTION_STEP),
	YML_TYPE_SINGLE		=> array (YML_ACTION_APPLY, YML_ACTION_APPLY),
	YML_TYPE_SWITCH		=> array (YML_ACTION_START, YML_ACTION_STOP)
);

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
	$parser = array (null, array ());

	foreach ($rules as $name => $rule)
	{
		// Browse defined tag patterns
		foreach ($rule['tags'] as $pattern => $type)
		{
			// Build parsing tree and decoding array
			$decode = array ();
			$length = strlen ($pattern);
			$node =& $parser[0];
			$param = 0;

			for ($i = 0; $i < $length; ++$i)
			{
				unset ($branch);

				switch ($pattern[$i])
				{
					case YML_PATTERN_BEGIN:
						for ($j = ++$i; $i < $length && $pattern[$i] != YML_PATTERN_END; )
							++$i;

						$class = substr ($pattern, $j, $i - $j);

						if (!isset ($classes[$class]))
							throw new Exception ('undefined or invalid character class "' . $class . '"');

						$branch = array (&$node, YML_BRANCH_LOOP, $param, null);

						if (!$classes[$class][0])
						{
							$target = array (null, YML_BRANCH_EMPTY, null, null);

							if (isset ($node['']))
								throw new Exception ('ambiguous default transition of class "' . $class . '" for pattern "' . $pattern . '" in rule "' . $name . '"');

							$node[''] =& $branch;
						}
						else
							$target = $branch;

						foreach ($classes[$class][1] as $character)
						{
							if (isset ($node[$character]))
								throw new Exception ('ambiguous character "' . $character . '" of class #' . $class . ' for pattern "' . $pattern . '" in rule "' . $name . '"');

							$node[$character] = $target;
						}

						$decode[] = array (YML_DECODE_PARAM, $param++);

						break;

					default:
						if ($pattern[$i] == YML_PATTERN_ESCAPE && $i + 1 < $length)
							++$i;

						$character = $pattern[$i];
						$decode[] = array (YML_DECODE_CHARACTER, $character);

						if (!isset ($node[$character]))
							$node[$character] = array (null, YML_BRANCH_NEXT, null, null);

						$branch =& $node[$character];

						if ($branch[1] == YML_BRANCH_LOOP)
							throw new Exception ('ambiguous character "' . $character . '" at position #' . $i . ' for pattern "' . $pattern . '" in rule "' . $name . '"');
						else
							$branch[1] = YML_BRANCH_NEXT;

						$node =& $branch[0];

						break;
				}
			}

			// Register terminal node
			if (isset ($branch))
			{
				if ($branch[3] !== null)
					throw new Exception ('conflict for pattern "' . $pattern . '" in rule "' . $name . '"');

				$branch[3] = array ($name, $type/*, $value*/);
			}

			// Register decoding array
			if (!isset ($rule['decode']) || !$rule['decode'])
				$parser[1][$name . '.' . $type . '.' . $param] = $decode;
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
	global	$ymlConvert;

	$parsed = ymlParse ($token);

	if ($parsed === null)
		return null;

	list ($scopes, $clean) = $parsed;

	$decodes =& $parser[1];
	$index = 0;

	foreach ($scopes as $scope)
	{
		list ($delta, $name, $action, $params) = $scope;

		$count = count ($params);
		$index += $delta;

		// Try to find decoder by reverting action to type
		if (!isset ($opens[$name]))
			$opens[$name] = 0;

		$decode = null;
		$open = count ($opens[$name]) > 0 ? 1 : 0;

		foreach ($ymlConvert as $type => $actions)
		{
			if ($actions[$open] === $action)
			{
				$key = $name . '.' . $type . '.' . $count;

				if (isset ($decodes[$key]))
				{
					$decode = $decodes[$key];

					break;
				}
			}
		}

		// Use found decoder (if any) to inject tag into clean string
		if ($decode !== null)
		{
			// Generate decoded tag string from decoder
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

			// Update opened tags counter
			switch ($action)
			{
				case YML_ACTION_START:
					++$opens[$name];

					break;

				case YML_ACTION_STOP:
					--$opens[$name];

					break;
			}
		}
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
	global	$ymlConvert; // FIXME

	$token = '1';
	$tree =& $parser[0];

	if ($tree !== null)
	{
		// Parse plain string
		$cursors = array ();
		$length = strlen ($plain);
		$seal = 0;
		$tags = array ();

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
						else if ($node[$plain[$i]][1] != YML_BRANCH_EMPTY)
							$branch =& $node[$plain[$i]];
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
						if ($branch[2] !== null)
						{
							$param = $branch[2];

							if (!isset ($cursor[1][$param]))
								$cursor[1][$param] = '';

							$cursor[1][$param] .= $plain[$i];
						}

						// Move cursor to non-terminal node
						if ($branch[3] === null)
							$cursor[0] =& $branch[0];

						// Emit action for terminal node
						else
						{
							list ($name, $type/*, $value*/) = $branch[3];

// <Crappy Code>
							// Get compatible and unprocessed tags on stack
							$links = array ();

							for ($link = count ($tags) - 1; $link >= $seal; --$link)
							{
								if ($tags[$link][1] !== null && $tags[$link][2] == $name)
									$links[] = $link;
							}

							// Get action, invalidate cursor if none available
							$action = $ymlConvert[$type][count ($links) > 0 ? 1 : 0];

							if ($action === null)
								unset ($cursor[0]);

							// Push new tag to list for action
							else
							{
								$tagLength = count ($cursors) - $trail;
								$tagStart = $i - $tagLength + 1;

								$tags[] = array ($tagStart, $tagLength, $name, $action, $cursor[1]);

								if ($action == YML_ACTION_APPLY || $action == YML_ACTION_STOP)
								{
									array_unshift ($links, count ($tags) - 1);

									// Remove tags and flag them as processed
									foreach ($links as $link)
									{
										$tagLength = $tags[$link][1];
										$tagStart = $tags[$link][0];

										for ($after = count ($tags) - 1; $after > $link; --$after)
											$tags[$after][0] -= $tagLength;

										$length -= $tagLength;
										$plain = substr_replace ($plain, '', $tagStart, $tagLength);
										$i -= $tagLength;

										$tags[$link][1] = null;
									}

									// Seal flagged tags for faster processing
									while ($seal < count ($tags) && $tags[$seal][1] === null)
										++$seal;
								}

								// Reset all cursors
								$cursors = array ();

								break;
							}
// </Crappy Code>
						}
					}
				}

				++$trail;
			}

			while (count ($cursors) > 0 && !isset ($cursors[0][0]))
				array_shift ($cursors);
		}

		// Tokenize processed tags into scopes
		$actions = array (YML_ACTION_APPLY => '/', YML_ACTION_START => '<', YML_ACTION_STEP => '!', YML_ACTION_STOP => '>');
		$escape = array (YML_TOKEN_ESCAPE => true, YML_TOKEN_PARAM => true, YML_TOKEN_PLAIN => true, YML_TOKEN_SCOPE => true);
		$shift = 0;

		foreach ($tags as $tag)
		{
			list ($start, $length, $name, $action, $params) = $tag;

			if ($length !== null)
				continue;

			$token .= YML_TOKEN_SCOPE . ($start - $shift) . $actions[$action];
			$shift = $start;

			foreach (str_split ($name) as $character)
			{
				if (isset ($escape[$character]))
					$token .= YML_TOKEN_ESCAPE;

				$token .= $character;
			}

			foreach ($params as $param)
			{
				$token .= YML_TOKEN_PARAM;

				foreach (str_split ($param) as $character)
				{
					if (isset ($escape[$character]))
						$token .= YML_TOKEN_ESCAPE;

					$token .= $character;
				}
			}
		}
	}

	return $token . YML_TOKEN_PLAIN . $plain;
}

/*
** Parse tokenized string into (scopes, text) array.
** $token:	tokenized string
** return:	(scopes, text) array, null on parsing error.
*/
function	ymlParse ($token)
{
	$actions = array ('/' => YML_ACTION_APPLY, '<' => YML_ACTION_START, '!' => YML_ACTION_STEP, '>' => YML_ACTION_STOP);
	$escape = array (YML_TOKEN_PARAM => true, YML_TOKEN_PLAIN => true, YML_TOKEN_SCOPE => true);
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

		$scopes[] = array ($delta, $name, $action, $params);
	}

	if ($i >= $length || $token[$i++] != YML_TOKEN_PLAIN)
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
		list ($delta, $name, $action, $params) = $scope;

		$index += $delta;

		if (!isset ($modifiers[$name]))
			continue;

		$modifier = $modifiers[$name];

		// Initialize action effect
		switch ($action)
		{
			case YML_ACTION_APPLY:
			case YML_ACTION_START:
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

				$close = $action == YML_ACTION_APPLY ? $touch : $touch + 1;

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

			case YML_ACTION_STEP:
			case YML_ACTION_STOP:
				// Search for matching tag in pending stack
				for ($touch = count ($stack) - 1; $touch >= 0 && $stack[$touch][0] != $name; )
					--$touch;

				if ($touch < 0)
					continue 2;

				$close = $action == YML_ACTION_STEP ? $touch + 1 : $touch;

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

		// Finalize action effect
		switch ($action)
		{
			// Remove tag from the stack
			case YML_ACTION_APPLY:
			case YML_ACTION_STOP:
				array_splice ($stack, $touch, 1);

				break;

			// Call step function
			case YML_ACTION_STEP:
				$broken =& $stack[$touch];
				$length = $index - $broken[1];

				$body = substr ($clean, $broken[1], $length);

				if (isset ($broken[4]))
					$body = $broken[4] ($broken[0], $broken[2], $body);

				$clean = substr_replace ($clean, $body, $broken[1], $length);
				$index = $broken[1] + strlen ($body);

				$broken[1] = $index;

				break;
		}

		// Update modifiers indices
		for ($i = count ($stack) - 1; $i >= $touch; --$i)
			$stack[$i][1] = $index;
	}

	return $clean;
}

?>
