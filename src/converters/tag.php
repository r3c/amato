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

					if ($length === null)
						continue;

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

			// Candidate is an escape sequence and disables next candidate
			else if ($i + 1 < count ($candidates) && $offset + $length === $candidates[$i + 1][1])
			{
				$candidates[$i + 1][2] = null;
				$matches = array ($i);
			}

			// Candidate is an escape sequence but doesn't escape any candidate
			else
				continue;

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

		list ($markup, $chains) = $pair;

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
		$shift = 0;

		foreach ($matches as $match)
		{
			list ($id, $offset, $captures, $is_first, $is_last) = $match;

			// Find definition matching current marker
			$insert = null;

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
