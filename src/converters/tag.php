<?php

namespace Amato;

defined ('AMATO') or die;

class TagConverterCandidate
{
	public function __construct ($id)
	{
		$this->id = $id;
		$this->matches = array ();
	}
}

class TagConverterMatch
{
	public function __construct ($offset, $length, $params, $incomplete)
	{
		$this->incomplete = $incomplete;
		$this->length = $length;
		$this->offset = $offset;
		$this->params = $params;
	}

	/*
	** Check if given match overlaps this one.
	** $other:	other match instance
	** return:	true if matches overlap, false otherwise
	*/
	public function overlap ($other)
	{
		return $this->offset < $other->offset + $other->length && $other->offset < $this->offset + $this->length;
	}
}

class TagConverter extends Converter
{
	/*
	** Constructor.
	** $encoder:	encoder instance
	** $scanner:	scanner instance
	** $syntax:		syntax (id, definitions) declaration
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
				list ($key, $names) = $scanner->assign ((string)$definition[1]);

				$convert = isset ($definition[3]) ? $definition[3] : null;
				$defaults = isset ($definition[2]) ? array_map ('strval', $definition[2]) : array ();
				$revert = isset ($definition[4]) ? $definition[4] : null;
				$type = (int)$definition[0];

				$this->attributes[$key] = array ($id, $type, $defaults, $names, $convert, $revert);
			}
		}
	}

	/*
	** Override for Converter::convert.
	*/
	public function convert ($markup, $context = null)
	{
		// Group compatible tags into array of matches by group candidate
		$blocks = array ();
		$candidates = array ();
		$shift = 0;
		$tags = $this->scanner->find ($markup);

		for ($i = 0; $i < count ($tags); ++$i)
		{
			list ($key, $offset, $length) = $tags[$i];

			// Fix offset to take removed escape tags into account
			$offset += $shift;

			// Current tag is an escape tag
			if ($key === null)
			{
				// Skip tags following this escape tag if any
				for ($escape = false; $i + 1 < count ($tags) && $tags[$i + 1][1] + $shift <= $offset + $length; ++$i)
					$escape = true;

				// Flag escape tag for removal if it had an effect
				if ($escape)
				{
					$markup = mb_substr ($markup, 0, $offset) . mb_substr ($markup, $offset + $length);
					$shift -= $length;
				}
			}

			// Current tag is a sequence tag, insert into candidates
			else
			{
				list ($id, $type, $defaults, $names, $convert) = $this->attributes[$key];

				// Augment captured parameters with defaults
				$params = $tags[$i][3] + $defaults;

				// Call convert callback if any, ignore tag if requested
				if ($convert !== null && $convert ($type, $params, $context) === false)
					continue;

				// Append to compatible candidates
				self::candidate_append ($candidates, $id, $type, $offset, $length, $params);

				// Try to resolve candidates into marker groups and flag them for removal
				$min = self::candidate_accept ($candidates, $blocks, false);

				// Skip following overlapped tags
				while ($i + 1 < count ($tags) && $tags[$i + 1][1] + $shift < $min)
					++$i;
			}
		}

		// Resolve compatible candidates into marker groups
		self::candidate_accept ($candidates, $blocks, true);

		// List markers from all groups and sort them by offset
		$cursors = array ();
		$groups = array ();

		foreach ($blocks as $i => $block)
		{
			$markers = array ();

			foreach ($block[1] as $j => $marker)
			{
				$cursors[] = array ($marker[0], $marker[1], $i, $j);
				$markers[] = array ($marker[0], $marker[2]);
			}

			$groups[] = array ($block[0], $markers);
		}

		usort ($cursors, function ($c1, $c2)
		{
			return $c1[0] < $c2[0] ? -1 : 1;
		});

		// Remove groups from markup string and fix offsets
		$plain = $markup;

		for ($i = 0; $i < count ($cursors); ++$i)
		{
			list ($offset, $length) = $cursors[$i];

			// Shift all references located after current one
			for ($j = $i; $j < count ($cursors); ++$j)
				$cursors[$j][0] -= $length;

			// Shift all markers located after current reference
			foreach ($groups as &$group)
			{
				foreach ($group[1] as &$marker)
				{
					if ($marker[0] > $offset)
						$marker[0] -= $length;
				}
			}

			// Remove match from string
			$plain = mb_substr ($plain, 0, $offset) . mb_substr ($plain, $offset + $length);
		}

		// Increment all marker offets by the number of marker preceeding them
		// so each marker has a different offset and can be strictly ordered
		for ($i = 0; $i < count ($cursors); ++$i)
			$groups[$cursors[$i][2]][1][$cursors[$i][3]][0] += $i;

		// Sort groups by offset of their first marker
		uasort ($groups, function ($g1, $g2)
		{
			return $g1[1][0] < $g2[1][0] ? -1 : 1;
		});

		// Encode into tokenized string and return
		return $this->encoder->encode ($plain, $groups);
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $context = null)
	{
		// Decode tokenized string into marker groups and pairs
		$pair = $this->encoder->decode ($token);

		if ($pair === null)
			return null;

		list ($plain, $groups) = $pair;

		// Define callback used for parameters filtering
		$not_null = function ($value) { return $value !== null; };

		// Browse groups and markers, revert them into text and insert into plain string
		$levels = array ();
		$markup = '';
		$start = 0;

		for ($cursors = Encoder::begin ($groups); Encoder::next ($groups, $cursors, $next); )
		{
			list ($id, $offset, $is_first, $is_last, $params) = $next;

			// Escape and append skipped plain string to markup
			$markup .= $this->build_markup ($levels, mb_substr ($plain, $start, $offset - $start));
			$start = $offset;

			// Find definition matching current marker
			foreach ($this->attributes as $key => $attribute)
			{
				list ($id_attribute, $type, $defaults, $names, $convert, $revert) = $attribute;

				// Accept current definition if...
				if
				(
					// ...it matches tag attribute
					($id === $id_attribute) &&

					// ...it has a compatible type
					($type !== Tag::ALONE || ($is_first && $is_last)) &&
					($type !== Tag::FLIP || ($is_first || $is_last)) &&
					($type !== Tag::PULSE || !$is_last) &&
					($type !== Tag::START || $is_first) &&
					($type !== Tag::STEP || (!$is_first && !$is_last)) &&
					($type !== Tag::STOP || $is_last) &&

					// ...it has same defaults and equivalent captures
					(count (array_diff_assoc ($defaults, $params)) === 0) &&
					(count (array_diff ($names, array_keys ($params))) === 0) &&

					// ...and pass revert callback or doesn't have any
					($revert === null || $revert ($type, $params, $context) !== false)
				)
				{
					// Revert sequence tag and append to plain string
					$markup .= $this->scanner->build ($key, $params);

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
		}

		// Escape and append remaining plain string to markup
		$markup .= $this->build_markup ($levels, mb_substr ($plain, $start));

		return $markup;
	}

	/*
	** Build plain string from given raw string, by escaping tags that would be
	** matched when converting it.
	** $levels:	array of id => depth values per id
	** $raw:	unescaped raw string
	** return:	escaped plain string
	*/
	private function build_markup ($levels, $plain)
	{
		$markup = $plain;
		$tags = $this->scanner->find ($plain);

		for ($i = count ($tags); $i-- > 0; )
		{
			list ($key, $offset, $length) = $tags[$i];

			// Tag is a registered definition, ensure there is need to escape it
			// FIXME: detection could be made more accurate by ensuring tag could be registered [revert-escape]
			if ($key !== null)
			{
				list ($id, $type) = $this->attributes[$key];

				if (($type !== Tag::ALONE) &&
					($type !== Tag::FLIP) &&
					($type !== Tag::PULSE) &&
					($type !== Tag::START) &&
					($type !== Tag::STEP || !isset ($levels[$id])) &&
					($type !== Tag::STOP || !isset ($levels[$id])))
					continue;
			}

			// Tag is an escape sequence
			$markup = mb_substr ($markup, 0, $offset) . $this->scanner->escape (mb_substr ($markup, $offset, $length)) . mb_substr ($markup, $offset + $length);

			// Skip tags overlapping the one we just escaped, as they're now escaped too
			while ($i > 0 && $tags[$i - 1][1] + $tags[$i - 1][2] > $offset)
				--$i;
		}

		return $markup;
	}

	/*
	** Accept completed candidates, convert into blocks and flag for removal,
	** remove overlapped candidates from list.
	** &candidates:	candidates (id, matches) list
	** &blocks:		resolved marker blocks (id, [(offset, length, params)]) list
	** $finish:		true when all candidates are registered, false otherwise
	** return:		highest matched candidate offset
	*/
	private static function candidate_accept (&$candidates, &$blocks, $finish)
	{
		$offset = 0;

		for ($accept_candidate = count ($candidates); $accept_candidate-- > 0; )
		{
			$candidate1 = $candidates[$accept_candidate];

			// Find first match able to complete current candidate group
			for ($last = 0; $last < count ($candidate1->matches) && $candidate1->matches[$last]->incomplete; )
				++$last;

			if ($last >= count ($candidate1->matches))
				continue;

			// Search for candidates overlapping any match of current one
			$drops = array ();

			for ($accept_match = 0; $accept_match <= $last; ++$accept_match)
			{
				$match = $candidate1->matches[$accept_match];

				for ($other_candidate = count ($candidates); $other_candidate-- > 0; )
				{
					$candidate2 = $candidates[$other_candidate];

					for ($other_match = count ($candidate2->matches); $other_match-- > 0; )
					{
						// Current match overlaps match of another candidate...
						if ($match->overlap ($candidate2->matches[$other_match]))
						{
							// ...which is unresolved and before current, abort
							if ($accept_candidate > $other_candidate && !$finish)
								continue 4;

							// ...which must be flagged for removal
							$drops[$other_candidate][] = $other_match;
						}
					}
				}
			}

			// Remove all flagged overlapps from candidates
			krsort ($drops);

			foreach ($drops as $other_candidate => $indices)
			{
				// Drop entire candidate if its first match is removed
				if (in_array (0, $indices, true))
				{
					if  ($other_candidate < $accept_candidate)
						--$accept_candidate;

					array_splice ($candidates, $other_candidate, 1);
				}

				// Otherwise just remove overlapped match
				else
				{
					rsort ($indices);

					foreach ($indices as $other_match)
						array_splice ($candidates[$other_candidate]->matches, $other_match, 1);
				}
			}

			// Convert matches into markers and insert into blocks
			$markers = array ();

			for ($accept_match = 0; $accept_match <= $last; ++$accept_match)
			{
				$match = $candidate1->matches[$accept_match];

				$markers[] = array ($match->offset, $match->length, $match->params);
				$offset = max ($match->offset + $match->length, $offset);
			}

			$blocks[] = array ($candidate1->id, $markers);
		}

		return $offset;
	}

	/*
	** Append tag to all compatible candidates.
	** &candidates:	candidates (id, matches) list
	** $id:			tag id
	** $type:		tag type
	** $offset:		tag offset
	** $length:		tag length
	** $params:		tag parameters
	*/
	private static function candidate_append (&$candidates, $id, $type, $offset, $length, $params)
	{
		$inserts = array ();

		// Tag can continue an existing group, find compatible candidates
		if ($type === Tag::FLIP || $type === Tag::PULSE || $type === Tag::STEP || $type === Tag::STOP)
		{
			for ($i = 0; $i < count ($candidates); ++$i)
			{
				$last_match = $candidates[$i]->matches[count ($candidates[$i]->matches) - 1];

				// Tag share same id than candidate and doesn't overlap its last tag
				if ($candidates[$i]->id === $id && $offset >= $last_match->offset + $last_match->length)
					$inserts[$i] = $type === Tag::PULSE || $type === Tag::STEP;
			}
		}

		// Tag can start a new group, create new candidate
		if ($type === Tag::ALONE || $type === Tag::PULSE || $type === Tag::FLIP || $type === Tag::START)
		{
			$candidates[] = new TagConverterCandidate ($id);
			$inserts[count ($candidates) - 1] = $type !== Tag::ALONE;
		}

		// Append match to compatible candidates
		foreach ($inserts as $i => $incomplete)
			$candidates[$i]->matches[] = new TagConverterMatch ($offset, $length, $params, $incomplete);
	}
}

?>
