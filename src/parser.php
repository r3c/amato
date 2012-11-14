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
	** $limit:		default tag limit
	*/
	public function	__construct ($markup, $context, $escape, $limit = 100)
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

			$this->limits[$name] = isset ($rule['limit']) ? (int)$rule['limit'] : $limit;
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
				case UMEN_ACTION_BLOCK_START:
					++$stacks[$name];

					break;

				case UMEN_ACTION_BLOCK_STOP:
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
		// Parse original string using internal scanner
		$this->chains = array ();
		$this->literal = null;
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

	public function	resolve ($offset, $length, $match, $captures)
	{
		list ($name, $type, $flag) = $match;

		// Ensure tag limit has not be reached
		$usage = isset ($this->usages[$name]) ? $this->usages[$name] : 0;

		if ($usage >= $this->limits[$name])
			return false;

		$this->usages[$name] = $usage + 1;

		// Convert type to action given context rules
		if (!isset ($this->chains[$name]))
			$this->chains[$name] = array ();

		$action = $this->context[$type][count ($this->chains[$name]) > 0 ? 1 : 0];

		if ($action === null || ($this->literal !== null && ($action !== UMEN_ACTION_LITERAL || $name !== $this->literal)))
			return false;

		// Add current match to tags chain
		$chain =& $this->chains[$name];
		$chain[] = array ($offset, $length, $name, $action, $flag, $captures);
		$flush = count ($chain);

		// Set start of chain to be flushed
		switch ($action)
		{
			case UMEN_ACTION_ALONE:
				--$flush;

				break;

			case UMEN_ACTION_BLOCK_STEP:
				for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != UMEN_ACTION_BLOCK_START; )
					--$start;

				if ($start < 0)
					array_pop ($chain);

				break;

			case UMEN_ACTION_BLOCK_STOP:
				for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != UMEN_ACTION_BLOCK_START; )
					--$start;

				if ($start < 0)
					array_pop ($chain);
				else
					$flush = $start;

				break;

			case UMEN_ACTION_LITERAL:
				if ($this->literal === null)
					$this->literal = $name;
				else
					$this->literal = null;

				--$flush;

				break;
		}

		// Push entire chain to tags, sorted by start index
		for ($from = count ($chain) - 1; $from >= $flush; --$from)
		{
			for ($to = count ($this->tags); $to > 0 && $this->tags[$to - 1][0] > $chain[$from][0]; )
				--$to;

			array_splice ($this->tags, $to, 0, array_splice ($chain, $from, 1));
		}

		return true;
	}
}

?>
