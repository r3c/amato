<?php

include ('../src/lexer.php');

function	debug ($group)
{
	return implode (', ', array_map (function ($range)
	{
		return $range[0] . '-' . ($range[1] !== null ? $range[1] : '~');
	}, $group->ranges));
}

$group = new Group (true);
$group->merge (1, 5);
$group->merge (7, 9);

echo debug ($group) . "<br />\n";

$group = new Group (true);
$group->merge (1, 5);
$group->merge (3, 9);

echo debug ($group) . "<br />\n";

?>
