<?php

define ('CHARSET',	'utf-8');

require ('code/format.php');
require ('code/format.yn.php');
require ('code/regexp.php');

function	bench ($count, $init, $loop, $stop)
{
	$time = microtime (true);

	eval ($init . 'for ($i = 0; $i < ' . $count . '; ++$i) {' . $loop . '}' . $stop);

	return (int)((microtime (true) - $time) * 1000);
}

$hash = formatCompile ($_formatModifiers, $_formatArguments);
$out = '';
$i = 1;

$test = array
(
	'Plain text - long'		=> array
	(
		'count'	=> 5,
		'file'	=> 'test/plain.long.txt'
	),
	'Plain text - medium'	=> array
	(
		'count'	=> 10,
		'file'	=> 'test/plain.medium.txt'
	),
	'Plain text - short'	=> array
	(
		'count'	=> 20,
		'file'	=> 'test/plain.short.txt'
	),
	'Plain text - tiny'		=> array
	(
		'count'	=> 100,
		'file'	=> 'test/plain.tiny.txt'
	),
	'Tagged text - long'	=> array
	(
		'count'	=> 5,
		'file'	=> 'test/tag.long.txt'
	),
	'Tagged text - medium'	=> array
	(
		'count'	=> 10,
		'file'	=> 'test/tag.medium.txt'
	),
	'Tagged text - short'	=> array
	(
		'count'	=> 20,
		'file'	=> 'test/tag.short.txt'
	),
	'Tagged text - tiny'	=> array
	(
		'count'	=> 100,
		'file'	=> 'test/tag.tiny.txt'
	)
);

foreach ($test as $label => $params)
{
	file_exists ($params['file']) or die ('Cannot open input file "' . $params['file'] . '"');

	$str = file_get_contents ($params['file']);

	$time1 = bench ($params['count'], 'global $str; global $hash;', 'formatString ($str, $hash, CHARSET);', '');
	$time2 = bench ($params['count'], 'global $str;', 'formatRegexp ($str);', '');

	$out .= '
		<div class="box">
			<div class="head">
				#' . $i++ . ' - <a href="' . htmlspecialchars ($params['file']) . '">' . htmlspecialchars ($label) . '</a> (' . strlen ($str) . ' bytes, ' . $params['count'] . ' loops): mirari = ' . $time1 . 'ms, regexp = ' . $time2 . 'ms, ratio = ' . (int)(($time2 + 1) * 100 / ($time1 + 1)) . '% - <a href="#" onclick="var node = this.parentNode.parentNode.getElementsByTagName (\'DIV\')[1]; if (node.style.display == \'block\') node.style.display = \'none\'; else node.style.display = \'block\'; return false;">Show</a>
			</div>
			<div class="body" style="display: none;">
				' . formatString ($str, $hash, CHARSET) . '
			</div>
		</div>';
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>Mirari Format Test</title>
	</head>
	<body>' . $out . '
	</body>
</html>';

?>
