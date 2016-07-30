<html>
	<head>
		<link type="text/css" rel="stylesheet" href="../res/test.css" />
	</head>
	<body>
		<ul class="test tree">
<?php

define ('CHARSET',	'iso-8859-1');

include ('../src/umen.php');

include ('../sample/format/html.php');
include ('../sample/syntax/bbcode.php');

Umen\autoload ();

function check ($converter, $renderer, $string)
{
	$token1 = $converter->convert ($string);
	$print1 = $renderer->render ($token1);
	$plain1 = $converter->revert ($token1);
	$token2 = $converter->convert ($plain1);
	$print2 = $renderer->render ($token2);
	$plain2 = $converter->revert ($token2);

	if ($token1 === null || $token1 === '')
		return '<li class="ko">Tokenized string is null</li>';
	else if ($print1 === null || $print1 === '')
		return '<li class="ko">Rendered string is null</li>';
	else if ($token1 !== $token2)
		return '<li class="ko">Tokenized strings are different:<ul class="diff"><li>Original: [' . html_encode ($string) . ']</li><li>Tokenized 1: [' . html_encode ($token1) . ']</li><li>Tokenized 2: [' . html_encode ($token2) . ']</li></ul></li>';
	else if ($print1 !== $print2)
		return '<li class="ko">Rendered strings are different:<ul class="diff"><li>Original: [' . html_encode ($string) . ']</li><li>Rendered 1: [' . html_encode ($print1) . ']</li><li>Rendered 2: [' . html_encode ($print2) . ']</li></ul></li>';
	else if ($plain1 !== $plain2)
		return '<li class="ko">Decoded strings are different:<ul class="diff"><li>Original: [' . html_encode ($string) . ']</li><li>Reverted 1: [' . html_encode ($plain1) . ']</li><li>Reverted 2: [' . html_encode ($plain2) . ']</li></ul></li>';

	$xml = xml_parser_create (CHARSET);

	xml_parser_set_option ($xml, XML_OPTION_TARGET_ENCODING, CHARSET);

	if (!xml_parse ($xml, '<?xml version="1.0" encoding="ISO-8859-1" ?><xml>' . $print1 . '</xml>', true))
		$error = xml_error_string (xml_get_error_code ($xml));
	else
		$error = '';

	xml_parser_free ($xml);

	if ($error !== '')
		return '<li class="ko">Rendered string is not a valid XML (' . $error . '):<ul class="diff"><li>Original: [' . html_encode ($string) . ']</li><li>Rendered: [' . html_encode ($print1) . ']</li></ul></li>';

	return '<li class="ok">OK</li>';
}

function html_encode ($string)
{
	return htmlspecialchars ($string, ENT_COMPAT, CHARSET);
}

$encoder1 = new Umen\CompactEncoder ();
$scanner1 = new Umen\DefaultScanner ('\\');
$scanner2 = new Umen\RegExpScanner ('\\');

$pairs = array
(
	'compact + default'	=> array (new Umen\SyntaxConverter ($encoder1, $scanner1, $syntax), new Umen\FormatRenderer ($encoder1, $format, 'html_encode')),
	'compact + regex'	=> array (new Umen\SyntaxConverter ($encoder1, $scanner2, $syntax), new Umen\FormatRenderer ($encoder1, $format, 'html_encode'))
);

$tests = array
(
	'plain text'	=> 'data/unit-plain-short.txt',
	'mix tags'		=> 'data/unit-tag-short.txt',
	'escape limit'	=> 'data/unit-escape-limit.txt',
	'escape mixed'	=> 'data/unit-escape-mixed.txt',
	'escape nested'	=> 'data/unit-escape-nested.txt'
);

foreach ($pairs as $name1 => $pair)
{
	list ($converter, $renderer) = $pair;

	foreach ($tests as $name2 => $path)
		echo '<li>' . html_encode ($name1) . ' / ' . html_encode ($name2) . ':<ul>' . check ($converter, $renderer, file_get_contents ($path)) . '</ul></li>';
}

?>
		</ul>
	</body>
</html>
