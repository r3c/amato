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
	** return:		(id, offset, captures, is_first, is_last) of next tag
	*/
	public static function next (&$groups, &$cursors)
	{
		// First best group and marker indices in current cursors, by offset
		$best_offset = null;

		foreach ($cursors as $last_group => $last_marker)
		{
			$offset = $groups[$last_group][1][$last_marker][0];

			if ($best_offset === null || $offset <= $best_offset)
			{
				$best_group = $last_group;
				$best_offset = $offset;
			}
		}

		// Process current group and marker
		$best_marker = $cursors[$best_group];

		list ($id, $markers) = $groups[$best_group];
		list ($offset, $captures) = $markers[$best_marker];

		$is_first = $best_marker === 0;
		$is_last = $best_marker + 1 === count ($markers);

		// Append next group to cursors when processing first marker of last group
		if ($best_group === $last_group && $best_marker === 0 && $best_group + 1 < count ($groups))
			$cursors[$best_group + 1] = 0;

		// Remove current group from cursors when processing its last marker
		if (++$cursors[$best_group] >= count ($markers))
			unset ($cursors[$best_group]);

		return array ($id, $offset, $captures, $is_first, $is_last);
	}
}

?>
