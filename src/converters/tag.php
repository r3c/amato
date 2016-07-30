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
		// Resolve candidate into matched groups
		$candidates = $this->scanner->find ($markup);
		$groups = array ();

		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($key, $offset, $length) = $candidates[$i];

			// Skip disabled candidates
			if ($length === null)
				continue;

			// Candidate is a tag sequence
			if ($key !== null)
			{
				list ($id, $type, $defaults, $convert) = $this->attributes[$key];

				// Ignore tag types that can't start a group
				if ($type !== Tag::ALONE && $type !== Tag::FLIP && $type !== Tag::PULSE && $type !== Tag::START)
					continue;

				// FIXME: call pre-convert callback here if any

				// Search for compatible matches in candidates
				$incomplete = $type !== Tag::ALONE;
				$matches = array ($i);

				for ($j = $i + 1; $incomplete && $j < count ($candidates); ++$j)
				{
					list ($key, $offset, $length) = $candidates[$j];

					// Skip disabled candidates
					if ($length === null)
						continue;

					// Skip escape sequences along with following escaped candidates
					if ($key === null)
					{
						while ($j + 1 < count ($candidates) && $offset + $length === $candidates[$j + 1][1])
							++$j;

						continue;
					}

					list ($id_next, $type, $defaults, $convert) = $this->attributes[$key];

					// Ignore tag types that can't continue a group
					if ($id !== $id_next || ($type !== Tag::FLIP && $type !== Tag::PULSE && $type !== Tag::STEP && $type !== Tag::STOP))
						continue;

					// FIXME: call pre-convert callback here if any

					$incomplete = $type === Tag::PULSE || $type === Tag::STEP;
					$matches[] = $j;
				}

				// Matches group is incomplete, ignore it
				if ($incomplete)
					continue;

				// Append completed tag sequence to groups
				$groups[] = array ($id, $matches);
			}

			// Candidate is an escape sequence
			else
			{
				$incomplete = true;

				// Disabled following escaped candidates
				for ($j = $i + 1; $j < count ($candidates) && $offset + $length === $candidates[$j][1]; ++$j)
				{
					$candidates[$j][2] = null;
					$incomplete = false;
				}

				// Escape sequence doesn't escape anything, ignore it
				if ($incomplete)
					continue;

				// Flag escape sequence for removal
				$matches = array ($i);
			}

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
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($markup, $this->build_chains ($candidates, $groups));
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

		list ($plain, $chains) = $pair;

		// Build linear list of matches, ordered by offset
		$matches = array ();

		foreach ($chains as $precedence => $chain)
		{
			list ($id, $markers) = $chain;

			for ($i = 0; $i < count ($markers); ++$i)
			{
				list ($offset, $captures) = $markers[$i];

				$index = str_pad ($offset, 8, '0', STR_PAD_LEFT) . ':' . str_pad ($precedence, 8, '0', STR_PAD_LEFT);
				$matches[$index] = array ($id, $offset, $captures, $i === 0, $i + 1 === count ($markers));
			}
		}

		ksort ($matches);

		// Build tokens and insert into plain string
		$depths = array ();
		$markup = '';
		$start = 0;

		foreach ($matches as $match)
		{
			list ($id, $offset, $captures, $is_first, $is_last) = $match;

			// Append plain string before current tag
			$markup .= $this->build_markup ($depths, mb_substr ($plain, $start, $offset - $start));
			$start = $offset;

			// Find definition matching current marker
			foreach ($this->attributes as $key => $attribute)
			{
				list ($id_attribute, $type, $defaults, $convert, $revert) = $attribute;

				// Skip definition if id, type or captures don't match
				if (($id !== $id_attribute) ||
					($type === Tag::ALONE && (!$is_first || !$is_last)) ||
					($type === Tag::FLIP && !$is_first && !$is_last) ||
					($type === Tag::PULSE && $is_last) ||
					($type === Tag::START && !$is_first) ||
					($type === Tag::STEP && ($is_first || $is_last)) ||
					($type === Tag::STOP && !$is_last) ||
				    (count (array_diff_assoc ($defaults, $captures)) > 0))
					continue;

				// Insert reverted tag into plain string
				$markup .= $this->scanner->build ($key, $captures);

				// Update depth of current marker id
				if ($type === Tag::FLIP && isset ($depths[$id]))
					$depths[$id] = ($depths[$id] - 1) ?: null;
				else if (($type === Tag::FLIP || $type === Tag::PULSE) && !isset ($depths[$id]))
					$depths[$id] = 1;
				else if ($type === Tag::START)
					$depths[$id] = (isset ($depths[$id]) ? $depths[$id] : 0) + 1;

				break;
			}
		}

		// Append remaining plain string
		$markup .= $this->build_markup ($depths, mb_substr ($plain, $start));

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

	/*
	** Build plain string from given raw string, by escaping tag sequences that
	** would be matched when converting it.
	** $depths:	array of id => depth values per id
	** $raw:	unescaped raw string
	** return:	escaped plain string
	*/
	private function build_markup ($depths, $plain)
	{
		$candidates = $this->scanner->find ($plain);
		$markup = $plain;

		foreach (array_reverse ($candidates) as $candidate)
		{
			list ($key, $offset, $length) = $candidate;

			// Candidate is a tag, ensure there is need to escape it
			if ($key !== null)
			{
				list ($id, $type, $defaults) = $this->attributes[$key];

				$captures = $candidate[3];

				if ((($type !== Tag::ALONE) &&
					 ($type !== Tag::FLIP) &&
					 ($type !== Tag::PULSE) &&
					 ($type !== Tag::START) &&
					 ($type !== Tag::STEP || !isset ($depths[$id])) &&
					 ($type !== Tag::STOP || !isset ($depths[$id]))) ||
					count (array_diff_assoc ($defaults, $captures)) > 0)
					continue;
			}

			// Escape candidate
			$markup = mb_substr ($markup, 0, $offset) . $this->scanner->escape (mb_substr ($markup, $offset, $length)) . mb_substr ($markup, $offset + $length);
		}

		return $markup;
	}
}

?>
