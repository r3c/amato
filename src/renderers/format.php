<?php

namespace Umen;

defined ('UMEN') or die;

class	FormatRenderer extends Renderer
{
	/*
	** Initialize a new renderer.
	** $encoder:	encoder instance
	** $format:		render format definition
	*/
	public function	__construct ($encoder, $format)
	{
		$this->encoder = $encoder;
		$this->format = $format;
	}

	public function	render ($token)
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
			list ($delta, $name, $action, $flag, $captures) = $scope;

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

					// Jump over pending tags with lower precedence
					for ($last = count ($stack); $last > 0 && $level > $stack[$last - 1][0]; )
						--$last;

					// Action "alone": close all crossed tags
					if ($action === UMEN_ACTION_ALONE)
						$close = $last;

					// Action "start": call initializer and insert modifier
					else
					{
						$close = $last + 1;

						if (isset ($rule['onStart']))
							$rule['onStart'] ($name, $flag, $captures);

						array_splice ($stack, $last, 0, array (array
						(
							$level,
							$index,
							$name,
							$flag,
							$captures
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
					$broken[3] = $flag;
					$broken[4] = array_merge ($broken[4], $captures);

					// Close tags before current, included for "stop" action
					$close = $action === UMEN_ACTION_STEP ? $last + 1 : $last;

					break;

				default:
					continue 2;
			}

			// Close crossed modifiers
			for ($i = count ($stack) - 1; $i >= $close; --$i)
			{
				list ($level, $start, $name, $flag, $captures) = $stack[$i];

				if (isset ($this->format[$name]['onStop']))
				{
					$length = $index - $start;
					$result = $this->format[$name]['onStop'] ($name, $flag, $captures, substr ($plain, $start, $length));

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
					if (isset ($rule['onAlone']))
					{
						$result = $rule['onAlone'] ($name, $flag, $captures);

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
					list ($level, $start, $name, $flag) = $stack[$last];

					// Use "step" callback to replace tag body if available
					if (isset ($this->format[$name]['onStep']))
					{
						$length = $index - $start;
						$result = $this->format[$name]['onStep'] ($name, $flag, $stack[$last][4], substr ($plain, $start, $length));

						$plain = substr_replace ($plain, $result, $start, $length);
						$index = $start + strlen ($result);
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
