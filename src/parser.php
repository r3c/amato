<?php

require_once (dirname (__FILE__) . '/encoder.php');
require_once (dirname (__FILE__) . '/scanner.php');

class	UmenParser
{
	/*
	** Initialize a new parser.
	** $markup:		markup language definition
	** $context:	context context
	** $escape:		escape character
	*/
	public function	__construct ($markup, $context, $escape)
	{
		$this->context = $context;
		$this->encoder = new UmenEncoder ();
		$this->limits = array ();
		$this->scanner = new UmenScanner ($escape);

		foreach ($markup as $name => $rule)
		{
			if (isset ($rule['tags']))
			{
				foreach ($rule['tags'] as $pattern => $options)
				{
					$match = array
					(
						(string)$name,
						$options[0],
						count ($options) > 1 ? (string)$options[1] : ''
					);

					$this->scanner->assign ($pattern, $match);
				}
			}

			$this->limits[$name] = isset ($rule['limit']) ? (int)$rule['limit'] : 100;
		}
	}

	/*
	** Convert tokenized string back to original format.
	** $token:	tokenized string
	** return:	original string
	*/
	public function	inverse ($token)
	{
		// Parse tokenized string
		$decoded = $this->encoder->decode ($token);

		if ($decoded === null)
			return null;

		list ($scopes, $plain) = $decoded;

		$offset = 0;
		$stacks = array ();

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Find valid decoded version of current tag using internal scanner
			if (!isset ($stacks[$name]))
				$stacks[$name] = 0;

			$open = count ($stacks[$name]) > 0 ? 1 : 0;

			foreach ($this->context as $type => $actions)
			{
				if ($actions[$open] === $action)
				{
					$tag = $this->scanner->decode (array ($name, $type, $flag), $captures);

					if ($tag !== null)
						break;
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
			if ($tag !== null)
			{
				$escape = $this->scanner->escape (substr ($plain, $offset, $delta));
				$plain = substr_replace ($plain, $escape . $tag, $offset, $delta);

				$offset += strlen ($escape) + strlen ($tag);
			}
		}

		// Escape remaining plain text
		if ($offset < strlen ($plain))
		{
			$escape = $this->scanner->escape (substr ($plain, $offset));
			$plain = substr_replace ($plain, $escape, $offset);
		}

		return $plain;
	}

	/*
	** Convert original string to tokenized format.
	** $string:	original string
	** return:	tokenized string
	*/
	public function	parse ($string)
	{
		$chains = array ();
		$context =& $this->context;
		$limits =& $this->limits;
		$literal = false;
		$tags = array ();
		$usages = array ();

		// Parse original string using internal scanner
		$plain = $this->scanner->scan ($string, function ($offset, $length, $match, $captures) use (&$chains, &$context, &$limits, &$literal, &$tags, &$usages)
		{
			list ($name, $type, $flag) = $match;

			// Ensure tag limit has not be reached
			$usage = isset ($usages[$name]) ? $usages[$name] : 0;

			if ($usage >= $limits[$name])
				return false;

			$usages[$name] = $usage + 1;

			// Convert type to action given context rules
			if (!isset ($chains[$name]))
				$chains[$name] = array ();

			$action = $context[$type][count ($chains[$name]) > 0 ? 1 : 0];

			if ($action === null || ($literal && $action !== UMEN_ACTION_LITERAL))
				return false;

			// Add current match to tags chain
			$chain =& $chains[$name];
			$chain[] = array ($offset, $length, $name, $action, $flag, $captures);
			$flush = count ($chain);

			// Set start of chain to be flushed
			switch ($action)
			{
				case UMEN_ACTION_ALONE:
					--$flush;

					break;

				case UMEN_ACTION_LITERAL:
					$literal = !$literal;

					--$flush;

					break;

				case UMEN_ACTION_STEP:
					for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != UMEN_ACTION_START; )
						--$start;

					if ($start < 0)
						array_pop ($chain);

					break;

				case UMEN_ACTION_STOP:
					for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != UMEN_ACTION_START; )
						--$start;

					if ($start < 0)
						array_pop ($chain);
					else
						$flush = $start;

					break;
			}

			// Push entire chain to tags, sorted by start index
			for ($from = count ($chain) - 1; $from >= $flush; --$from)
			{
				for ($to = count ($tags); $to > 0 && $tags[$to - 1][0] > $chain[$from][0]; )
					--$to;

				array_splice ($tags, $to, 0, array_splice ($chain, $from, 1));
			}

			return true;
		});

		// Remove resolved tags from plain string
		$offset = 0;
		$origin = 0;
		$scopes = array ();
		$shift = 0;

		foreach ($tags as $tag)
		{
			list ($offset, $length, $name, $action, $flag, $params) = $tag;

			$offset -= $shift;
			$shift += $length;

			$scopes[] = array ($offset - $origin, $name, $action, $flag, $params);

			$plain = substr_replace ($plain, '', $offset, $length);
			$origin = $offset;
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($scopes, $plain);
	}
}

?>
