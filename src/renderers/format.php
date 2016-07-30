<?php

namespace Amato;

defined ('AMATO') or die;

class FormatRenderer extends Renderer
{
	/*
	** Initialize a new renderer.
	** $encoder:	encoder instance
	** $format:		render format definition
	** $escape:		optional plain text escape callback (string) -> string
	*/
	public function __construct ($encoder, $format, $escape = null)
	{
		$this->encoder = $encoder;
		$this->escape = $escape;
		$this->format = $format;
	}

	/*
	** Override for Renderer::render.
	*/
	public function render ($token)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($text, $scopes) = $pack;

		// Apply scopes on plain text
		$escape = $this->escape;
		$offset = 0;
		$stack = array ();

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Escape incoming text chunk using provided callback if any
			if ($escape !== null)
			{
				$chunk = $escape (mb_substr ($text, $offset, $delta));
				$text = mb_substr ($text, 0, $offset) . $chunk . mb_substr ($text, $offset + $delta);

				$offset += mb_strlen ($chunk) - $delta;
			}

			$offset += $delta;

			// Get formatting rule for current scope
			if (!isset ($this->format[$name]))
				continue;

			$rule = $this->format[$name];

			// Initialize action effect
			switch ($action)
			{
				case Tag::ALONE:
				case Tag::START:
					// Get precedence level for this modifier
					$level = isset ($rule['level']) ? (int)$rule['level'] : 1;

					// Jump over pending tags with lower precedence
					for ($index = count ($stack); $index > 0 && $level > $stack[$index - 1][0]; )
						--$index;

					break;

				case Tag::STEP:
				case Tag::STOP:
					// Search for matching tag in pending stack, cancel if none
					for ($index = count ($stack) - 1; $index >= 0 && $stack[$index][2] != $name; )
						--$index;

					if ($index < 0)
						continue 2;

					// Update tag flag and parameters
					$tag =& $stack[$index];
					$tag[3] = $flag;
					$tag[4] = array_merge ($tag[4], $captures);

					break;

				default:
					continue 2;
			}

			// Close and reset crossed scopes
			for ($i = count ($stack) - 1; $i >= $index; --$i)
			{
				$callback = $i === $index && $action === Tag::STEP ? 'onStep' : 'onStop';
				$cross = $stack[$i][2];

				if (isset ($this->format[$cross][$callback]))
				{
					$crossOffset = $stack[$i][1];
					$crossLength = $offset - $crossOffset;

					$result = $this->format[$cross][$callback] ($cross, $stack[$i][3], $stack[$i][4], mb_substr ($text, $crossOffset, $crossLength));
					$text = mb_substr ($text, 0, $crossOffset) . $result . mb_substr ($text, $crossOffset + $crossLength);

					$offset = $crossOffset + mb_strlen ($result);
				}
			}

			// Execute action effect
			switch ($action)
			{
				// Generate body and insert into text
				case Tag::ALONE:
					if (isset ($rule['onAlone']))
					{
						$result = $rule['onAlone'] ($name, $flag, $captures);
						$text = mb_substr ($text, 0, $offset) . $result . mb_substr ($text, $offset);

						$offset += mb_strlen ($result);
					}

					break;

				// Insert opened tag into stack
				case Tag::START:
					if (isset ($rule['onStart']))
						$rule['onStart'] ($name, $flag, $captures);

					array_splice ($stack, $index, 0, array (array
					(
						$level,
						0,
						$name,
						$flag,
						$captures
					)));

					break;

				// Remove closed tag from the stack
				case Tag::STOP:
					array_splice ($stack, $index, 1);

					break;
			}

			// Update crossed scopes start offsets
			for ($i = count ($stack) - 1; $i >= $index; --$i)
				$stack[$i][1] = $offset;
		}

		// Escape remaining text chunk using provided callback if any
		if ($escape !== null)
		{
			$chunk = $escape (mb_substr ($text, $offset));
			$text = mb_substr ($text, 0, $offset) . $chunk;
		}

		return $text;
	}
}

?>
