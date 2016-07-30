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
		// Resolve candidate into matched groups
		$candidates = $this->scanner->find ($markup);
		$groups = array ();

		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($key, $offset, $length) = $candidates[$i];

			if ($length === null)
				continue;

			list ($id, $type, $defaults, $convert) = $this->attributes[$key];

			// Ignore tag types that can't start a group
			if ($type !== Tag::ALONE && $type !== Tag::FLIP && $type !== Tag::START)
				continue;

			// FIXME: call pre-convert callback here if any

			// Search for compatible matches in candidates
			$incomplete = $type !== Tag::ALONE;
			$matches = array ($i);

			for ($j = $i + 1; $incomplete && $j < count ($candidates); ++$j)
			{
				list ($key, $offset, $length) = $candidates[$j];

				if ($length === null)
					continue;

				list ($id_next, $type, $defaults, $convert) = $this->attributes[$key];

				if ($id !== $id_next || ($type !== Tag::FLIP && $type !== Tag::STEP && $type !== Tag::STOP))
					continue;

				// FIXME: call pre-convert callback here if any

				$incomplete = $type === Tag::STEP;
				$matches[] = $j;
			}

			// Matches group is incomplete, ignore it
			if ($incomplete)
				continue;

			// Search for escape sequences before matches
			// FIXME

			// Remove matches from string and fix offsets
			foreach ($matches as $match)
			{
				list ($key1, $offset1, $length1) = $candidates[$match];

				// Disable current candidate
				$candidates[$match][2] = null;

				// Disable overlapped candidates, shift successors
				for ($next = $match + 1; $next < count ($candidates); ++$next)
				{
					list ($key2, $offset2, $length2) = $candidates[$next];

					if ($length2 !== null && $offset1 < $offset2 + $length2 && $offset2 < $offset1 + $length1)
						$candidates[$next][2] = null;

					$candidates[$next][1] -= $length1;
				}

				// Remove match from string
				$markup = mb_substr ($markup, 0, $offset1) . mb_substr ($markup, $offset1 + $length1);
			}

			// Append to completed groups
			$groups[] = array ($id, $matches);
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($this->build_chains ($candidates, $groups), $markup);
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $context = null)
	{
		// Decode tokenized string into chains and pairs
		$pair = $this->encoder->decode ($token);

		if ($pair === null)
			return null;

		list ($chains, $markup) = $pair;

		// Build tokens and insert into plain string
		$shift = 0;

		while (count ($chains) > 0)
		{
			// Find next marker occurrence by offset
			$index = 0;
			$min = $chains[$index][1][0][0];

			for ($i = 1; $i < count ($chains) && $chains[$i][1][0][0] < $min; ++$i)
			{
				$index = $i;
				$min = $chains[$i][1][0][0];
			}

			// Remove marker from chains, and chain if no markers are left
			list ($offset, $captures) = array_shift ($chains[$index][1]);
			list ($id, $markers) = $chains[$index];

			if (count ($markers) === 0)
				array_splice ($chains, $index, 1);

			// Find definition matching current marker
			$insert = null;

			foreach ($this->attributes as $key => $attribute)
			{
				list ($id_attribute, $type, $defaults, $convert, $revert) = $attribute;

				// Skip definition if ids don't match
				if ($id !== $id_attribute)
					continue;

				// FIXME: check if type is compatible

				// Skip definition if captures and defaults don't match
				if (count (array_diff_assoc ($defaults, $captures)) > 0)
					continue;

				$insert = $this->scanner->build ($key, $captures);

				break;
			}

			// Insert tag into markup string
			if ($insert !== null)
			{
				$markup = mb_substr ($markup, 0, $offset + $shift) . $insert . mb_substr ($markup, $offset + $shift);
				$shift += mb_strlen ($insert);
			}
		}

		return $markup;
	}

	/*
	** Build chains from matched groups.
	** $candidates:	array of (key, offset, length, captures) candidates
	** $groups:		array of (id, array of matches) completed groups
	** return:		array of (id, array of (offset, captures)) chains
	*/
	private function build_chains ($candidates, $groups)
	{
		$chains = array ();

		foreach ($groups as $group)
		{
			list ($id, $matches) = $group;

			$markers = array ();

			foreach ($matches as $match)
			{
				list ($key, $offset, $length, $captures) = $candidates[$match];

				$markers[] = array ($offset, $captures + $this->attributes[$key][2]);
			}

			$chains[] = array ($id, $markers);
		}

		return $chains;
	}
}

?>
