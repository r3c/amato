<html>
	<head>
		<link type="text/css" rel="stylesheet" href="../res/test.css" />
	</head>
	<body>

<?php

include ('../src/lexer.php');

function	debug ($state, &$known)
{
	for ($i = count ($known); $i-- > 0; )
	{
		if ($known[$i] === $state)
			return '#' . $i . ' ref';
	}

	$known[] = $state;
	$out = '#' . (count ($known) - 1) . ' (-&gt; ' . $state->parents;

	if (count ($state->captures) > 0)
		$out .= ', captures = ' . implode (', ', array_map (function ($k, $v) { return "$k:$v"; }, array_keys ($state->captures), $state->captures));

	if (count ($state->accepts) > 0)
		$out .= ', accepts = ' . implode (', ', $state->accepts);

	$out .= ')<ul>';

	foreach ($state->branches as $branch)
		$out .= '<li>' . implode (', ', array_keys ($branch->hash)) . ' =&gt; ' . debug ($branch->to, $known) . '</li>';

	$out .= '</ul>';

	return $out;
}

function	test ($rules, $checks)
{
	$lexer = new Lexer ();

	echo '<ul class="tree"><li>Rules:<ul>';

	foreach ($rules as $pattern => $match)
	{
		echo '<li>' . $pattern . ' = ' . $match . '</li>';

		$lexer->assign ($pattern, $match);
	}

	echo '</ul></li>';

	$known = array ();
	$start = $lexer->start;

	echo '<li>States:<ul><li>&lt;start&gt; ' . debug ($lexer->start, $known) . '</li></ul></li>';
	echo '<li>Tests:<ul class="test">';

	foreach ($checks as $string => $matches)
	{
		$state = $start;

		foreach (str_split ($string) as $character)
		{
			$stop = true;

			foreach ($state->branches as $branch)
			{
				if (isset ($branch->hash[$character]))
				{
					$state = $branch->to;
					$stop = false;

					break;
				}
			}

			if ($stop)
				break;
		}

		$results = array_map (function ($index) use ($lexer) { return $lexer->matches[$index]; }, $state->accepts);

		if ($results == $matches)
			echo '<li class="ok">string "' . $string . '" resolves to matches ' . json_encode ($results) . '</li>';
		else
			echo '<li class="ko">string "' . $string . '" resolves to matches ' . json_encode ($results) . ', not ' . json_encode ($matches) . '</li>';
	}

	echo '</ul></li></ul>';
}

// Run some unit tests
test (array ('[c=<(0-9A-Fa-f){3}>]' => 'color3', '[c=<(0-9A-Fa-f){6}>]' => 'color6'),
array
(
	'[c=ABC]'		=> array ('color3'),
	'[c=ABG]'		=> array (),
	'[c=AGC]'		=> array (),
	'[c=01234]'		=> array (),
	'[c=012345]'	=> array ('color6'),
	'[c=6789AB]'	=> array ('color6'),
	'[c=CDEFG0]'	=> array ()
));

test (array ('x<(abc)>y' => 1, 'xaz' => 2),
array
(
	'xay'	=> array (1),
	'xby'	=> array (1),
	'xcy'	=> array (1),
	'xaz'	=> array (2),
	'xbz'	=> array (),
	'xcz'	=> array ()
));

test (array ('(ab)y' => 1, '(bc){}z' => 2),
array
(
	'ay'	=> array (1),
	'az'	=> array (),
	'by'	=> array (1),
	'bz'	=> array (2),
	'ccz'	=> array (2),
	'z'		=> array (2)
));

test (array ('x(ab){}y' => 1, 'x(bc){}z' => 2),
array
(
	'xy'		=> array (1),
	'xaababy'	=> array (1),
	'xbbcbz'	=> array (2),
	'xz'		=> array (2),
	'xaz'		=> array (),
	'xcy'		=> array ()
));

test (array ('x(ab){}y' => 1, 'x(b){}z' => 2),
array
(
	'xy'	=> array (1),
	'xay'	=> array (1),
	'xbaby'	=> array (1),
	'xaz'	=> array (),
	'xbaz'	=> array (),
	'xbbz'	=> array (2),
	'xz'	=> array (2)
));

test (array ('xa{}y' => 1, 'xbz' => 2),
array
(
	'xy'	=> array (1),
	'xaay'	=> array (1),
	'xaby'	=> array (),
	'xabz'	=> array (),
	'xbz'	=> array (2),
	'xz'	=> array ()
));

test (array ('xa{}y' => 1, 'xa{}z' => 2),
array
(
	'xy'	=> array (1),
	'xay'	=> array (1),
	'xaay'	=> array (1),
	'xz'	=> array (2),
	'xaz'	=> array (2),
	'xaaz'	=> array (2)
));

test (array ('xay' => 1, 'xby' => 2, 'x(ab){}z' => 3),
array
(
	'xay'	=> array (1),
	'xby'	=> array (2),
	'xz'	=> array (3),
	'xaz'	=> array (3),
	'xbz'	=> array (3),
	'xabaz'	=> array (3)
));

?>

	</body>
</html>
