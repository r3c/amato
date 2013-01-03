<?php

define ('CHARSET',	'utf-8');

include ('../src/umen.php');

include ('../test/formats/html.php');
include ('../test/markups/yml.php');

Umen\autoload ();

include ('debug.php');
include ('yml-regexp.php');

function	bench ($count, $init, $loop, $stop)
{
	$time = microtime (true);

	eval ($init . 'for ($i = 0; $i < ' . $count . '; ++$i) {' . $loop . '}' . $stop);

	return (int)((microtime (true) - $time) * 1000);
}

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\DefaultScanner ();
$converter = new Umen\MarkupConverter ($encoder, $scanner, $markup);
$renderer = new Umen\FormatRenderer ($encoder, $format);

$out = '';
$i = 1;

$test = array
(
	'Plain text - long'		=> array
	(
		'count'	=> 5,
		'file'	=> '../res/plain.long.txt'
	),
	'Plain text - medium'	=> array
	(
		'count'	=> 10,
		'file'	=> '../res/plain.medium.txt'
	),
	'Plain text - short'	=> array
	(
		'count'	=> 20,
		'file'	=> '../res/plain.short.txt'
	),
	'Plain text - tiny'		=> array
	(
		'count'	=> 100,
		'file'	=> '../res/plain.tiny.txt'
	),
	'Tagged text - long'	=> array
	(
		'count'	=> 5,
		'file'	=> '../res/tag.long.txt'
	),
	'Tagged text - medium'	=> array
	(
		'count'	=> 10,
		'file'	=> '../res/tag.medium.txt'
	),
	'Tagged text - short'	=> array
	(
		'count'	=> 20,
		'file'	=> '../res/tag.short.txt'
	),
	'Tagged text - tiny'	=> array
	(
		'count'	=> 100,
		'file'	=> '../res/tag.tiny.txt'
	)
);

foreach ($test as $label => $params)
{
	file_exists ($params['file']) or die ('Cannot open input file "' . $params['file'] . '"');

	$plain = file_get_contents ($params['file']);
	$token = $converter->convert ($plain);

	$time1 = bench ($params['count'], 'global $renderer, $token;', '$renderer->render ($token);', '');
	$time2 = bench ($params['count'], 'global $plain;', 'formatRegexp (htmlspecialchars ($plain));', '');

	$out .= '
		<div class="box">
			<h1>#' . $i++ . ' - <a href="' . htmlspecialchars ($params['file']) . '">' . htmlspecialchars ($label) . '</a> (' . strlen ($plain) . ' bytes, ' . $params['count'] . ' loops): umen = ' . $time1 . 'ms, regexp = ' . $time2 . 'ms, ratio = ' . (int)(($time2 + 1) * 100 / ($time1 + 1)) . '% - <a href="#" onclick="var node = this.parentNode.parentNode.getElementsByTagName (\'DIV\')[0]; if (node.style.display == \'block\') node.style.display = \'none\'; else node.style.display = \'block\'; return false;">Show</a></h1>
			<div class="body umen" style="display: none;">
				' . $renderer->render ($token, function ($string) { return htmlspecialchars ($string, ENT_COMPAT, CHARSET); }) . '
			</div>
		</div>';
}

profile (null);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="../res/style.css" rel="stylesheet" type="text/css" />
		<link href="../res/umen.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>Umen Bench</title>
	</head>
	<body>' . $out . '
	</body>
</html>';

?>
