<html>
	<head>
		<link type="text/css" rel="stylesheet" href="../res/test.css" />
	</head>
	<body>
		<div class="block">
			<legend>Scanner selection</legend>
			Scanner mode:
			<a href="?s=default">default scanner</a>,
			<a href="?s=regexp">regexp scanner</a>
		</div>

<?php

$start = microtime (true);

include ('../src/umen.php');

include ('../sample/syntax/bbcode.php');

Umen\autoload ();

$files = array
(
	'Tagged text - long'	=> 'data/unit-tag-long.txt',
	'Tagged text - medium'	=> 'data/unit-tag-medium.txt',
	'Tagged text - short'	=> 'data/unit-tag-short.txt',
	'Tagged text - tiny'	=> 'data/unit-tag-tiny.txt'
);

switch (isset ($_GET['s']) ? $_GET['s'] : 'default')
{
	case 'default':
		$scanner = new Umen\DefaultScanner ();

		break;

	case 'regexp':
		$scanner = new Umen\RegExpScanner ();

		break;

	default:
		die;
}

foreach ($syntax as $name => $rule)
{
	foreach ($rule['tags'] as $pattern => $options)
		$scanner->assign ($pattern, $name);
}

foreach ($files as $name => $path)
{
	$plain = file_get_contents ($path);
	$tags = array ();

	$plain = $scanner->scan ($plain, function ($match, $offset, $length, $captures) use (&$tags)
	{
		$tags[] = array ($offset, $length, $match, $captures);

		return true;
	}, function ($match) { return false; });

	for ($i = count ($tags) - 1; $i >= 0; --$i)
	{
		list ($offset, $length, $match, $captures) = $tags[$i];

		$hint = 'tag: ' . htmlspecialchars ($match);

		if (count ($captures) > 0)
			$hint .= ', captures: ' . htmlspecialchars (implode (', ', array_map (function ($k, $v) { return "$k = $v"; }, array_keys ($captures), $captures)));

		$plain = substr ($plain, 0, $offset) . '<span class="tag"><span class="hint">' . $hint . '</span>' . substr ($plain, $offset, $length) . '</span>' . substr ($plain, $offset + $length);
	}

	echo '<div class="block"><legend>' . $name . '</legend>' . $plain . '</div>';
}

echo '<div class="block"><legend>Debug</legend>Execution time: ' . (int)((microtime (true) - $start) * 1000) . ' ms</div>';

?>
