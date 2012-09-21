<?php

/*
** Internal constants.
*/
define ('YML_ACTION_APPLY',		0);
define ('YML_ACTION_START',		1);
define ('YML_ACTION_STEP',		2);
define ('YML_ACTION_STOP',		3);

define ('YML_BRANCH_INVALID',	0);
define ('YML_BRANCH_SHARED',	1);
define ('YML_BRANCH_UNIQUE',	2);

define ('YML_DECODE_CHARACTER',	0);
define ('YML_DECODE_PARAM',		1);

define ('YML_PATTERN_BEGIN',	'(');
define ('YML_PATTERN_END',		')');
define ('YML_PATTERN_ESCAPE',	'\\');
define ('YML_PATTERN_LOOP',		'*');

define ('YML_TOKEN_ESCAPE',		'\\');
define ('YML_TOKEN_PARAM',		',');
define ('YML_TOKEN_PLAIN',		'|');
define ('YML_TOKEN_SCOPE',		';');
define ('YML_TOKEN_VALUE',		'=');

define ('YML_TYPE_BEGIN',		0);
define ('YML_TYPE_BETWEEN',		1);
define ('YML_TYPE_END',			2);
define ('YML_TYPE_RESUME',		3);
define ('YML_TYPE_SINGLE',		4);
define ('YML_TYPE_SWITCH',		5);

define ('YML_VERSION',			1);

$ymlConvert = array
(
	YML_TYPE_BEGIN		=> array (YML_ACTION_START, YML_ACTION_START),
	YML_TYPE_BETWEEN	=> array (null, YML_ACTION_STEP),
	YML_TYPE_END		=> array (null, YML_ACTION_STOP),
	YML_TYPE_RESUME		=> array (YML_ACTION_START, YML_ACTION_STEP),
	YML_TYPE_SINGLE		=> array (YML_ACTION_APPLY, YML_ACTION_APPLY),
	YML_TYPE_SWITCH		=> array (YML_ACTION_START, YML_ACTION_STOP)
);

class	yML
{
	/*
	** Compile tag parsing rules.
	** $rules:		parsing rules
	** $classes:	character classes
	** $codes:		compiled encoding and decoding structure
	*/
	public static function	compile ($rules, $classes)
	{
		// Compile character classes into special structures
		$specials = array ();

		foreach ($classes as $class => $expression)
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

			$specials[$class] = array ($mode, array_keys ($characters));
		}

		// Process rules
		$decodes = array ();
		$tree = null;

		foreach ($rules as $name => $rule)
		{
			// Browse defined tag patterns
			foreach ($rule['tags'] as $pattern => $behavior)
			{
				// Build parsing tree and decoding array
				$count = 0;
				$decode = array ();
				$length = strlen ($pattern);
				$node =& $tree;
				$type = $behavior[0];
				$value = isset ($behavior[1]) ? $behavior[1] : '';

				for ($i = 0; $i < $length; ++$i)
				{
					unset ($branch);
					unset ($target);

					switch ($pattern[$i])
					{
						case YML_PATTERN_BEGIN:
							// Parse class name
							for ($j = ++$i; $i < $length && $pattern[$i] != YML_PATTERN_LOOP && $pattern[$i] != YML_PATTERN_END; )
								++$i;

							$class = substr ($pattern, $j, $i - $j);

							if (!isset ($specials[$class]))
								throw new Exception ('undefined or invalid character class "' . $class . '"');

							// Parse loop if specified
							switch ($pattern[$i])
							{
								case YML_PATTERN_LOOP:
									$branch = array (&$node, YML_BRANCH_SHARED, $count, null);

									++$i;

									break;

								default:
									$branch = array (null, YML_BRANCH_SHARED, $count, null);

									break;
							}

							// Define target branch depending on special mode
							if (!$specials[$class][0])
							{
								$target = array (null, YML_BRANCH_INVALID, null, null);

								if (isset ($node['']))
									throw new Exception ('ambiguous default transition of class "' . $class . '" for pattern "' . $pattern . '" in rule "' . $name . '"');

								$node[''] =& $branch;
							}
							else
								$target =& $branch;

							// Assign target branch to each class character
							foreach ($specials[$class][1] as $character)
							{
								if (isset ($node[$character]))
									throw new Exception ('ambiguous character "' . $character . '" of class #' . $class . ' for pattern "' . $pattern . '" in rule "' . $name . '"');

								$node[$character] =& $target;
							}

							$decode[] = array (YML_DECODE_PARAM, $count++);

							if ($branch[1] == YML_BRANCH_UNIQUE)
								$node =& $branch[0];

							break;

						default:
							if ($pattern[$i] == YML_PATTERN_ESCAPE && $i + 1 < $length)
								++$i;

							$character = $pattern[$i];
							$decode[] = array (YML_DECODE_CHARACTER, $character);

							if (!isset ($node[$character]))
								$node[$character] = array (null, YML_BRANCH_UNIQUE, null, null);

							$branch =& $node[$character];

							if ($branch[1] == YML_BRANCH_SHARED)
								throw new Exception ('ambiguous character "' . $character . '" at position #' . $i . ' for pattern "' . $pattern . '" in rule "' . $name . '"');

							$branch[1] = YML_BRANCH_UNIQUE;
							$node =& $branch[0];

							break;
					}
				}

				// Register terminal node
				if (isset ($branch))
				{
					if ($branch[3] !== null)
						throw new Exception ('conflict for pattern "' . $pattern . '" in rule "' . $name . '"');

					$branch[3] = array ($name, $type, $value);
				}

				// Register decoding array
				if (!isset ($rule['decode']) || !$rule['decode'])
					$decodes[$name . '.' . $type . '.' . $count . '.' . $value] = $decode;
			}
		}

		return array ($tree, $decodes);
	}

	/*
	** Decode tokenized string to plain format.
	** $token:	tokenized string
	** $codes:	compiled encoding and decoding structure
	** return:	plain string
	*/
	public static function	decode ($token, $codes)
	{
		global	$ymlConvert;

		$parsed = self::parse ($token);

		if ($parsed === null)
			return null;

		list ($scopes, $clean) = $parsed;

		$decodes =& $codes[1];
		$index = 0;

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $value, $params) = $scope;

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
					$key = $name . '.' . $type . '.' . $count . '.' . $value;

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
	** $codes:	compiled encoding and decoding structure
	** return:	tokenized string
	*/
	public static function	encode ($plain, $codes)
	{
		global	$ymlConvert; // FIXME

		$token = YML_VERSION;
		$tree =& $codes[0];

		// Parse plain string if a parsing tree is available
		if ($tree !== null)
		{
			$closed = 0;
			$cursors = array ();
			$length = strlen ($plain);
			$tags = array ();

			for ($i = 0; $i <= $length; ++$i)
			{
				$character = $i < $length ? $plain[$i] : null;

				array_push ($cursors, new yMLCursor ($tree, $i));

				for ($current = count ($cursors) - 1; $current >= 0; --$current)
				{
					$cursor =& $cursors[$current];

					// Nothing to do until cursor can't be moved to next node
					if ($cursor->move ($character, $i + 1))
						continue;

					// Process this cursor's last matched tag, if any
					if (isset ($cursor->match))
					{
						list ($name, $type, $value) = $cursor->match;

						// Browse stack for compatible unprocessed tags
						$links = array ();

						for ($link = count ($tags) - 1; $link >= $closed; --$link)
						{
							if ($tags[$link][1] !== null && $tags[$link][2] == $name)
								$links[] = $link;
						}

						// Deduce action from tag type and links
						$action = $ymlConvert[$type][count ($links) > 0 ? 1 : 0];

						if ($action !== null)
						{
							// Remove all cursors before current one
							array_splice ($cursors, 0, $current);

							// Remove all cursors after this one having a match
							// that overlaps with current one's
							for ($after = count ($cursors) - 1; $after > 0; --$after)
							{
								if ($cursors[$after]->start < $cursor->start + $cursor->length)
									array_splice ($cursors, $after, 1);
							}

							// Add current cursor to tags
							$tags[] = array ($cursor->start, $cursor->length, $name, $action, $value, $cursor->params);

							if ($action == YML_ACTION_APPLY || $action == YML_ACTION_STOP)
							{
								array_unshift ($links, count ($tags) - 1);

								// Remove tags and flag them as processed
								foreach ($links as $link)
								{
									$tagLength = $tags[$link][1];
									$tagStart = $tags[$link][0];

									// Shift cursors after current one
									for ($after = count ($cursors) - 1; $after > 0; --$after)
										$cursors[$after]->start -= $tagLength;

									// Shift tags after current one
									for ($after = count ($tags) - 1; $after > $link; --$after)
										$tags[$after][0] -= $tagLength;

									// Remove tag from string
									$length -= $tagLength;
									$plain = substr_replace ($plain, '', $tagStart, $tagLength);
									$i -= $tagLength;

									$tags[$link][1] = null;
								}

								// Close resolved tags for faster processing
								while ($closed < count ($tags) && $tags[$closed][1] === null)
									++$closed;
							}

							// Current cursor is now first one
							$current = 0;
						}
					}

					// Remove cursor once used
					array_splice ($cursors, $current, 1);
				}
			}

			// Tokenize processed tags into scopes
			$actions = array (YML_ACTION_APPLY => '/', YML_ACTION_START => '<', YML_ACTION_STEP => '!', YML_ACTION_STOP => '>');
			$escape = array (YML_TOKEN_ESCAPE => true, YML_TOKEN_PARAM => true, YML_TOKEN_PLAIN => true, YML_TOKEN_SCOPE => true, YML_TOKEN_VALUE => true);
			$shift = 0;

			foreach ($tags as $tag)
			{
				list ($start, $length, $name, $action, $value, $params) = $tag;

				if ($length !== null)
					continue;

				// Write delta offset and action
				$token .= YML_TOKEN_SCOPE . ($start - $shift) . $actions[$action];
				$shift = $start;

				// Write tag name
				foreach (str_split ($name) as $character)
				{
					if (isset ($escape[$character]))
						$token .= YML_TOKEN_ESCAPE;

					$token .= $character;
				}

				// Write tag value
				if ($value)
				{
					$token .= YML_TOKEN_VALUE;

					foreach (str_split ($value) as $character)
					{
						if (isset ($escape[$character]))
							$token .= YML_TOKEN_ESCAPE;

						$token .= $character;
					}
				}

				// Write tag parameters
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
	** Render tokenized string.
	** $token:		tokenized string
	** $modifiers:	text modifiers
	** return:		rendered string
	*/
	public static function	render ($token, $modifiers)
	{
		// Parse tokenized string
		$parsed = self::parse ($token);

		if ($parsed === null)
			return null;

		list ($scopes, $clean) = $parsed;

		// Apply scopes on plain text
		$index = 0;
		$stack = array ();
		$uses = array ();

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $value, $params) = $scope;

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
					for ($touch = count ($stack); $touch > 0 && $level > $stack[$touch - 1][0]; )
						--$touch;

					$close = $action == YML_ACTION_APPLY ? $touch : $touch + 1;

					// Call initializer and push modifier to stack
					if (isset ($modifier['start']))
						$modifier['start'] ($name, $value, $params);

					array_splice ($stack, $touch, 0, array (array
					(
						$level,
						$index,
						$name,
						$value,
						$params,
						isset ($modifier['step']) ? $modifier['step'] : null,
						isset ($modifier['stop']) ? $modifier['stop'] : null
					)));

					break;

				case YML_ACTION_STEP:
				case YML_ACTION_STOP:
					// Search for matching tag in pending stack, cancel if none
					for ($touch = count ($stack) - 1; $touch >= 0 && $stack[$touch][2] != $name; )
						--$touch;

					if ($touch < 0)
						continue 2;

					$close = $action == YML_ACTION_STEP ? $touch + 1 : $touch;

					// Update tag value and parameters
					$broken =& $stack[$touch];

					foreach ($params as $key => $value)
						$broken[4][$key] = $value;

					$broken[3] = $value;

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

				if (isset ($closed[6]))
					$body = $closed[6] ($closed[2], $closed[3], $closed[4], $body);

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

					if (isset ($broken[5]))
						$body = $broken[5] ($broken[2], $broken[3], $broken[4], $body);

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

	/*
	** Parse tokenized string into (scopes, text) array.
	** $token:	tokenized string
	** return:	(scopes, text) array, null on parsing error.
	*/
	private static function	parse ($token)
	{
		$actions = array ('/' => YML_ACTION_APPLY, '<' => YML_ACTION_START, '!' => YML_ACTION_STEP, '>' => YML_ACTION_STOP);
		$escape = array (YML_TOKEN_PARAM => true, YML_TOKEN_PLAIN => true, YML_TOKEN_SCOPE => true, YML_TOKEN_VALUE => true);
		$length = strlen ($token);
		$scopes = array ();

		// Parse version
		for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
			++$i;

		$version = (int)substr ($token, 0, $i);

		if ($version != YML_VERSION)
			return null;

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
			$name = '';

			for ($j = $i; $i < $length && !isset ($escape[$token[$i]]); ++$i)
			{
				if ($token[$i] == YML_TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$name .= $token[$i];
			}

			// Parse value
			$value = '';

			if ($i < $length && $token[$i] == YML_TOKEN_VALUE)
			{
				for (++$i; $i < $length && !isset ($escape[$token[$i]]); ++$i)
				{
					if ($token[$i] == YML_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$value .= $token[$i];
				}
			}

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

			$scopes[] = array ($delta, $name, $action, $value, $params);
		}

		if ($i >= $length || $token[$i++] != YML_TOKEN_PLAIN)
			return null;

		return array ($scopes, substr ($token, $i));
	}
}

class	yMLCursor
{
	public function	__construct (&$tree, $start)
	{
		$this->length = 0;
		$this->match = null;
		$this->node =& $tree;
		$this->params = array ();
		$this->start = $start;
	}

	public function	move ($character, $index)
	{
		// Find and follow branch to next node
		if (!isset ($this->node) || $character === null)
			return false;
		else if (isset ($this->node['']))
		{
			if (!isset ($this->node[$character]))
				$branch =& $this->node[''];
			else if ($this->node[$character][1] != YML_BRANCH_INVALID)
				$branch =& $this->node[$character];
			else
				return false;
		}
		else
		{
			if (isset ($this->node[$character]))
				$branch =& $this->node[$character];
			else
				return false;
		}

		$this->node =& $branch[0];

		// Append character to parameters if requested
		if ($branch[2] !== null)
		{
			if (!isset ($this->params[$branch[2]]))
				$this->params[$branch[2]] = '';

			$this->params[$branch[2]] .= $character;
		}

		// Store matching information on terminal node
		if ($branch[3] !== null)
		{
			$this->length = $index - $this->start;
			$this->match =& $branch[3];
		}

		return true;
	}
}

?>
