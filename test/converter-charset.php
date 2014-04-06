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

function	html_encode ($string)
{
	return htmlspecialchars ($string, ENT_COMPAT, mb_internal_encoding ());
}

function	run ($converter, $renderer, $file, $charset)
{
	echo '<li>Using ' . $file . ' with charset ' . $charset . ':<ul>';

	mb_internal_encoding ($charset);

	$token = $converter->convert (file_get_contents ($file));
	$render = $renderer->render ($token, 'html_encode');
	$plain = $converter->revert ($token);

	echo '<li>' . html_encode ($token) . '</li>';
	echo '<li>' . html_encode ($render) . '</li>';
	echo '<li>' . html_encode ($plain) . '</li>';
	echo '</ul>';
}

echo '<li>DefaultScanner:<ul>';

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\DefaultScanner ('\\');
$converter = new Umen\SyntaxConverter ($encoder, $scanner, $syntax);
$renderer = new Umen\FormatRenderer ($encoder, $format);

run ($converter, $renderer, 'txt/charset.iso-8859-1.txt', 'iso-8859-1');
run ($converter, $renderer, 'txt/charset.utf-8.txt', 'utf-8');

echo '</ul></li>';

echo '<li>RegExpScanner:<ul>';

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\RegExpScanner ('\\');
$converter = new Umen\SyntaxConverter ($encoder, $scanner, $syntax);
$renderer = new Umen\FormatRenderer ($encoder, $format);

run ($converter, $renderer, 'txt/charset.iso-8859-1.txt', 'iso-8859-1');
run ($converter, $renderer, 'txt/charset.utf-8.txt', 'utf-8');

echo '</ul></li>';

?>
		</ul>
	</body>
</html>
