<?php

function	locate ($string, $from, $to = null)
{
	if ($to === null)
		$to = $from;

	$lhs = substr ($string, 0, max ($from, 0));
	$mid = substr ($string, min ($from, strlen ($string)), max ($to - $from, 0));
	$rhs = substr ($string, min ($to, strlen ($string)));

	if (strlen ($lhs) > 25)
		$lhs = '...' . substr ($lhs, strlen ($lhs) - 25);

	if (strlen ($rhs) > 25)
		$rhs = substr ($rhs, 0, 25) . '...';

	return '<span style="font: normal normal normal 11px courier;"><span style="color: gray;">' . htmlspecialchars ($lhs) . '</span><span style="color: red;">' . ($from < $to ? '[' : '|') . '</span><span style="color: blue;">' . htmlspecialchars ($mid) . '</span><span style="color: red;">' . ($from < $to ? ']' : '') . '</span><span style="color: gray;">' . htmlspecialchars ($rhs) . '</span></span>';
}

?>
