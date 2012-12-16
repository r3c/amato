<?php

define ('CHARSET',	'iso-8859-1');

include ('../src/umen.php');
include ('../src/converters/markup.php');
include ('../src/encoders/compact.php');
include ('../src/renderers/format.php');
include ('../src/scanners/default.php');

include ('formats/html.php');
include ('markups/yml.php');

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\DefaultScanner ('\\');

$converter = new Umen\MarkupConverter ($encoder, $scanner, $ymlMarkup);
$token = $converter->convert (file_get_contents ('../res/tag.medium.txt'), function ($string) { return htmlspecialchars ($string, ENT_COMPAT, CHARSET); });

$renderer = new Umen\FormatRenderer ($encoder, $htmlFormat);
$out = $renderer->render ($token);

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
