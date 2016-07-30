<?php

include ('../../src/umen.php');
include ('../../demo/syntax/bbcode.php');

Umen\autoload ();

$encoder = new Umen\CompactEncoder ();
$scanner = new Umen\RegExpScanner ();

$converter = new Umen\SyntaxConverter ($encoder, $scanner, $syntax);
$renderer = new Umen\FormatRenderer (...);

function assert_render ($plain, $html)
{
	global $converter;
	global $renderer;

	$token = $converter->convert ($plain);

	assert ($renderer->render ($token) === $html);
	echo $token . "\n";
}

header ('Content-Type: text/plain; charset=UTF-8');

assert_render ('www.lol.net', '<a href="http://www.lol.net">http://www.lol.net</a>');
assert_render ('http://www.lol.net', '<a href="http://www.lol.net">http://www.lol.net</a>');
assert_render ('[url=http://www.lol.net]blah', '[url=<a href="http://www.lol.net">http://www.lol.net</a>]blah');
assert_render ('[url=http://www.lol.net]blah[/url]', '<a href="http://www.lol.net">blah</a>');

?>
