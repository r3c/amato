<?php

require ('../src/parser.php');
require ('../src/viewer.php');

include ('../src/formats/html.php');
include ('../src/markups/yml.php');

$parser = new UmenParser ($ymlMarkup, '\\');
$token = $parser->parse (null, file_get_contents ('../res/tag.medium.txt'));

$viewer = new UmenViewer ($htmlFormat);
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
