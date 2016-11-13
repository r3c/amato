<?php

namespace Amato;

defined ('AMATO') or die;

class FormatRenderer extends Renderer
{
	/*
	** Initialize a new renderer.
	** $encoder:	encoder instance
	** $formats:	render formats definitions
	** $escape:		optional plain text escape callback (string) -> string
	*/
	public function __construct ($encoder, $formats, $escape = null)
	{
		$this->encoder = $encoder;
		$this->escape = $escape;
		$this->formats = $formats;
	}

	/*
	** Override for Renderer::render.
	*/
	public function render ($token, $context = null)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($render, $markers) = $pack;

		// Process all markers
		$escape = $this->escape;
		$last = 0;
		$scopes = array ();
		$stop = 0;

		foreach ($markers as $marker)
		{
			list ($id, $offset, $is_first, $is_last, $params) = $marker;

			// Get start and stop offsets of plain text since last position
			$start = $stop;
			$stop += $offset - $last;
			$last = $offset;

			// Escape plain text using provided callback if any
			if ($escape !== null)
			{
				$length = $stop - $start;
				$plain = $escape (mb_substr ($render, $start, $length));

				$render = mb_substr ($render, 0, $start) . $plain . mb_substr ($render, $stop);
				$stop += mb_strlen ($plain) - $length;
			}

			// Get formatting rule for current marker if any
			if (!isset ($this->formats[$id]) || !isset ($this->formats[$id][0]))
				continue;

			// Create and insert new scope according to its precedence level
			if ($is_first)
			{
				$callback = $this->formats[$id][0];
				$level = isset ($this->formats[$id][1]) ? $this->formats[$id][1] : 1;

				for ($scope_shift = count ($scopes); $scope_shift > 0 && $level > $scopes[$scope_shift - 1][3]; )
					--$scope_shift;

				array_splice ($scopes, $scope_shift, 0, array (array ($id, $stop, $callback, $level, $params)));

				$scope_current = $scope_shift + ($is_last ? 0 : 1);
			}

			// Find existing scope matching current marker id, cancel if none
			else
			{
				for ($scope_shift = count ($scopes) - 1; $scope_shift >= 0 && $scopes[$scope_shift][0] !== $id; )
					--$scope_shift;

				if ($scope_shift < 0)
					continue;

				$scopes[$scope_shift][4] = $params + $scopes[$scope_shift][4];
				$scope_current = $scope_shift;
			}

			// Invoke callback of both crossed scopes and current one
			for ($i = count ($scopes) - 1; $i >= $scope_current; --$i)
			{
				list ($id, $start, $callback) = $scopes[$i];

				// Fast-forward offset of current one if just added and about to be closed
				if ($i === $scope_current && $is_first && $is_last)
					$start = $stop;

				$length = $stop - $start;
				$markup = $callback (mb_substr ($render, $start, $length), $scopes[$i][4], $i !== $scope_current || $is_last, $context);

				$render = mb_substr ($render, 0, $start) . $markup . mb_substr ($render, $stop);
				$stop += mb_strlen ($markup) - $length;
			}

			// Remove scope from stack when closed
			if ($is_last)
				array_splice ($scopes, $scope_current, 1);

			// Shift offset of both crossed scopes and current one
			for ($i = count ($scopes) - 1; $i >= $scope_shift; --$i)
				$scopes[$i][1] = $stop;
		}

		// Escape remaining plain text using provided callback if any
		if ($escape !== null)
			$render = mb_substr ($render, 0, $stop) . $escape (mb_substr ($render, $stop));

		return $render;
	}
}

?>
