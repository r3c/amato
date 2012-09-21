<?php

include ('src/legacy/debug.php');

function	test ($plain, $tree)
{
	$cursors = array ();
	$length = strlen ($plain);

	for ($i = 0; $i <= $length; ++$i)
	{
		$character = $i < $length ? $plain[$i] : null;
		$count = array_push ($cursors, new yMLCursor ($tree, $i));
echo $character;
		for ($j = 0; $j < $count; ++$j)
		{
			$cursor =& $cursors[$j];

			if (!$cursor->move ($character, $i + 1))
			{
				if (isset ($cursor->match))
				{
					for ($k = 0; $k < $j; ++$k)
						$cursors[$k]->kill ();

echo "<br />matched: name " . $cursor->match[0] . ", action " . $cursor->match[1] . ", start: " . $cursor->start . ", length: " . $cursor->length . ", params: " . json_encode ($cursor->params) . "<br />";
echo locate ($plain, $cursor->start, $cursor->start + $cursor->length) . "<br />";

					$plain = substr_replace ($plain, '', $cursor->start, $cursor->length);

					$length = strlen ($plain);
					$i -= $cursor->length;
echo locate ($plain, $i) . "<br />";

					for ($k = $j + 1; $k < $count; ++$k)
						$cursors[$k]->start -= $cursor->length;
				}

				$cursor->kill ();
			}
		}

		// Flush invalidated cursors
		while (count ($cursors) > 0 && !$cursors[0]->keep ())
			array_shift ($cursors);
	}
}

include ('src/formats/html.php');
include ('src/rules/demo.php');

$rules = array
(
	'1'	=> array
	(
		'tags'	=> array
		(
			'abcde'	=> YML_TYPE_SINGLE
		)
	),
	'2'	=> array
	(
		'tags'	=> array
		(
			'cd'	=> YML_TYPE_SINGLE
		)
	)
);

//$codes = yML::compile ($ymlRulesDemo, $ymlClassesDemo);
$codes = yML::compile ($rules, array ());
//$plain = file_get_contents ('res/sample.txt');
$plain = 'xabcdefy';

echo yML::encode ($plain, $codes);

?>
