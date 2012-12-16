<?php

namespace Umen;

defined ('UMEN') or die;

class	DefaultConverter extends Converter
{
	/*
	** Initialize a new default converter.
	** $encoder:	encoder instance
	** $scanner:	scanner instance
	** $markup:		markup language definition
	** $limit:		default tag limit
	*/
	public function	__construct ($encoder, $scanner, $markup, $limit = 100)
	{
		$this->callbacks = array ();
		$this->encoder = $encoder;
		$this->inverses = array ();
		$this->limits = array ();
		$this->scanner = $scanner;

		foreach ($markup as $name => $rule)
		{
			if (isset ($rule['onConvert']))
				$this->callbacks[$name . '+'] = $rule['onConvert'];

			if (isset ($rule['onInverse']))
				$this->callbacks[$name . '-'] = $rule['onInverse'];

			if (isset ($rule['tags']))
			{
				foreach ($rule['tags'] as $pattern => $options)
				{
					$actions = isset ($options['actions']) ? $options['actions'] : array ();
					$flag = isset ($options['flag']) ? (string)$options['flag'] : '';
					$switch = isset ($options['switch']) ? (string)$options['switch'] : null;

					$accept = $this->scanner->assign ($pattern, array ($name, $actions, $flag, $switch));

					foreach ($actions as $condition => $action)
					{
						$key = $name . ':' . $condition . ':' . $action . ':' . $flag;

						if (!isset ($this->inverses[$key]))
							$this->inverses[$key] = array ($accept, $switch);
					}
				}
			}

			$this->limits[$name] = isset ($rule['limit']) ? (int)$rule['limit'] : $limit;
		}
	}

	public function	convert ($string, $escape, $custom = null)
	{
		// Parse original string using internal scanner
		$chains = array ();
		$context = '';
		$custom = $custom;
		$tags = array ();
		$usages = array ();

		$string = $this->scanner->scan ($string, function ($offset, $length, $match, $captures) use (&$chains, &$context, &$custom, &$tags, &$usages)
		{
			list ($name, $actions, $flag, $switch) = $match;

			// Ensure tag limit has not be reached
			$usage = isset ($usages[$name]) ? $usages[$name] : 0;

			if ($usage >= $this->limits[$name])
				return false;

			$usages[$name] = $usage + 1;

			// Find action from current context condition
			if (!isset ($chains[$name]))
				$chains[$name] = array ();

			$condition = $context . (count ($chains[$name]) > 0 ? '+' : '-');
			$action = isset ($actions[$condition]) ? $actions[$condition] : null;

			if ($action === null || (isset ($this->callbacks[$name . '+']) && !$this->callbacks[$name . '+'] ($custom, $action, $flag, $captures)))
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
				case UMEN_ACTION_START:
					++$first;

					break;

				case UMEN_ACTION_STEP:
					for ($start = $first - 1; $start >= 0 && $chain[$start][3] != UMEN_ACTION_START; )
						--$start;

					if ($start < 0)
						return true;

					++$first;

					break;

				case UMEN_ACTION_STOP:
					for ($start = $first - 1; $start >= 0 && $chain[$start][3] != UMEN_ACTION_START; )
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
		$plain = '';
		$scopes = array ();

		foreach ($tags as $tag)
		{
			list ($offset, $length, $name, $action, $flag, $captures) = $tag;

			$chunk = $escape (substr ($string, $origin, $offset - $origin));
			$scopes[] = array (strlen ($chunk), $name, $action, $flag, $captures);
			$plain .= $chunk;

			$origin = $offset + $length;
		}

		$plain .= $escape (substr ($string, $origin));

		// Encode into tokenized string and return
		return $this->encoder->encode ($scopes, $plain);
	}

	public function	inverse ($token, $unescape, $custom = null)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($scopes, $plain) = $pack;

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
			if (isset ($this->callbacks[$name . '-']) && !$this->callbacks[$name . '-'] ($custom, $action, $flag, $captures))
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
				if (isset ($this->inverses[$key]))
				{
					list ($accept, $switch) = $this->inverses[$key];

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
				case UMEN_ACTION_START:
					++$stacks[$name];

					break;

				case UMEN_ACTION_STOP:
					--$stacks[$name];

					break;
			}

			// Escape skipped plain text and insert tag
			$chunk = $this->scanner->escape ($unescape (substr ($plain, $offset, $delta)), $sensible);
			$plain = substr_replace ($plain, $chunk . $tag, $offset, $delta);

			$offset += strlen ($chunk) + strlen ($tag);

			// Apply context switch if required
			if ($switch !== null)
				$context = $switch;
		}

		// Escape remaining plain text
		if ($offset < strlen ($plain))
		{
			$chunk = $this->scanner->escape ($unescape (substr ($plain, $offset)), $sensible);
			$plain = substr_replace ($plain, $chunk, $offset);
		}

		return $plain;
	}
}

?>
