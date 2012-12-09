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

					$index = $this->scanner->assign ($pattern, array ($name, $actions, $flag, $switch));

					foreach ($actions as $condition => $action)
					{
						$key = $name . ':' . $condition . ':' . $action . ':' . $flag;

						if (!isset ($this->inverses[$key]))
							$this->inverses[$key] = array ($index, $switch);
					}
				}
			}

			$this->limits[$name] = isset ($rule['limit']) ? (int)$rule['limit'] : $limit;
		}
	}

	public function	convert ($context, $string)
	{
		// Parse original string using internal scanner
		$this->chains = array ();
		$this->context = $context;
		$this->mode = '';
		$this->tags = array ();
		$this->usages = array ();

		$plain = $this->scanner->scan ($string, array ($this, 'resolve'));

		// Remove resolved tags from plain string
		$offset = 0;
		$origin = 0;
		$scopes = array ();
		$shift = 0;

		foreach ($this->tags as $tag)
		{
			list ($offset, $length, $name, $action, $flag, $captures) = $tag;

			$offset -= $shift;
			$shift += $length;

			$scopes[] = array ($offset - $origin, $name, $action, $flag, $captures);

			$plain = substr_replace ($plain, '', $offset, $length);
			$origin = $offset;
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($scopes, $plain);
	}

	public function	inverse ($context, $token)
	{
		// Parse tokenized string
		$decoded = $this->encoder->decode ($token);

		if ($decoded === null)
			return null;

		list ($scopes, $plain) = $decoded;

		$current = '';
		$offset = 0;
		$stacks = array ();

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Decode current tag
			if (isset ($this->callbacks[$name . '-']) && !$this->callbacks[$name . '-'] ($context, $action, $flag, $captures))
				$tag = '';
			else
			{
				// Find valid decoded version of current tag using internal scanner
				if (!isset ($stacks[$name]))
					$stacks[$name] = 0;

				$key = $name . ':' . $current . ($stacks[$name] > 0 ? '+' : '-') . ':' . $action . ':' . $flag;

				// Get decoded tag text if exists
				if (!isset ($this->inverses[$key]))
					$tag = '';
				else
				{
					list ($decode, $switch) = $this->inverses[$key];

					if ($switch !== null)
						$current = $switch;

					$tag = $this->scanner->decode ($decode, $captures);
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
			$escape = $this->scanner->escape (substr ($plain, $offset, $delta));
			$plain = substr_replace ($plain, $escape . $tag, $offset, $delta);

			$offset += strlen ($escape) + strlen ($tag);
		}

		// Escape remaining plain text
		if ($offset < strlen ($plain))
		{
			$escape = $this->scanner->escape (substr ($plain, $offset));
			$plain = substr_replace ($plain, $escape, $offset);
		}

		return $plain;
	}

	public function	resolve ($offset, $length, $match, $captures)
	{
		list ($name, $actions, $flag, $switch) = $match;

		// Ensure tag limit has not be reached
		$usage = isset ($this->usages[$name]) ? $this->usages[$name] : 0;

		if ($usage >= $this->limits[$name])
			return false;

		$this->usages[$name] = $usage + 1;

		// Find action from current mode condition
		if (!isset ($this->chains[$name]))
			$this->chains[$name] = array ();

		$condition = $this->mode . (count ($this->chains[$name]) > 0 ? '+' : '-');
		$action = isset ($actions[$condition]) ? $actions[$condition] : null;

		if ($action === null || (isset ($this->callbacks[$name . '+']) && !$this->callbacks[$name . '+'] ($this->context, $action, $flag, $captures)))
			return false;

		// Switch mode if requested
		if ($switch !== null)
			$this->mode = $switch;

		// Add current match to tags chain
		$chain =& $this->chains[$name];
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
			for ($to = count ($this->tags); $to > 0 && $this->tags[$to - 1][0] > $chain[$from][0]; )
				--$to;

			array_splice ($this->tags, $to, 0, array_splice ($chain, $from, 1));
		}

		return true;
	}
}

?>
