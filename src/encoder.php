<?php

namespace Amato;

defined ('AMATO') or die;

abstract class Encoder
{
	/*
	** Decode tokenized string into tag groups and plain string.
	** $token:	tokenized string
	** return:	(plain, groups) array or null on parsing error
	*/
	public abstract function decode ($token);

	/*
	** Encode tag groups and plain string into tokenized string.
	** $plain:	plain string
	** $groups:	resolved tag groups
	** return:	tokenized string
	*/
	public abstract function encode ($plain, $groups);

	/*
	** Begin iteration over tag groups by creating cursors array.
	** &groups:	tag groups
	** return:	cursors array
	*/
	public static function begin (&$groups)
	{
		return count ($groups) > 0 ? array (0 => 0) : array ();
	}

	/*
	** Find next tag to be processed in input groups, by order of precedence.
	** Indices of groups and markers for each tag currently being processed are
	** stored in cursors array, and updated by this method.
	** &groups:		tag groups
	** &cursors:	pending (group_index => marker_index) sorted array
	** &next:		(id, offset, captures, is_first, is_last) of next tag if any
	** return:		true if a tag was found, false otherwise
	*/
	public static function next (&$groups, &$cursors, &$next)
	{
		if (count ($cursors) < 1)
			return false;

		// First best group and marker indices in current cursors by offset ascending, marker descending
		$best_offset = null;

		foreach ($cursors as $last_group_index => $last_marker_index)
		{
			$offset = $groups[$last_group_index][1][$last_marker_index][0];

			if ($best_offset === null || ($offset < $best_offset) || ($offset === $best_offset && $last_marker_index >= $best_marker_index))
			{
				$best_marker_index = $last_marker_index;
				$best_offset = $offset;

				$group_index = $last_group_index;
				$marker_index = $last_marker_index;
			}
		}

		// Process current group and marker
		list ($id, $markers) = $groups[$group_index];
		list ($offset, $captures) = $markers[$marker_index];

		$is_first = $marker_index === 0;
		$is_last = $marker_index + 1 === count ($markers);

		// Append next group to cursors when processing first marker of last group
		if ($group_index === $last_group_index && $marker_index === 0 && $group_index + 1 < count ($groups))
			$cursors[$group_index + 1] = 0;

		// Remove current group from cursors when processing its last marker
		if (++$cursors[$group_index] >= count ($markers))
			unset ($cursors[$group_index]);

		$next = array ($id, $offset, $captures, $is_first, $is_last);

		return true;
	}
}

?>
