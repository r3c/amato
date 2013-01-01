<html>
	<head>
		<link type="text/css" rel="stylesheet" href="../res/test.css" />
	</head>
	<body>
		<ul class="test tree">
<?php

define ('CHARSET',	'iso-8859-1');

include ('../src/umen.php');

include ('formats/html.php');
include ('markups/yml.php');

Umen\autoload ();

function	check ($converter, $renderer, $string)
{
	$token1 = $converter->convert ($string);
	$print1 = $renderer->render ($token1, 'html_encode');
	$plain1 = $converter->revert ($token1);
	$token2 = $converter->convert ($plain1);
	$print2 = $renderer->render ($token2, 'html_encode');
	$plain2 = $converter->revert ($token2);

	if ($token1 !== $token2)
		return '<li class="ko">Tokenized strings are different:<ul class="diff"><li>[' . html_encode ($string) . ']</li><li>[' . html_encode ($token1) . ']</li><li>[' . html_encode ($token2) . ']</li></ul></li>';
	else if ($print1 !== $print2)
		return '<li class="ko">Rendered strings are different:<ul class="diff"><li>[' . html_encode ($string) . ']</li><li>[' . html_encode ($print1) . ']</li><li>[' . html_encode ($print2) . ']</li></ul></li>';
	else if ($plain1 !== $plain2)
		return '<li class="ko">Decoded strings are different:<ul class="diff"><li>[' . html_encode ($string) . ']</li><li>[' . html_encode ($plain1) . ']</li><li>[' . html_encode ($plain2) . ']</li></ul></li>';

	$xml = xml_parser_create (CHARSET);

	xml_parser_set_option ($xml, XML_OPTION_TARGET_ENCODING, CHARSET);

	if (!xml_parse ($xml, '<?xml version="1.0" encoding="ISO-8859-1" ?><xml>' . $print1 . '</xml>', true))
		$error = xml_error_string (xml_get_error_code ($xml));
	else
		$error = '';

	xml_parser_free ($xml);

	if ($error !== '')
		return '<li class="ko">Rendered string is not a valid XML (' . $error . '):<ul class="diff"><li>[' . html_encode ($string) . ']</li><li>[' . html_encode ($print1) . ']</li></ul></li>';

	return '<li class="ok">OK</li>';
}

function	html_encode ($string)
{
	return htmlspecialchars ($string, ENT_COMPAT, CHARSET);
}

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\DefaultScanner ('\\');
$converter = new Umen\MarkupConverter ($encoder, $scanner, $markup);
$renderer = new Umen\FormatRenderer ($encoder, $format);

mysql_connect ('localhost', 'yaronet', 'yaronet') or die ('connect');
mysql_select_db ('yaronet') or die ('select');

$limit = isset ($_GET['limit']) ? (int)$_GET['limit'] : 50;

$q = mysql_query ('SELECT sujet, num, post FROM postsx ORDER BY RAND() LIMIT ' . $limit);
//$q = mysql_query ('SELECT sujet, num, post FROM postsx WHERE sujet = 102931 AND num = 165');

while (($row = mysql_fetch_assoc ($q)))
{
	$href = 'http://www.yaronet.com/posts.php?s=' . $row['sujet'] . '&p=' . (int)((($row['num'] - 1) / 30) + 1) . '&h=' . ($row['num'] - 1) . '#' . ($row['num'] - 1);

	echo '<li>Topic ' . $row['sujet'] . ' post <a href="' . html_encode ($href) . '">#' . ($row['num'] - 1) . '</a>:<ul>' . check ($converter, $renderer, $row['post']) . '</ul></li>';
}

?>
		</ul>
	</body>
</html>
