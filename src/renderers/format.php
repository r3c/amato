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
	public function render ($token, $state = null)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($render, $groups) = $pack;

		// Process all groups elements
		$cursors = Encoder::begin ($groups);
		$escape = $this->escape;
		$last = 0;
		$scopes = array ();
		$stop = 0;

		while (count ($cursors) > 0)
		{
			list ($id, $offset, $captures, $is_first, $is_last) = Encoder::next ($groups, $cursors);

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

			// Get formatting rule for current group
			if (!isset ($this->format[$id]))
				continue;

			// Create and insert new scope according to its precedence level
			if ($is_first)
			{
				$callback = isset ($this->format[$id][0]) ? $this->format[$id][0] : null;
				$level = isset ($this->format[$id][1]) ? $this->format[$id][1] : 1;

				for ($scope_end = count ($scopes); $scope_end > 0 && $level > $scopes[$scope_end - 1][3]; )
					--$scope_end;

				array_splice ($scopes, $scope_end, 0, array (array ($id, $stop, $callback, $level, $captures)));

				$scope_begin = $scope_end;

				if (!$is_last)
					++$scope_end;
			}

			// Find existing scope matching current group id, cancel if none
			else
			{
				for ($scope_end = count ($scopes) - 1; $scope_end >= 0 && $scopes[$scope_end][0] !== $id; )
					--$scope_end;

				if ($scope_end < 0)
					continue;

				$scopes[$scope_end][4] = $captures + $scopes[$scope_end][4];
				$scope_begin = $scope_end;
			}

			// Invoke callback for crossed scopes
			for ($i = count ($scopes) - 1; $i >= $scope_end; --$i)
			{
				list ($id, $start, $callback) = $scopes[$i];

				if ($callback === null)
					continue;

				$length = $stop - $start;
				$markup = $callback ($scopes[$i][4], mb_substr ($render, $start, $length), $i !== $scope_end || $is_last, $state);

				$render = mb_substr ($render, 0, $start) . $markup . mb_substr ($render, $start + $length);
				$stop += mb_strlen ($markup) - $length;
			}

			// Remove scope from stack when closed
			if ($is_last)
				array_splice ($scopes, $scope_end, 1);

			// Fast-forward offset of crossed scopes
			for ($i = count ($scopes) - 1; $i >= $scope_begin; --$i)
				$scopes[$i][1] = $stop;
		}

		// Escape trailing plain text using provided callback if any
		if ($escape !== null)
			$render = mb_substr ($render, 0, $stop) . $escape (mb_substr ($render, $stop));

		return $render;
	}
}

?>
