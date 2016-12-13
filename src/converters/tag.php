<?php

namespace Amato;

defined ('AMATO') or die;

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
		$candidates = array ();
		$markers = array ();
		$shift = 0;
		$tags = $this->scanner->find ($markup);
		$trims = array ();

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
				self::candidate_register ($candidates, $id, $type, $offset, $length, $params);

				// Try to resolve candidates into markers and flag them for removal
				for ($min = 0; count ($candidates) > 0 && self::candidate_resolve ($candidates, $markers, $trims, $min); )
				{
					// Skip following overlapped tags
					while ($i + 1 < count ($tags) && $tags[$i + 1][1] + $shift < $min)
						++$i;
				}
			}
		}

		// Resolve compatible candidates into markers and flag them for removal
		for ($min = 0; count ($candidates) > 0; )
		{
			if (!self::candidate_resolve ($candidates, $markers, $trims, $min))
				array_shift ($candidates);
		}

		// Remove markers from markup string and fix offsets
		// FIXME: could be optimized by doing a single pass on $trims [convert-on-the-fly]
		$plain = $markup;

		for ($i = 0; $i < count ($trims); ++$i)
		{
			list ($offset, $length) = $trims[$i];

			foreach ($markers as &$marker)
			{
				if ($marker[1] > $offset)
					$marker[1] -= $length;
			}

			for ($j = 0; $j < count ($trims); ++$j)
			{
				list ($offset2, $length2) = $trims[$j];

				if ($offset2 > $offset)
					$trims[$j][0] -= $length;
			}

			$plain = mb_substr ($plain, 0, $offset) . mb_substr ($plain, $offset + $length);
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($plain, $markers);
	}

	/*
	** Override for Converter::revert.
	*/
	public function revert ($token, $context = null)
	{
		// Decode tokenized string into markers and pairs
		$pair = $this->encoder->decode ($token);

		if ($pair === null)
			return null;

		list ($plain, $markers) = $pair;

		// Define callback used for parameters filtering
		$not_null = function ($value) { return $value !== null; };

		// Browse groups and markers, revert them into text and insert into plain string
		$levels = array ();
		$markup = '';
		$start = 0;

		foreach ($markers as $marker)
		{
			list ($id, $offset, $is_first, $is_last, $params) = $marker;

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
		$candidates = $this->scanner->find ($plain);
		$markup = $plain;

		for ($i = count ($candidates); $i-- > 0; )
		{
			list ($key, $offset, $length) = $candidates[$i];

			// Candidate is a tag, ensure there is need to escape it
			// FIXME: detection could be made more accurate by ensuring candidate could be registered [revert-escape]
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

			// Escape candidate
			$markup = mb_substr ($markup, 0, $offset) . $this->scanner->escape (mb_substr ($markup, $offset, $length)) . mb_substr ($markup, $offset + $length);

			// Skip candidates overlapping the one we just escaped, as they're now escaped too
			while ($i > 0 && $candidates[$i - 1][1] + $candidates[$i - 1][2] > $offset)
				--$i;
		}

		return $markup;
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
	private static function candidate_register (&$candidates, $id, $type, $offset, $length, $params)
	{
		$inserts = array ();

		// Tag can continue an existing group, find compatible candidates
		if ($type === Tag::FLIP || $type === Tag::PULSE || $type === Tag::STEP || $type === Tag::STOP)
		{
			for ($i = 0; $i < count ($candidates); ++$i)
			{
				list ($last_offset, $last_length) = $candidates[$i][1][count ($candidates[$i][1]) - 1];

				// Tag share same id than candidate and doesn't overlap its last tag
				if ($candidates[$i][0] === $id && $offset >= $last_offset + $last_length)
					$inserts[$i] = $type === Tag::PULSE || $type === Tag::STEP;
			}
		}

		// Tag can start a new group, create new candidate
		if ($type === Tag::ALONE || $type === Tag::PULSE || $type === Tag::FLIP || $type === Tag::START)
		{
			$candidates[] = array ($id, array ());
			$inserts[count ($candidates) - 1] = $type !== Tag::ALONE;
		}

		// Append match to compatible candidates
		foreach ($inserts as $i => $incomplete)
			$candidates[$i][1][] = array ($offset, $length, $params, $incomplete);
	}

	/*
	** Resolve first candidate from given list into group if complete, flag
	** matches for removal and remove candidates from list.
	** &candidates:	candidates (id, matches) list
	** &markers:	resolved markers (id, offset, is_first, is_last, params) list
	** &trims:		removal (offset, length) list
	** &min:		highest matched candidate offset
	** return:		true if first candidates was resolved, false otherwise
	*/
	private static function candidate_resolve (&$candidates, &$markers, &$trims, &$min)
	{
		list ($id, $matches) = $candidates[0];

		// Find first match able to complete current candidate group
		for ($close = 0; $close < count ($matches) && $matches[$close][3]; )
			++$close;

		if ($close >= count ($matches))
			return false;

		array_shift ($candidates);

		// Process all candidates from first to closing one
		for ($current = 0; $current <= $close; ++$current)
		{
			list ($offset1, $length1, $params) = $matches[$current];

			// Remove overlapped matches from all remaining candidates
			for ($i = count ($candidates); $i-- > 0; )
			{
				for ($j = count ($candidates[$i][1]); $j-- > 0; )
				{
					list ($offset2, $length2) = $candidates[$i][1][$j];

					if ($offset1 < $offset2 + $length2 && $offset2 < $offset1 + $length1)
					{
						// Drop entire candidate if its first match is removed
						if ($j === 0)
						{
							array_splice ($candidates, $i, 1);

							break;
						}

						// Otherwise just remove overlapped match
						array_splice ($candidates[$i][1], $j, 1);
					}
				}
			}

			// Insert match into markers (ordered by offset) and trimming list
			$i = count ($markers);

			while ($i > 0 && $markers[$i - 1][1] > $offset1)
				--$i;

			array_splice ($markers, $i, 0, array (array ($id, $offset1, $current === 0, $current === $close, $params)));

			$trims[] = array ($offset1, $length1);

			$min = max ($offset1 + $length1, $min);
		}

		return true;
	}
}

?>
