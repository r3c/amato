<?php

define ('CHARSET',	'iso-8859-1');

include ('src/converter.php');
include ('src/viewer.php');
include ('src/formats/html.php');
include ('src/legacy/debug.php');
include ('src/legacy/regexp.php');
include ('src/rules/yml.php');

function	bench ($count, $init, $loop, $stop)
{
	$time = microtime (true);

	eval ($init . 'for ($i = 0; $i < ' . $count . '; ++$i) {' . $loop . '}' . $stop);

	return (int)((microtime (true) - $time) * 1000);
}

$converter = new Converter ($ymlRules, $ymlActions);
$viewer = new Viewer ($htmlFormats);

$out = '';
$i = 1;

mysql_connect ('localhost', 'yaronet', 'yaronet') or die ('connect');
mysql_select_db ('yaronet') or die ('select');

$q = mysql_query ('SELECT post FROM postsx ORDER BY RAND() LIMIT 20');

while (($row = mysql_fetch_assoc ($q)))
{
	$count = 50;

	$plain = $row['post'];
	$token = $converter->convert (htmlspecialchars ($plain, ENT_COMPAT, CHARSET));

	$time1 = bench ($count, 'global $token, $viewer;', '$viewer->view ($token);', '');
	$time2 = bench ($count, 'global $plain;', 'formatRegexp ($plain);', '');

	$out .= '
		<div class="box">
			<div class="head">
				#' . $i++ . ' - Post (' . strlen ($plain) . ' bytes, ' . $count . ' loops): mapa = ' . $time1 . 'ms, regexp = ' . $time2 . 'ms, ratio = ' . (int)(($time2 + 1) * 100 / ($time1 + 1)) . '% - <a href="#" onclick="var node = this.parentNode.parentNode.getElementsByTagName (\'DIV\')[1]; if (node.style.display == \'block\') node.style.display = \'none\'; else node.style.display = \'block\'; return false;">Show</a>
			</div>
			<div class="body mapa" style="display: none;">
				' . $viewer->view ($token) . '
			</div>
		</div>';
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<link href="res/mapa.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>MaPa Format Test</title>
	</head>
	<body>' . $out . '
	</body>
</html>';

?>
