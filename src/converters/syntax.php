<?php

namespace Umen;

defined ('UMEN') or die;

class SyntaxConverter extends Converter
{
	/*
	** Initialize a new default converter.
	** $encoder:	encoder instance
	** $scanner:	scanner instance
	** $syntax:		syntax language definitions
	** $limit:		optional default tag limit, 0 for no limit
	*/
	public function __construct ($encoder, $scanner, $syntax, $limit = 0)
	{
		$this->encoder = $encoder;
		$this->limits = array ();
		$this->onConverts = array ();
		$this->onReverts = array ();
		$this->resolvers = array ();
		$this->scanner = $scanner;

		foreach ($syntax as $name => $definition)
		{
			if (isset ($definition['onConvert']))
				$this->onConverts[$name] = $definition['onConvert'];

			if (isset ($definition['onRevert']))
				$this->onReverts[$name] = $definition['onRevert'];

			if (isset ($definition['tags']))
			{
				foreach ($definition['tags'] as $pattern => $instructions)
				{
					$meanings = array ();

					foreach ($instructions as $expression => $instruction)
					{
						$meanings[] = array
						(
							self::parseCondition ($expression),
							isset ($instruction[0]) ? $instruction[0] : Action::ALONE,
							isset ($instruction[1]) ? $instruction[1] : '',
							isset ($instruction[2]) ? self::parseSwitch ($instruction[2]) : array ()
						);
					}

					$accept = $this->scanner->assign ($pattern, array ($name, $meanings));

					foreach ($meanings as $meaning)
					{
						$key = $name . ':' . $meaning[1] . ':' . $meaning[2];

						if (!isset ($this->resolvers[$key]))
							$this->resolvers[$key] = array ();

						$this->resolvers[$key][] = array ($accept, $meaning[0], $meaning[3]);
					}
				}
			}

			$this->limits[$name] = isset ($definition['limit']) ? (int)$definition['limit'] : $limit;
		}
	}

	/*
	** Override for Converter::convert.
	*/
	public function convert ($text, $custom = null)
	{
		// Parse original string using internal scanner
		$callbacks =& $this->onConverts;
		$chains = array ();
		$context = array ('default' => 1);
		$limits =& $this->limits;
		$tags = array ();
		$usages = array ();

		$process = function ($match, $offset, $length, $captures) use (&$callbacks, &$chains, &$context, &$custom, &$limits, &$tags, &$usages)
		{
			list ($name, $meanings) = $match;

			// Ensure tag limit has not be reached
			$limit = $limits[$name];
			$usage = isset ($usages[$name]) ? $usages[$name] : 0;

			if ($limit > 0 && $usage >= $limit)
				return false;

			$usages[$name] = $usage + 1;

			// Find action from current context
			foreach ($meanings as $meaning)
			{
				list ($condition, $action, $flag, $switch) = $meaning;

				// Check whether meaning is acceptable or not
				foreach ($condition as $key => $exists)
				{
					if (isset ($context[$key]) !== $exists)
						continue 2;
				}

				if (isset ($callbacks[$name]) && $callbacks[$name] ($action, $flag, $captures, $custom) === false)
					continue;

				// Add current match to tags chain
				if (!isset ($chains[$name]))
					$chains[$name] = array ();

				$chain =& $chains[$name];

				// Set start of chain to be flushed
				switch ($action)
				{
					case Action::ALONE:
						$flush = count ($chain);

						break;

					case Action::START:
						$flush = null;

						break;

					case Action::STEP:
						for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != Action::START; )
							--$start;

						if ($start < 0)
							continue 2;

						$flush = null;

						break;

					case Action::STOP:
						for ($start = count ($chain) - 1; $start >= 0 && $chain[$start][3] != Action::START; )
							--$start;

						if ($start < 0)
							continue 2;

						$flush = $start;

						break;

					default:
						continue 2;
				}

				// Update context keys
				foreach ($switch as $key => $update)
				{
					$value = $update (isset ($context[$key]) ? $context[$key] : 0);

					if ($value > 0)
						$context[$key] = $value;
					else
						unset ($context[$key]);
				}

				// Add matched tag to chain
				$chain[] = array ($offset, $length, $name, $action, $flag, $captures);

				// Push chain section to tags
				if ($flush !== null)
				{
					foreach (array_splice ($chain, $flush) as $tag)
						$tags[$tag[0]] = $tag;
				}

				return true;
			}

			return false;
		};

		$verify = function ($match) use (&$context, &$limits, &$usages)
		{
			list ($name, $meanings) = $match;

			// Ensure tag limit has not be reached
			$limit = $limits[$name];
			$usage = isset ($usages[$name]) ? $usages[$name] : 0;

			if ($limit > 0 && $usage >= $limit)
				return false;

			// Find action from current context
			foreach ($meanings as $meaning)
			{
				foreach ($meaning[0] as $key => $exists)
				{
					if (isset ($context[$key]) !== $exists)
						continue 2;
				}

				return true;
			}

			return false;
		};

		$text = $this->scanner->scan ($text, $process, $verify);

		// Sort resolved tags and remove from plain text string
		ksort ($tags);

		$origin = 0;
		$scopes = array ();
		$shift = 0;

		foreach ($tags as $tag)
		{
			list ($offset, $length, $name, $action, $flag, $captures) = $tag;

			$scopes[] = array ($offset - $origin, $name, $action, $flag, $captures);
			$text = mb_substr ($text, 0, $offset - $shift) . mb_substr ($text, $offset - $shift + $length);

			$origin = $offset + $length;
			$shift += $length;
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($scopes, $text);
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $custom = null)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($scopes, $text) = $pack;

		$callbacks =& $this->onReverts;
		$context = array ('default' => 1);
		$offset = 0;

		$verify = function ($match) use (&$context)
		{
			list ($name, $meanings) = $match;

			foreach ($meanings as $meaning)
			{
				foreach ($meaning[0] as $key => $exists)
				{
					if (isset ($context[$key]) !== $exists)
						continue 2;
				}

				return true;
			}

			return false;
		};

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Decode current tag
			$string = '';
			$switch = array ();

			if (!isset ($callbacks[$name]) || $callbacks[$name] ($action, $flag, $captures, $custom) !== false)
			{
				$key = $name . ':' . $action . ':' . $flag;

				if (isset ($this->resolvers[$key]))
				{
					foreach ($this->resolvers[$key] as $resolver)
					{
						list ($accept, $condition) = $resolver;

						// Check whether meaning is acceptable or not
						foreach ($condition as $key => $exists)
						{
							if (isset ($context[$key]) !== $exists)
								continue 2;
						}

						// Decode tag to string and save context switch
						$string = $this->scanner->make ($accept, $captures);
						$switch = $resolver[2];

						break;
					}
				}
			}

			// Escape skipped plain text and insert tag
			$chunk = $this->scanner->escape (mb_substr ($text, $offset, $delta), $verify);
			$text = mb_substr ($text, 0, $offset) . $chunk . $string . mb_substr ($text, $offset + $delta);

			$offset += mb_strlen ($chunk) + mb_strlen ($string);

			// Apply context switch for resolved tag
			foreach ($switch as $key => $update)
			{
				$value = $update (isset ($context[$key]) ? $context[$key] : 0);

				if ($value > 0)
					$context[$key] = $value;
				else
					unset ($context[$key]);
			}
		}

		// Escape remaining plain text
		$chunk = $this->scanner->escape (mb_substr ($text, $offset), $verify);
		$text = mb_substr ($text, 0, $offset) . $chunk;

		return $text;
	}

	private static function parseCondition ($expression)
	{
		$result = array ();

		if ($expression !== '')
		{
			foreach (explode (';', $expression) as $key)
			{
				if (strlen ($key) > 0 && $key[0] === '!')
					$result[substr ($key, 1)] = false;
				else
					$result[$key] = true;
			}
		}

		return $result;
	}

	private static function parseSwitch ($expression)
	{
		$switch = array ();

		foreach (explode (';', $expression) as $fragment)
		{
			if (strlen ($fragment) < 1)
				continue;

			$context = substr ($fragment, 1);

			switch ($fragment[0])
			{
				case '-':
					$switch[$context] = function ($value)
					{
						return $value - 1;
					};

					break;

				case '+':
					$switch[$context] = function ($value)
					{
						return $value + 1;
					};

					break;
			}
		}

		return $switch;
	}
}

?>
