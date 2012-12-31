<?php

namespace Umen;

defined ('UMEN') or die;

class	MarkupConverter extends Converter
{
	/*
	** Initialize a new default converter.
	** $encoder:	encoder instance
	** $scanner:	scanner instance
	** $markup:		markup language definitions
	** $limit:		optional default tag limit, 0 for no limit
	*/
	public function	__construct ($encoder, $scanner, $markup, $limit = 0)
	{
		$this->callbacks = array ();
		$this->encoder = $encoder;
		$this->limits = array ();
		$this->reverts = array ();
		$this->scanner = $scanner;

		foreach ($markup as $name => $definition)
		{
			if (isset ($definition['onConvert']))
				$this->callbacks[$name . '+'] = $definition['onConvert'];

			if (isset ($definition['onRevert']))
				$this->callbacks[$name . '-'] = $definition['onRevert'];

			if (isset ($definition['tags']))
			{
				foreach ($definition['tags'] as $pattern => $options)
				{
					$actions = isset ($options['actions']) ? $options['actions'] : array ();
					$flag = isset ($options['flag']) ? (string)$options['flag'] : '';
					$switch = isset ($options['switch']) ? (string)$options['switch'] : null;

					$accept = $this->scanner->assign ($pattern, array ($name, $actions, $flag, $switch));

					foreach ($actions as $condition => $action)
					{
						$key = $name . ':' . $condition . ':' . $action . ':' . $flag;

						if (!isset ($this->reverts[$key]))
							$this->reverts[$key] = array ($accept, $switch);
					}
				}
			}

			$this->limits[$name] = isset ($definition['limit']) ? (int)$definition['limit'] : $limit;
		}
	}

	/*
	** Override for Converter::convert.
	*/
	public function	convert ($text, $custom = null)
	{
		// Parse original string using internal scanner
		$callbacks =& $this->callbacks;
		$chains = array ();
		$context = '';
		$limits =& $this->limits;
		$tags = array ();
		$usages = array ();

		$text = $this->scanner->scan ($text, function ($offset, $length, $match, $captures) use (&$callbacks, &$chains, &$context, &$custom, &$limits, &$tags, &$usages)
		{
			list ($name, $actions, $flag, $switch) = $match;

			// Ensure tag limit has not be reached
			$usage = isset ($usages[$name]) ? $usages[$name] : 0;

			if ($limits[$name] > 0 && $usage >= $limits[$name])
				return false;

			$usages[$name] = $usage + 1;

			// Find action from current context condition
			if (!isset ($chains[$name]))
				$chains[$name] = array ();

			$condition = $context . (count ($chains[$name]) > 0 ? '+' : '-');
			$action = isset ($actions[$condition]) ? $actions[$condition] : null;

			if ($action === null || (isset ($callbacks[$name . '+']) && call_user_func ($callbacks[$name . '+'], $action, $flag, $captures, $custom) === false))
				return false;

			// Switch context if requested
			if ($switch !== null)
				$context = $switch;

			// Add current match to tags chain
			$chain =& $chains[$name];
			$first = count ($chain);

			// Set start of chain to be flushed
			switch ($action)
			{
				case Action::START:
					++$first;

					break;

				case Action::STEP:
					for ($start = $first - 1; $start >= 0 && $chain[$start][3] != Action::START; )
						--$start;

					if ($start < 0)
						return true;

					++$first;

					break;

				case Action::STOP:
					for ($start = $first - 1; $start >= 0 && $chain[$start][3] != Action::START; )
						--$start;

					if ($start < 0)
						return true;

					$first = $start;

					break;
			}

			// Push entire chain to tags, sorted by start index
			$chain[] = array ($offset, $length, $name, $action, $flag, $captures);

			for ($from = count ($chain) - 1; $from >= $first; --$from)
			{
				for ($to = count ($tags); $to > 0 && $tags[$to - 1][0] > $chain[$from][0]; )
					--$to;

				array_splice ($tags, $to, 0, array_splice ($chain, $from, 1));
			}

			return true;
		});

		// Remove resolved tags from string
		$origin = 0;
		$scopes = array ();
		$shift = 0;

		foreach ($tags as $tag)
		{
			list ($offset, $length, $name, $action, $flag, $captures) = $tag;

			$scopes[] = array ($offset - $origin, $name, $action, $flag, $captures);
			$text = substr_replace ($text, '', $offset - $shift, $length);

			$origin = $offset + $length;
			$shift += $length;
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($scopes, $text);
	}

	/*
	** Override for Converter::revert.
	*/
	public function	revert ($token, $custom = null)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($scopes, $text) = $pack;

		$context = '';
		$offset = 0;
		$stacks = array ();

		$sensible = function ($match) use (&$context, &$stacks)
		{
			list ($name, $actions) = $match;

			$condition = $context . (isset ($stacks[$name]) && count ($stacks[$name]) > 0 ? '+' : '-');

			return isset ($actions[$condition]);
		};

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Decode current tag
			if (isset ($this->callbacks[$name . '-']) && call_user_func ($this->callbacks[$name . '-'], $action, $flag, $captures, $custom) === false)
			{
				$switch = null;
				$tag = '';
			}
			else
			{
				// Find valid decoded version of current tag using internal scanner
				if (!isset ($stacks[$name]))
					$stacks[$name] = 0;

				$key = $name . ':' . $context . ($stacks[$name] > 0 ? '+' : '-') . ':' . $action . ':' . $flag;

				// Get decoded tag text if exists
				if (isset ($this->reverts[$key]))
				{
					list ($accept, $switch) = $this->reverts[$key];

					$tag = $this->scanner->make ($accept, $captures);
				}
				else
				{
					$switch = null;
					$tag = '';
				}
			}

			// Update opened tags counter
			switch ($action)
			{
				case Action::START:
					++$stacks[$name];

					break;

				case Action::STOP:
					--$stacks[$name];

					break;
			}

			// Escape skipped plain text and insert tag
			$chunk = $this->scanner->escape (substr ($text, $offset, $delta), $sensible);
			$text = substr_replace ($text, $chunk . $tag, $offset, $delta);

			$offset += strlen ($chunk) + strlen ($tag);

			// Apply context switch if required
			if ($switch !== null)
				$context = $switch;
		}

		// Escape remaining plain text
		$chunk = $this->scanner->escape (substr ($text, $offset), $sensible);
		$text = substr_replace ($text, $chunk, $offset);

		return $text;
	}
}

?>
