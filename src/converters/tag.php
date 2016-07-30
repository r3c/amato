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
	** $escape:		escape sequence
	*/
	public function __construct ($encoder, $scanner, $syntax, $escape = '\\')
	{
		$this->attributes = array ();
		$this->encoder = $encoder;
		$this->escape = $escape;
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
		$candidates = $this->scanner->find ($markup);
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
				list ($key, $offset, $length, $captures) = $candidates[$j];

				if ($key === null)
					continue;

				list ($id_next, $type, $defaults, $convert) = $this->attributes[$key];

				if ($id !== $id_next || ($type !== Tag::FLIP && $type !== Tag::STEP && $type !== Tag::STOP))
					continue;

				// FIXME: call pre-convert callback here if any

				$matches[] = array ($j, $captures + $defaults);
				$search = $type === Tag::STEP;
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

		// Encode into tokenized string and return
		return $this->encoder->encode (self::build_tags ($candidates, $groups), $markup);
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $context = null)
	{
	}

	/*
	** Build tags from matched groups.
	*/
	private static function build_tags ($candidates, $groups)
	{
		$tags = array ();

		foreach ($groups as $group)
		{
			list ($id, $matches) = $group;

			$markers = array ();

			foreach ($matches as $match)
			{
				list ($key, $captures) = $match;

				$markers[] = array ($candidates[$key][1], $captures);
			}

			$tags[] = array ($id, $markers);
		}

		return $tags;
	}
}

?>
