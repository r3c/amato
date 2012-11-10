<?php

require_once (dirname (__FILE__) . '/encoder.php');

class	Viewer
{
	/*
	** Initialize a new viewer.
	** $formats:	rendering formats
	*/
	public function	__construct ($formats)
	{
		$this->encoder = new Encoder ();
		$this->formats = $formats;
	}

	/*
	** Render tokenized string.
	** $token:		tokenized string
	** return:		rendered string
	*/
	public function	view ($token)
	{
		// Parse tokenized string
		$decoded = $this->encoder->decode ($token);

		if ($decoded === null)
			return null;

		list ($scopes, $plain) = $decoded;

		// Apply scopes on plain text
		$index = 0;
		$stack = array ();
		$uses = array ();

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $params) = $scope;

			$index += $delta;

			if (!isset ($this->formats[$name]))
				continue;

			$format = $this->formats[$name];

			// Initialize action effect
			switch ($action)
			{
				case ENCODER_ACTION_ALONE:
				case ENCODER_ACTION_START:
					// Get precedence level for this modifier
					if (isset ($format['level']))
						$level = $format['level'];
					else
						$level = 1;

					// Check usage limit for this modifier
					if (isset ($format['limit']))
					{
						if (!isset ($uses[$name]))
							$uses[$name] = 0;

						if ($uses[$name] >= $format['limit'])
							continue 2;

						++$uses[$name];
					}

					// Browse pending tags with lower precedence
					for ($last = count ($stack); $last > 0 && $level > $stack[$last - 1][0]; )
						--$last;

					// Action "alone": close all crossed tags
					if ($action === ENCODER_ACTION_ALONE)
						$close = $last;

					// Action "start": call initializer and insert modifier
					else
					{
						$close = $last + 1;

						if (isset ($format['start']))
							$format['start'] ($name, $flag, $params);

						array_splice ($stack, $last, 0, array (array
						(
							$level,
							$index,
							$name,
							$flag,
							$params
						)));
					}

					break;

				case ENCODER_ACTION_STEP:
				case ENCODER_ACTION_STOP:
					// Search for matching tag in pending stack, cancel if none
					for ($last = count ($stack) - 1; $last >= 0 && $stack[$last][2] != $name; )
						--$last;

					if ($last < 0)
						continue 2;

					// Update tag flag and parameters
					$broken =& $stack[$last];

					foreach ($params as $paramKey => $paramValue) // FIXME: hack to save params modifications
						$broken[4][$paramKey] = $paramValue;

					$broken[3] = $flag;

					// Action "step": close all tags before this one, excluded
					if ($action === ENCODER_ACTION_STEP)
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
				list ($level, $start, $name, $flag, $params) = $stack[$i];

				if (isset ($this->formats[$name]['stop']))
				{
					$length = $index - $start;
					$result = $this->formats[$name]['stop'] ($name, $flag, $params, substr ($plain, $start, $length));

					$plain = substr_replace ($plain, $result, $start, $length);
					$index = $start + strlen ($result);
				}
			}

			// Execute action effect
			switch ($action)
			{
				// Generate body and insert to string
				case ENCODER_ACTION_ALONE:
					// Use "alone" callback to generate tag body if available
					if (isset ($format['alone']))
					{
						$result = $format['alone'] ($name, $flag, $params);

						$plain = substr_replace ($plain, $result, $index, 0);
						$index += strlen ($result);
					}

					break;

				// Remove closed tag from the stack
				case ENCODER_ACTION_STOP:
					array_splice ($stack, $last, 1);

					break;

				// Call step function
				case ENCODER_ACTION_STEP:
					list ($level, $start, $name, $flag, $params) = $stack[$last];

					// Use "step" callback to replace tag body if available
					if (isset ($this->formats[$name]['step']))
					{
						$length = $index - $start;
						$result = $this->formats[$name]['step'] ($name, $flag, $params, substr ($plain, $start, $length));

						$plain = substr_replace ($plain, $result, $start, $length);
						$index = $start + strlen ($result);

						$stack[$last][4] = $params; // FIXME: hack to save params modifications
					}

					break;
			}

			// Update modifiers indices
			for ($i = count ($stack) - 1; $i >= $last; --$i)
				$stack[$i][1] = $index;
		}

		return $plain;
	}
}

?>
