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
		// Group compatible sequences into array of matches by group candidate
		$candidates = array ();
		$sequences = $this->scanner->find ($markup);
		$trims = array ();

		for ($i = 0; $i < count ($sequences); ++$i)
		{
			list ($key, $offset, $length) = $sequences[$i];

			// Current sequence is an escape sequence
			if ($key === null)
			{
				// Skip sequences following this escape sequence if any
				for ($escape = false; $i + 1 < count ($sequences) && $sequences[$i + 1][1] <= $offset + $length; ++$i)
					$escape = true;

				// Flag escape sequence for removal if it had an effect
				if ($escape)
					$trims[] = array ($offset, $length);

				continue;
			}

			// Current sequence is a tag sequence
			list ($id, $type, $defaults, $convert) = $this->attributes[$key];

			// Build captures array and call conversion callback if any
			$captures = $sequences[$i][3] + $defaults;

			if ($convert !== null && $convert ($type, $captures, $context) === false)
				continue;

			$inserts = array ();

			// Tag sequence can continue an existing group, find compatible candidates
			if ($type === Tag::FLIP || $type === Tag::PULSE || $type === Tag::STEP || $type === Tag::STOP)
			{
				for ($j = 0; $j < count ($candidates); ++$j)
				{
					if ($candidates[$j][0] === $id)
						$inserts[$j] = $type === Tag::PULSE || $type === Tag::STEP;
				}
			}

			// Tag sequence can start a new group, create new candidate
			if ($type === Tag::ALONE || $type === Tag::PULSE || $type === Tag::FLIP || $type === Tag::START)
			{
				$candidates[] = array ($id, array ());
				$inserts[count ($candidates) - 1] = $type !== Tag::ALONE;
			}

			// Append match to compatible candidates
			foreach ($inserts as $index => $incomplete)
				$candidates[$index][1][] = array ($offset, $length, $captures, $incomplete);
		}

		// Resolve compatible candidates into groups and flag them for removal
		$groups = array ();

		while (count ($candidates) > 0)
		{
			list ($id, $matches) = array_shift ($candidates);

			// Find first match able to close current candidate group
			for ($i = 0; $i < count ($matches) && $matches[$i][3]; )
				++$i;

			if ($i >= count ($matches))
				continue;

			// Save candidate and remove other overlapped candidates
			$matches = array_splice ($matches, 0, $i + 1);
			$markers = array ();

			foreach ($matches as $match)
			{
				list ($offset1, $length1, $captures) = $match;

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

				$markers[] = array ($offset1, $captures);
				$trims[] = array ($offset1, $length1);
			}

			$groups[] = array ($id, $markers);
		}

		// Remove groups from markup string and fix offsets
		// FIXME: could be optimized by doing a single pass on $trims
		for ($i = 0; $i < count ($trims); ++$i)
		{
			list ($offset, $length) = $trims[$i];

			foreach ($groups as &$group)
			{
				foreach ($group[1] as &$marker)
				{
					if ($marker[0] > $offset)
						$marker[0] -= $length;
				}
			}

			for ($j = 0; $j < count ($trims); ++$j)
			{
				list ($offset2, $length2) = $trims[$j];

				if ($offset2 > $offset)
					$trims[$j][0] -= $length;
			}

			$markup = mb_substr ($markup, 0, $offset) . mb_substr ($markup, $offset + $length);
		}

		// Encode into tokenized string and return
		return $this->encoder->encode ($markup, $groups);
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
