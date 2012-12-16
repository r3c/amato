<?php

define ('CHARSET',	'iso-8859-1');

include ('../src/umen.php');
include ('../src/converters/markup.php');
include ('../src/encoders/compact.php');
include ('../src/renderers/format.php');
include ('../src/scanners/default.php');

include ('../test/formats/html.php');
include ('../test/markups/yml.php');

include ('debug.php');
include ('yml-regexp.php');

function	bench ($count, $init, $loop, $stop)
{
	$time = microtime (true);

	eval ($init . 'for ($i = 0; $i < ' . $count . '; ++$i) {' . $loop . '}' . $stop);

	return (int)((microtime (true) - $time) * 1000);
}

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\DefaultScanner ('\\');
$converter = new Umen\MarkupConverter ($encoder, $scanner, $ymlMarkup);
$renderer = new Umen\FormatRenderer ($encoder, $htmlFormat);

$out = '';
$i = 1;

mysql_connect ('localhost', 'yaronet', 'yaronet') or die ('connect');
mysql_select_db ('yaronet') or die ('select');

$q = mysql_query ('SELECT post FROM postsx ORDER BY RAND() LIMIT 20');

while (($row = mysql_fetch_assoc ($q)))
{
	$count = 50;

	$plain = $row['post'];
	$token = $converter->convert ($plain, function ($string) { return htmlspecialchars ($string, ENT_COMPAT, CHARSET); });

	$time1 = bench ($count, 'global $renderer, $token;', '$renderer->render ($token);', '');
	$time2 = bench ($count, 'global $plain;', 'formatRegexp ($plain);', '');

	$out .= '
		<div class="box">
			<div class="head">
				#' . $i++ . ' - Post (' . strlen ($plain) . ' bytes, ' . $count . ' loops): umen = ' . $time1 . 'ms, regexp = ' . $time2 . 'ms, ratio = ' . (int)(($time2 + 1) * 100 / ($time1 + 1)) . '% - <a href="#" onclick="var node = this.parentNode.parentNode.getElementsByTagName (\'DIV\')[1]; if (node.style.display == \'block\') node.style.display = \'none\'; else node.style.display = \'block\'; return false;">Show</a>
			</div>
			<div class="body umen" style="display: none;">
				' . $renderer->render ($token) . '
			</div>
		</div>';
}

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
