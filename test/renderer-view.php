<?php

require ('../src/converter.php');
require ('../src/renderer.php');

include ('../src/formats/html.php');
include ('../src/markups/yml.php');

$converter = new UmenConverter ($ymlMarkup, '\\');
$token = $converter->convert (null, file_get_contents ('../res/tag.medium.txt'));

$renderer = new UmenRenderer ($htmlFormat);
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
