<html>
	<head>
		<link type="text/css" rel="stylesheet" href="../res/test.css" />
	</head>
	<body>

<?php

include ('../src/umen.php');

include ('markups/yml.php');

Umen\autoload ();

$files = array
(
	'Tagged text - long'	=> '../res/tag.long.txt',
	'Tagged text - medium'	=> '../res/tag.medium.txt',
	'Tagged text - short'	=> '../res/tag.short.txt',
	'Tagged text - tiny'	=> '../res/tag.tiny.txt'
);

$scanner = new Umen\DefaultScanner ('\\');

foreach ($markup as $name => $rule)
{
	foreach ($rule['tags'] as $pattern => $options)
		$scanner->assign ($pattern, $name);
}

foreach ($files as $name => $path)
{
	$plain = file_get_contents ($path);
	$tags = array ();

	$plain = $scanner->scan ($plain, function ($offset, $length, $match, $captures) use (&$tags)
	{
		$tags[] = array ($offset, $length, $match, $captures);

		return true;
	});

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

?>
