<?php

define ('CHARSET',	'iso-8859-1');

include ('../src/umen.php');

include ('format/html.php');
include ('syntax/yml.php');

Umen\autoload ();

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\DefaultScanner ('\\');

$converter = new Umen\SyntaxConverter ($encoder, $scanner, $syntax);
$token = $converter->convert (file_get_contents ('../res/tag.medium.txt'));

$renderer = new Umen\FormatRenderer ($encoder, $format);
$out = $renderer->render ($token, function ($string) { return htmlspecialchars ($string, ENT_COMPAT, CHARSET); });

if (false)
{
	header ('Content-Type: text/plain; charset=UTF-8');

	echo $token;
}
else
{
	header ('Content-Type: text/html; charset=UTF-8');

	echo $out;
}

?>
