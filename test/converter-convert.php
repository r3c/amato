<?php

require ('../src/converter.php');
require ('../src/viewer.php');
include ('../src/formats/html.php');
include ('../src/rules/yml.php');

$converter = new Converter ($ymlRules, $ymlActions);
$token = $converter->convert (file_get_contents ('../res/tag.medium.txt'));

$viewer = new Viewer ($htmlFormats);
$out = $viewer->view ($token);

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
