<?php

require_once (dirname (__FILE__) . '/encoder.php');

class	UmenViewer
{
	/*
	** Initialize a new viewer.
	** $format:	render format definition
	*/
	public function	__construct ($format)
	{
		$this->encoder = new UmenEncoder ();
		$this->format = $format;
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

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $params) = $scope;

			$index += $delta;

			if (!isset ($this->format[$name]))
				continue;

			$rule = $this->format[$name];

			// Initialize action effect
			switch ($action)
			{
				case UMEN_ACTION_ALONE:
				case UMEN_ACTION_START:
					// Get precedence level for this modifier
					$level = isset ($rule['level']) ? (int)$rule['level'] : 1;

					// Browse pending tags with lower precedence
					for ($last = count ($stack); $last > 0 && $level > $stack[$last - 1][0]; )
						--$last;

					// Action "alone": close all crossed tags
					if ($action === UMEN_ACTION_ALONE)
						$close = $last;

					// Action "start": call initializer and insert modifier
					else
					{
						$close = $last + 1;

						if (isset ($rule['start']))
							$rule['start'] ($name, $flag, $params);

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

				case UMEN_ACTION_STEP:
				case UMEN_ACTION_STOP:
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
					if ($action === UMEN_ACTION_STEP)
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

				if (isset ($this->format[$name]['stop']))
				{
					$length = $index - $start;
					$result = $this->format[$name]['stop'] ($name, $flag, $params, substr ($plain, $start, $length));

					$plain = substr_replace ($plain, $result, $start, $length);
					$index = $start + strlen ($result);
				}
			}

			// Execute action effect
			switch ($action)
			{
				// Generate body and insert to string
				case UMEN_ACTION_ALONE:
					// Use "alone" callback to generate tag body if available
					if (isset ($rule['alone']))
					{
						$result = $rule['alone'] ($name, $flag, $params);

						$plain = substr_replace ($plain, $result, $index, 0);
						$index += strlen ($result);
					}

					break;

				// Remove closed tag from the stack
				case UMEN_ACTION_STOP:
					array_splice ($stack, $last, 1);

					break;

				// Call step function
				case UMEN_ACTION_STEP:
					list ($level, $start, $name, $flag, $params) = $stack[$last];

					// Use "step" callback to replace tag body if available
					if (isset ($this->format[$name]['step']))
					{
						$length = $index - $start;
						$result = $this->format[$name]['step'] ($name, $flag, $params, substr ($plain, $start, $length));

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
