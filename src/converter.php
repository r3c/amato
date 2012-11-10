<?php

require_once (dirname (__FILE__) . '/encoder.php');
require_once (dirname (__FILE__) . '/scanner.php');

class	Converter
{
	/*
	** Initialize a new encoder.
	** $rules:		tag parsing rules
	** $actions:	actions conditions
	*/
	public function	__construct ($rules, $actions)
	{
		$lexer = new Lexer ();

		foreach ($rules as $name => $rule)
		{
			$limit = isset ($rule['limit']) ? (int)$rule['limit'] : null;

			foreach ($rule['tags'] as $pattern => $behavior)
			{
				$match = array
				(
					$name,
					$limit,
					$behavior[0],
					count ($behavior) > 1 ? $behavior[1] : null
				);

				$lexer->assign ($pattern, $match);
			}
		}

		$this->actions = $actions;
		$this->encoder = new Encoder ();
		$this->lexer = $lexer;
	}

	/*
	** Convert plain string to tokenized format.
	** $plain:	plain string
	** return:	tokenized string
	*/
	public function	convert ($plain)
	{
		// Parse plain string using internal lexer
		$actions = $this->actions;
		$chains = array ();
		$literal = false;
		$tags = array ();
		$usages = array ();

		$this->lexer->scan ($plain, function ($offset, $length, $match, $captures) use (&$actions, &$chains, &$literal, &$tags, &$usages)
		{
			list ($name, $limit, $type, $flag) = $match;

			// Ensure tag limit has not be reached
			if ($limit !== null)
			{
				$usage = isset ($usages[$name]) ? $usages[$name] : 0;

				if ($usage >= $limit)
					return false;

				$usages[$name] = $usage + 1;
			}

			// Convert type to action given tag name
			if (!isset ($chains[$name]))
				$chains[$name] = array ();

			$action = $actions[$type][count ($chains[$name]) > 0 ? 1 : 0];

			if ($action === null || ($literal && $action !== ENCODER_ACTION_LITERAL))
				return false;

			// Add current match to tags chain
			$chain =& $chains[$name];
			$chain[] = array ($offset, $length, $name, $action, $flag, $captures);
			$flush = count ($chain);

			// Set start of chain to be flushed
			switch ($action)
			{
				case ENCODER_ACTION_LITERAL:
					$literal = !$literal;

					--$flush;

					break;

				case ENCODER_ACTION_SINGLE:
					--$flush;

					break;

				case ENCODER_ACTION_STEP:
					for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != ENCODER_ACTION_START; )
						--$start;

					if ($start < 0)
						array_pop ($chain);

					break;

				case ENCODER_ACTION_STOP:
					for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != ENCODER_ACTION_START; )
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

	/*
	** Convert tokenized string back to plain format.
	** $token:	tokenized string
	** return:	plain string
	*/
	public function	reverse ($token)
	{
		global	$mapaConvert; // FIXME

		throw new Exception ('fuck.');

		$parsed = self::parse ($token);

		if ($parsed === null)
			return null;

		list ($scopes, $clean) = $parsed;

		$decodes =& $codes[1];
		$index = 0;

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $params) = $scope;

			$count = count ($params);
			$index += $delta;

			// Try to find decoder by reverting action to type
			if (!isset ($opens[$name]))
				$opens[$name] = 0;

			$decode = null;
			$open = count ($opens[$name]) > 0 ? 1 : 0;

			foreach ($mapaConvert as $type => $actions)
			{
				if ($actions[$open] === $action)
				{
					$key = $name . '.' . $type . '.' . $count . '.' . $flag;

					if (isset ($decodes[$key]))
					{
						$decode = $decodes[$key];

						break;
					}
				}
			}

			if ($decode === null)
				continue;

			// Generate decoded tag string from decoder
			$tag = '';

			foreach ($decode as $item)
			{
				switch ($item[0])
				{
					case MAPA_DECODE_PARAM:
						$tag .= $item[1] < $count ? $params[$item[1]] : '';

						break;

					case MAPA_DECODE_PLAIN:
						$tag .= $item[1];

						break;
				}
			}

			$clean = substr_replace ($clean, $tag, $index, 0);
			$index += strlen ($tag);

			// Update opened tags counter
			switch ($action)
			{
				case MAPA_ACTION_START:
					++$opens[$name];

					break;

				case MAPA_ACTION_STOP:
					--$opens[$name];

					break;
			}
		}

		return $clean;
	}
}

?>
