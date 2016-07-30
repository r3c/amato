<?php

namespace Amato;

defined ('AMATO') or die;

class TagConverter extends Converter
{
	/*
	** Constructor.
	** $encoder:	encoder instance
	** $scanner:	scanner instance
	** $syntax:		syntax configuration
	*/
	public function __construct ($encoder, $scanner, $syntax)
	{
		$this->attributes = array ();
		$this->encoder = $encoder;
		$this->scanner = $scanner;

		foreach ($syntax as $id => $definitions)
		{
			foreach ($definitions as $definition)
			{
				$convert = isset ($definition[3]) ? $definition[3] : null;
				$defaults = isset ($definition[2]) ? (array)$definition[2] : array ();
				$revert = isset ($definition[4]) ? $definition[4] : null;
				$type = (int)$definition[0];

				$key = $scanner->assign ((string)$definition[1]);

				$this->attributes[$key] = array ($id, $type, $defaults, $convert, $revert);
			}
		}
	}

	/*
	** Override for Converter::convert.
	*/
	public function convert ($markup, $context = null)
	{
		// Resolve candidate into matched tags chains
		list ($markup, $candidates) = $this->scanner->find ($markup);

		$groups = array ();

		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($key, $offset, $length, $captures) = $candidates[$i];

			if ($key === null)
				continue;

			list ($id, $type, $defaults, $convert) = $this->attributes[$key];

			// Ignore tag types that can't start a chain
			if ($type !== Tag::ALONE && $type !== Tag::FLIP && $type !== Tag::START)
				continue;

			// FIXME: call pre-convert callback here if any

			// Search for compatible matches in candidates
			$matches = array (array ($i, $captures + $defaults));
			$search = $type !== Tag::ALONE;

			for ($j = $i + 1; $search && $j < count ($candidates); ++$j)
			{
				list ($key2, $offset2, $length2, $captures2) = $candidates[$j];

				if ($key2 === null)
					continue;

				list ($id2, $type2, $defaults2, $convert2) = $this->attributes[$key2];

				if ($id2 !== $id || ($type2 !== Tag::FLIP && $type2 !== Tag::STEP && $type2 !== Tag::STOP))
					continue;

				// FIXME: call pre-convert callback here if any

				$matches[] = array ($j, $captures2 + $defaults2);
				$search = $type2 === Tag::STEP;
			}

			// Matches chain is incomplete, ignore it
			if ($search)
				continue;

			// Search for escape sequences before matches
			// FIXME

			// Remove matches from string and fix offsets
			foreach ($matches as $match)
			{
				list ($index, $captures) = $match;
				list ($key1, $offset1, $length1) = $candidates[$index];

				// Disable current candidate
				$candidates[$index][0] = null;

				// Disable overlapped candidates, shift successors
				for (++$index; $index < count ($candidates); ++$index)
				{
					list ($key2, $offset2, $length2) = $candidates[$index];

					if ($offset1 < $offset2 + $length2 && $offset2 < $offset1 + $length1)
						$candidates[$index][0] = null;

					$candidates[$index][1] -= $length1;
				}

				// Remove match from string
				$markup = mb_substr ($markup, 0, $offset1) . mb_substr ($markup, $offset1 + $length1);
			}

			$groups[] = array ($id, $matches);
		}

		// Build tag markers from matches groups
		$tags = array ();

		foreach ($groups as $group)
		{
			list ($id, $matches) = $group;

			$markers = array ();

			foreach ($matches as $match)
				$markers[] = array ($candidates[$match[0]][1], $match[1]);

			$tags[] = array ($id, $markers);
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($tags, $markup);
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $context = null)
	{
		// Parse tokenized string
		$pack = $this->encoder->decode ($token);

		if ($pack === null)
			return null;

		list ($scopes, $plain) = $pack;

		$callbacks =& $this->onReverts;
		$context = array ('default' => 1);
		$limits =& $this->limits;
		$offset = 0;
		$usages = array ();

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

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Decode current tag
			$string = '';
			$switch = array ();

			if (!isset ($callbacks[$name]) || $callbacks[$name] ($action, $flag, $captures, $context) !== false)
			{
				$key = $name . ':' . $action . ':' . $flag;

				if (isset ($this->resolvers[$key]))
				{
					foreach ($this->resolvers[$key] as $resolver)
					{
						list ($accept, $condition) = $resolver;

						// Ensure tag limit has not be reached
						$limit = $limits[$name];
						$usage = isset ($usages[$name]) ? $usages[$name] : 0;

						if ($limit > 0 && $usage >= $limit)
							continue;

						$usages[$name] = $usage + 1;

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
			$chunk = $this->scanner->escape (mb_substr ($plain, $offset, $delta), $verify);
			$plain = mb_substr ($plain, 0, $offset) . $chunk . $string . mb_substr ($plain, $offset + $delta);

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
		$chunk = $this->scanner->escape (mb_substr ($plain, $offset), $verify);
		$plain = mb_substr ($plain, 0, $offset) . $chunk;

		return $plain;
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
