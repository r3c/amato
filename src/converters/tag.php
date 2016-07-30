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
				$type = (int)$definition[0];

				$key = $scanner->assign ((string)$definition[1]);

				$this->attributes[$key] = array ($id, $type, $defaults, $convert);
			}
		}
	}

	/*
	** Override for Converter::convert.
	*/
	public function convert ($markup, $context = null)
	{
		// Resolve candidate into matched references
		$candidates = $this->scanner->find ($markup);
		$references = array ();

		for ($i = 0; $i < count ($candidates); ++$i)
		{
			list ($key, $offset, $length) = $candidates[$i];

			// Ignore disabled candidates
			if ($length === null)
				continue;

			// Candidate is a tag sequence
			if ($key !== null)
			{
				list ($id, $type, $defaults, $convert) = $this->attributes[$key];

				// Ignore tag types that can't start a group
				if ($type !== Tag::ALONE && $type !== Tag::FLIP && $type !== Tag::PULSE && $type !== Tag::START)
					continue;

				// Search for compatible matches in candidates
				$incomplete = $type !== Tag::ALONE;
				$matches = array (array ($i, $candidates[$i][3] + $defaults, $convert));

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

					$incomplete = $type === Tag::PULSE || $type === Tag::STEP;
					$matches[] = array ($j, $candidates[$j][3] + $defaults, $convert);
				}

				// Matches list is incomplete, ignore it
				if ($incomplete)
					continue;

				// Call pre-convert callbacks and cancel tag sequence if one fails
				foreach ($matches as &$match)
				{
					$convert = $match[2];

					if ($convert === null)
						continue;

					if ($convert ($match[1], $context) === false)
						continue 2;
				}

				// Append completed tag sequence to references
				$references[] = array ($id, $matches);
				$strips = array_map (function ($match) { return $match[0]; }, $matches);
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
				$strips = array ($i);
			}

			// Remove escape or tag sequences from string and fix offsets
			foreach ($strips as $strip)
			{
				list ($key1, $offset1, $length1) = $candidates[$strip];

				// Disable current candidate
				$candidates[$strip][2] = null;

				// Disable overlapped candidates, shift successors
				for ($next = 0; $next < count ($candidates); ++$next)
				{
					list ($key2, $offset2, $length2) = $candidates[$next];

					if ($length2 !== null && $offset1 < $offset2 + $length2 && $offset2 < $offset1 + $length1)
						$candidates[$next][2] = null;

					if ($strip < $next)
						$candidates[$next][1] -= $length1;
				}

				// Remove match from string
				$markup = mb_substr ($markup, 0, $offset1) . mb_substr ($markup, $offset1 + $length1);
			}
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($markup, $this->build_groups ($candidates, $references));
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $context = null)
	{
		// Decode tokenized string into groups and pairs
		$pair = $this->encoder->decode ($token);

		if ($pair === null)
			return null;

		list ($plain, $groups) = $pair;

		// Browse groups and markers, revert them into text and insert into plain string
		$cursors = Encoder::begin ($groups);
		$levels = array ();
		$markup = '';
		$start = 0;

		while (count ($cursors) > 0)
		{
			list ($id, $offset, $captures, $is_first, $is_last) = Encoder::next ($groups, $cursors);

			// Escape and append skipped plain string to markup
			$markup .= $this->build_markup ($levels, mb_substr ($plain, $start, $offset - $start));
			$start = $offset;

			// Find definition matching current marker
			foreach ($this->attributes as $key => $attribute)
			{
				list ($id_attribute, $type, $defaults) = $attribute;

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

				// Revert tag sequence and append to plain string
				$markup .= $this->scanner->build ($key, $captures);

				// Update depth level for current definition id
				if ($type === Tag::FLIP && isset ($levels[$id]))
					$levels[$id] = ($levels[$id] - 1) ?: null;
				else if (($type === Tag::FLIP || $type === Tag::PULSE) && !isset ($levels[$id]))
					$levels[$id] = 1;
				else if ($type === Tag::START)
					$levels[$id] = (isset ($levels[$id]) ? $levels[$id] : 0) + 1;

				break;
			}
		}

		// Escape and append remaining plain string to markup
		$markup .= $this->build_markup ($levels, mb_substr ($plain, $start));

		return $markup;
	}

	/*
	** Build groups from matched references.
	** $candidates:	array of (key, offset, length, captures) candidates
	** $references:	array of (id, array of matches) references
	** return:		array of (id, array of (offset, captures)) groups
	*/
	private function build_groups ($candidates, $references)
	{
		$groups = array ();

		foreach ($references as $reference)
		{
			list ($id, $matches) = $reference;

			$markers = array ();

			foreach ($matches as $match)
			{
				list ($i, $captures) = $match;

				$markers[] = array ($candidates[$i][1], $captures);
			}

			$groups[] = array ($id, $markers);
		}

		return $groups;
	}

	/*
	** Build plain string from given raw string, by escaping tag sequences that
	** would be matched when converting it.
	** $levels:	array of id => depth values per id
	** $raw:	unescaped raw string
	** return:	escaped plain string
	*/
	private function build_markup ($levels, $plain)
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
					 ($type !== Tag::STEP || !isset ($levels[$id])) &&
					 ($type !== Tag::STOP || !isset ($levels[$id]))) ||
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
