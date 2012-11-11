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
		$lexer = new Lexer ($escape);

		foreach ($markup as $name => $rule)
		{
			if (isset ($rule['limit']))
				$limit = (int)$rule['limit'];
			else
				$limit = 100;

			if (isset ($rule['tags']))
			{
				foreach ($rule['tags'] as $pattern => $options)
				{
					$match = array
					(
						$name,
						$limit,
						$options[0],
						count ($options) > 1 ? $options[1] : null
					);

					$lexer->assign ($pattern, $match);
				}
			}
		}

		$this->context = $context;
		$this->encoder = new UmenEncoder ();
		$this->lexer = $lexer;
	}

	/*
	** Convert tokenized string back to original format.
	** $token:	tokenized string
	** return:	original string
	*/
	public function	inverse ($token)
	{
		global	$mapaConvert; // FIXME
return '';
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

	/*
	** Convert original string to tokenized format.
	** $string:	original string
	** return:	tokenized string
	*/
	public function	parse ($string)
	{
		$chains = array ();
		$context = $this->context;
		$literal = false;
		$tags = array ();
		$usages = array ();

		// Parse original string using internal lexer
		$plain = $this->lexer->scan ($string, function ($offset, $length, $match, $captures) use (&$chains, &$context, &$literal, &$tags, &$usages)
		{
			list ($name, $limit, $type, $flag) = $match;

			// Ensure tag limit has not be reached
			$usage = isset ($usages[$name]) ? $usages[$name] : 0;

			if ($usage >= $limit)
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
