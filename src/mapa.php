<?php

/*
** Internal constants.
*/
define ('MAPA_ACTION_SINGLE',	0);
define ('MAPA_ACTION_START',	1);
define ('MAPA_ACTION_STEP',		2);
define ('MAPA_ACTION_STOP',		3);

define ('MAPA_BRANCH_INVALID',	0);
define ('MAPA_BRANCH_SHARED',	1);
define ('MAPA_BRANCH_UNIQUE',	2);

define ('MAPA_DECODE_PARAM',	0);
define ('MAPA_DECODE_PLAIN',	1);

define ('MAPA_PATTERN_BEGIN',	'(');
define ('MAPA_PATTERN_END',		')');
define ('MAPA_PATTERN_ESCAPE',	'\\');
define ('MAPA_PATTERN_LOOP',	'*');

define ('MAPA_TOKEN_ESCAPE',	'\\');
define ('MAPA_TOKEN_PARAM',		',');
define ('MAPA_TOKEN_PLAIN',		'|');
define ('MAPA_TOKEN_SCOPE',		';');
define ('MAPA_TOKEN_VALUE',		'=');

define ('MAPA_TYPE_BEGIN',		0);
define ('MAPA_TYPE_BETWEEN',	1);
define ('MAPA_TYPE_END',		2);
define ('MAPA_TYPE_RESUME',		3);
define ('MAPA_TYPE_SINGLE',		4);
define ('MAPA_TYPE_SWITCH',		5);

define ('MAPA_VERSION',			1);

$mapaConvert = array
(
	MAPA_TYPE_BEGIN		=> array (MAPA_ACTION_START, MAPA_ACTION_START),
	MAPA_TYPE_BETWEEN	=> array (null, MAPA_ACTION_STEP),
	MAPA_TYPE_END		=> array (null, MAPA_ACTION_STOP),
	MAPA_TYPE_RESUME	=> array (MAPA_ACTION_START, MAPA_ACTION_STEP),
	MAPA_TYPE_SINGLE	=> array (MAPA_ACTION_SINGLE, MAPA_ACTION_SINGLE),
	MAPA_TYPE_SWITCH	=> array (MAPA_ACTION_START, MAPA_ACTION_STOP)
);

class	MaPa
{
	/*
	** Constant encoding/decoding hashes.
	*/
	private static	$actionsDecode = array ('/' => MAPA_ACTION_SINGLE, '<' => MAPA_ACTION_START, '!' => MAPA_ACTION_STEP, '>' => MAPA_ACTION_STOP);
	private static	$actionsEncode = array (MAPA_ACTION_SINGLE => '/', MAPA_ACTION_START => '<', MAPA_ACTION_STEP => '!', MAPA_ACTION_STOP => '>');
	private static	$escapesDecode = array (MAPA_TOKEN_PARAM => true, MAPA_TOKEN_PLAIN => true, MAPA_TOKEN_SCOPE => true, MAPA_TOKEN_VALUE => true);
	private static	$escapesEncode = array (MAPA_TOKEN_ESCAPE => true, MAPA_TOKEN_PARAM => true, MAPA_TOKEN_PLAIN => true, MAPA_TOKEN_SCOPE => true, MAPA_TOKEN_VALUE => true);

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
				if ($j + 2 < $length && $expression[$j + 1] === '-')
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
						case MAPA_PATTERN_BEGIN:
							// Parse class name
							for ($j = ++$i; $i < $length && $pattern[$i] !== MAPA_PATTERN_LOOP && $pattern[$i] !== MAPA_PATTERN_END; )
								++$i;

							$class = substr ($pattern, $j, $i - $j);

							if (!isset ($specials[$class]))
								throw new Exception ('undefined or invalid character class "' . $class . '"');

							// Parse loop if specified
							switch ($pattern[$i])
							{
								case MAPA_PATTERN_LOOP:
									$branch = array (&$node, MAPA_BRANCH_SHARED, $count, null);

									++$i;

									break;

								default:
									$branch = array (null, MAPA_BRANCH_SHARED, $count, null);

									break;
							}

							// Define target branch depending on special mode
							if (!$specials[$class][0])
							{
								$target = array (null, MAPA_BRANCH_INVALID, null, null);

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

							$decode[] = array (MAPA_DECODE_PARAM, $count++);

							if ($branch[1] === MAPA_BRANCH_UNIQUE)
								$node =& $branch[0];

							break;

						default:
							if ($pattern[$i] === MAPA_PATTERN_ESCAPE && $i + 1 < $length)
								++$i;

							$character = $pattern[$i];
							$decode[] = array (MAPA_DECODE_PLAIN, $character);

							if (!isset ($node[$character]))
								$node[$character] = array (null, MAPA_BRANCH_UNIQUE, null, null);

							$branch =& $node[$character];

							if ($branch[1] === MAPA_BRANCH_SHARED)
								throw new Exception ('ambiguous character "' . $character . '" at position #' . $i . ' for pattern "' . $pattern . '" in rule "' . $name . '"');

							$branch[1] = MAPA_BRANCH_UNIQUE;
							$node =& $branch[0];

							break;
					}
				}

				// Register terminal node
				if (isset ($branch))
				{
					if ($branch[3] !== null)
						throw new Exception ('conflict for pattern "' . $pattern . '" in rule "' . $name . '"');

					$branch[3] = array ($name, $type, $value, isset ($rule['literal']) && $rule['literal']);
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
		global	$mapaConvert;

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

			foreach ($mapaConvert as $type => $actions)
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

			if ($decode === null)
				continue;

			// Generate decoded tag string from decoder
			$tag = '';

			foreach ($decode as $item)
			{
				switch ($item[0])
				{
					case MAPA_DECODE_PARAM:
						$tag .= $item[1] < $count ? $params[$item[1]] : '';

						break;

					case MAPA_DECODE_PLAIN:
						$tag .= $item[1];

						break;
				}
			}

			$clean = substr_replace ($clean, $tag, $index, 0);
			$index += strlen ($tag);

			// Update opened tags counter
			switch ($action)
			{
				case MAPA_ACTION_START:
					++$opens[$name];

					break;

				case MAPA_ACTION_STOP:
					--$opens[$name];

					break;
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
		global	$mapaConvert; // FIXME

		$token = MAPA_VERSION;
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

				array_push ($cursors, new MaPaCursor ($tree, $i));

				for ($current = count ($cursors) - 1; $current >= 0; --$current)
				{
					$cursor = $cursors[$current];

					// Nothing to do until cursor can't be moved to next node
					if ($cursor->move ($character, $i + 1))
						continue;

					// Process this cursor's last matched tag, if any
					if (isset ($cursor->match))
					{
						// FIXME: don't remove tags as soon as they're resolved
						// but wait for the entire string to be parsed. This
						// should allow unresolved tags of a given name to be
						// kept in their own array for faster searches. A new
						// "level" property can also be used to prevent tags
						// from being nested within higher-level ones.

						// Browse stack for compatible unprocessed tags
						$links = array ();
						$name = $cursor->match[0];

						for ($link = count ($tags) - 1; $link >= $closed; --$link)
						{
							if ($tags[$link][0])
							{
								$other = $tags[$link][1];

								if ($other->match[0] === $name)
									$links[] = $link;
								else if ($other->match[3]) // FIXME: hack for literal tags
								{
									$links = null;

									break;
								}
							}
						}

						// Deduce action from tag type and links
						$action = $links !== null ? $mapaConvert[$cursor->match[1]][count ($links) > 0 ? 1 : 0] : null;

						if ($action !== null)
						{
							// Remove all cursors before current one
							array_splice ($cursors, 0, $current);

							$current = 0;

							// Remove all cursors after this one having a match
							// that overlaps with current one's
							for ($after = count ($cursors) - 1; $after > 0; --$after)
							{
								if ($cursors[$after]->start < $cursor->start + $cursor->length)
									array_splice ($cursors, $after, 1);
							}

							// Add current unresolved cursor and action to tags
							$tags[] = array (true, $cursor, $action);

							if ($action === MAPA_ACTION_SINGLE || $action === MAPA_ACTION_STOP)
							{
								array_unshift ($links, count ($tags) - 1);

								// Remove tags and flag them as processed
								foreach ($links as $link)
								{
									$cursor = $tags[$link][1];

									// Shift cursors after current one
									for ($after = count ($cursors) - 1; $after > 0; --$after)
										$cursors[$after]->start -= $cursor->length;

									// Shift tags after current one
									for ($after = count ($tags) - 1; $after > $link; --$after)
										$tags[$after][1]->start -= $cursor->length;

									// Remove tag from string
									$length -= $cursor->length;
									$plain = substr_replace ($plain, '', $cursor->start, $cursor->length);
									$i -= $cursor->length;

									// Flag as resolved
									$tags[$link][0] = false;

									// Stop on tag start (FIXME: hack to handle [u][u]sth[/u][/u])
									if ($tags[$link][2] == MAPA_ACTION_SINGLE || $tags[$link][2] == MAPA_ACTION_START)
										break;
								}

								// Close resolved tags for faster processing
								while ($closed < count ($tags) && !$tags[$closed][0])
									++$closed;
							}
						}
					}

					// Remove cursor once used
					array_splice ($cursors, $current, 1);
				}
			}

			// Tokenize processed tags into scopes
			$shift = 0;

			foreach ($tags as $tag)
			{
				list ($pending, $cursor, $action) = $tag;

				if ($pending)
					continue;

				// Write delta offset and action
				$token .= MAPA_TOKEN_SCOPE . ($cursor->start - $shift) . self::$actionsEncode[$action];
				$shift = $cursor->start;

				// Write tag name
				foreach (str_split ($cursor->match[0]) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= MAPA_TOKEN_ESCAPE;

					$token .= $character;
				}

				// Write tag value
				if ($cursor->match[2])
				{
					$token .= MAPA_TOKEN_VALUE;

					foreach (str_split ($cursor->match[2]) as $character)
					{
						if (isset (self::$escapesEncode[$character]))
							$token .= MAPA_TOKEN_ESCAPE;

						$token .= $character;
					}
				}

				// Write tag parameters
				foreach ($cursor->params as $param)
				{
					$token .= MAPA_TOKEN_PARAM;

					foreach (str_split ($param) as $character)
					{
						if (isset (self::$escapesEncode[$character]))
							$token .= MAPA_TOKEN_ESCAPE;

						$token .= $character;
					}
				}
			}
		}

		return $token . MAPA_TOKEN_PLAIN . $plain;
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
profile ('r');
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
				case MAPA_ACTION_SINGLE:
				case MAPA_ACTION_START:
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
					for ($last = count ($stack); $last > 0 && $level > $stack[$last - 1][0]; )
						--$last;

					// Action "single": close all crossed tags
					if ($action === MAPA_ACTION_SINGLE)
						$close = $last;

					// Action "start": call initializer and insert modifier
					else
					{
						$close = $last + 1;

						if (isset ($modifier['start']))
							$modifier['start'] ($name, $value, $params);

						array_splice ($stack, $last, 0, array (array
						(
							$level,
							$index,
							$name,
							$value,
							$params
						)));
					}

					break;

				case MAPA_ACTION_STEP:
				case MAPA_ACTION_STOP:
					// Search for matching tag in pending stack, cancel if none
					for ($last = count ($stack) - 1; $last >= 0 && $stack[$last][2] != $name; )
						--$last;

					if ($last < 0)
						continue 2;

					// Update tag value and parameters
					$broken =& $stack[$last];

					foreach ($params as $key => $value) // FIXME: hack to save params modifications
						$broken[4][$key] = $value;

					$broken[3] = $value;

					// Action "step": close all tags before this one, excluded
					if ($action === MAPA_ACTION_STEP)
						$close = $last + 1;

					// Action "stop": close all tags before this one, included
					else
						$close = $last;

					break;

				default:
					continue 2;
			}

			// Close crossed modifiers
			for ($i = count ($stack) - 1; $i >= $close; --$i)
			{
				list ($level, $start, $name, $value, $params) = $stack[$i];

				if (isset ($modifiers[$name]['stop']))
				{
					$length = $index - $start;
					$result = $modifiers[$name]['stop'] ($name, $value, $params, substr ($clean, $start, $length));

					$clean = substr_replace ($clean, $result, $start, $length);
					$index = $start + strlen ($result);
				}
			}

			// Execute action effect
			switch ($action)
			{
				// Generate body and insert to string
				case MAPA_ACTION_SINGLE:
					// Use "single" callback to generate tag body if available
					if (isset ($modifier['single']))
					{
						$result = $modifier['single'] ($name, $value, $params);

						$clean = substr_replace ($clean, $result, $index, 0);
						$index += strlen ($result);
					}

					break;

				// Remove closed tag from the stack
				case MAPA_ACTION_STOP:
					array_splice ($stack, $last, 1);

					break;

				// Call step function
				case MAPA_ACTION_STEP:
					list ($level, $start, $name, $value, $params) = $stack[$last];

					// Use "step" callback to replace tag body if available
					if (isset ($modifiers[$name]['step']))
					{
						$length = $index - $start;
						$result = $modifiers[$name]['step'] ($name, $value, $params, substr ($clean, $start, $length));

						$clean = substr_replace ($clean, $result, $start, $length);
						$index = $start + strlen ($result);

						$stack[$last][4] = $params; // FIXME: hack to save params modifications
					}

					break;
			}

			// Update modifiers indices
			for ($i = count ($stack) - 1; $i >= $last; --$i)
				$stack[$i][1] = $index;
		}
profile ('r');
		return $clean;
	}

	/*
	** Parse tokenized string into (scopes, text) array.
	** $token:	tokenized string
	** return:	(scopes, text) array, null on parsing error.
	*/
	private static function	parse ($token)
	{
profile ('p');
		$length = strlen ($token);
		$scopes = array ();

		// Parse version
		for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
			++$i;

		$version = (int)substr ($token, 0, $i);

		if ($version !== MAPA_VERSION)
			return null;

		// Parse header
		while ($i < $length && $token[$i] === MAPA_TOKEN_SCOPE)
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
			if ($i < $length && isset (self::$actionsDecode[$token[$i]]))
				$action = self::$actionsDecode[$token[$i++]];
			else
				continue;

			// Parse name
			$name = '';

			for ($i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
			{
				if ($token[$i] === MAPA_TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$name .= $token[$i];
			}

			// Parse value
			$value = '';

			if ($i < $length && $token[$i] === MAPA_TOKEN_VALUE)
			{
				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === MAPA_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$value .= $token[$i];
				}
			}

			// Parse params
			for ($params = array (); $i < $length && $token[$i] === MAPA_TOKEN_PARAM; )
			{
				$param = '';

				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === MAPA_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$param .= $token[$i];
				}

				$params[] = $param;
			}

			$scopes[] = array ($delta, $name, $action, $value, $params);
		}

		if ($i >= $length || $token[$i++] !== MAPA_TOKEN_PLAIN)
			return null;
profile ('p');
		return array ($scopes, substr ($token, $i));
	}
}

class	MaPaCursor
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
		// Find and follow branch to next node if possible
		if (!isset ($this->node) || $character === null)
			return false;
		else if (isset ($this->node['']))
		{
			if (!isset ($this->node[$character]))
				$branch =& $this->node[''];
			else if ($this->node[$character][1] !== MAPA_BRANCH_INVALID)
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
