<html>
	<head>
		<link type="text/css" rel="stylesheet" href="../res/test.css" />
	</head>
	<body>

<?php

include ('../src/lexer.php');

$files = array
(
	'Tagged text - long'	=> '../res/tag.long.txt',
	'Tagged text - medium'	=> '../res/tag.medium.txt',
	'Tagged text - short'	=> '../res/tag.short.txt',
	'Tagged text - tiny'	=> '../res/tag.tiny.txt'
);

$lexer = new Lexer ();
//$lexer->assign ('\\\\(any)', 'escape'); // FIXME
$lexer->assign ("\r\n", 'newline');
$lexer->assign ('[0]', '0+');
$lexer->assign ('[/0]', '0-');
$lexer->assign ('[1]', '1+');
$lexer->assign ('[/1]', '1-');
$lexer->assign ('[2]', '2+');
$lexer->assign ('[/2]', '2-');
$lexer->assign ('[3]', '3+');
$lexer->assign ('[/3]', '3-');
$lexer->assign ('[4]', '4+');
$lexer->assign ('[/4]', '4-');
$lexer->assign ('[5]', '5+');
$lexer->assign ('[/5]', '5-');
$lexer->assign ('[6]', '6+');
$lexer->assign ('[/6]', '6-');
$lexer->assign ('[7]', '7+');
$lexer->assign ('[/7]', '7-');
$lexer->assign ('[8]', '8+');
$lexer->assign ('[/8]', '8-');
$lexer->assign ('[9]', '9+');
$lexer->assign ('[/9]', '9-');
$lexer->assign ('[10]', '10+');
$lexer->assign ('[/10]', '10-');
$lexer->assign ('[11]', '11+');
$lexer->assign ('[/11]', '11-');
$lexer->assign ('[12]', '12+');
$lexer->assign ('[/12]', '12-');
$lexer->assign ('[13]', '13+');
$lexer->assign ('[/13]', '13-');
$lexer->assign ('[14]', '14+');
$lexer->assign ('[/14]', '14-');
$lexer->assign ('[15]', '15+');
$lexer->assign ('[/15]', '15-');
$lexer->assign ('<https{,1}://(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\)*){1,}>', 'url!standalone');
$lexer->assign ('[url]<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\)*){1,}>[/url]', 'url!');
$lexer->assign ('[url=<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\\)*){1,}>]', 'url+');
$lexer->assign ('[urli=<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\\)*){1,}>]', 'url+i');
$lexer->assign ('[/url]', 'url-');
$lexer->assign ('[/urli]', 'url-i');
$lexer->assign ('[align=center]', 'align+c');
$lexer->assign ('[align=left]', 'align+l');
$lexer->assign ('[align=right]', 'align+r');
$lexer->assign ('[/align]', 'align-');
$lexer->assign ('[b]', 'bold+');
$lexer->assign ('[/b]', 'bold-');
$lexer->assign ('[box=(text*)]', 'box+'); // FIXME
$lexer->assign ('[/box]', 'box-');
$lexer->assign ('[center]', 'center+');
$lexer->assign ('[/center]', 'center-');
$lexer->assign ('[yncMd:159]', 'cmd+');
$lexer->assign ('[/yncMd:159]', 'cmd-');
$lexer->assign ('[color=<(0-9A-Fa-f){3}>]', 'color+3');
$lexer->assign ('[color=<(0-9A-Fa-f){6}>]', 'color+6');
$lexer->assign ('[color=#<(0-9A-Fa-f){3}>]', 'color+#3');
$lexer->assign ('[color=#<(0-9A-Fa-f){6}>]', 'color+#6');
$lexer->assign ('[/color]', 'color-');
$lexer->assign ('[em]', 'emphasis+');
$lexer->assign ('[/em]', 'emphasis-');
$lexer->assign ('[flash]<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\\)*){1,}>[/flash]', 'flash!');
$lexer->assign ('[flash=<(0-9){1,}>,<(0-9){1,}>]<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\\)*){1,}>[/flash]', 'flash!size');
$lexer->assign ('[font=<(0-9){1,}>]', 'font+');
$lexer->assign ('[/font]', 'font-');
$lexer->assign ('[hr]', 'hr!');
$lexer->assign ('[i]', 'italic+');
$lexer->assign ('[/i]', 'italic-');
$lexer->assign ('[img=<(0-9){1,}>]<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\\)*){1,}>[/img]', 'img!size');
$lexer->assign ('[img]<(-0-9A-Za-z._~:/?#@!$&\'*+,;=(\\)*){1,}>[/img]', 'img');
$lexer->assign ('[list]', 'list+');
$lexer->assign ('#', 'list/o');
$lexer->assign ('*', 'list/u');
$lexer->assign ('[/list]', 'list-');
$lexer->assign ('[modo]', 'modo+');
$lexer->assign ('[/modo]', 'modo-');
$lexer->assign ('[sondage=<(0-9){1,}>]', 'sondage+');
$lexer->assign ('[pre]', 'pre+');
$lexer->assign ('[/pre]', 'pre-');
$lexer->assign ('[cite]', 'cite+');
$lexer->assign ('[/cite]', 'cite-');
$lexer->assign ('[quote]', 'cite+quote');
$lexer->assign ('[/quote]', 'cite-quote');
$lexer->assign ('./<(0-9){1,}>', 'ref!');
$lexer->assign ('[s]', 'strike+');
$lexer->assign ('[/s]', 'strike-');
$lexer->assign ('!slap (text*)', 'slap!'); // FIXME
$lexer->assign (':D', '0');
$lexer->assign (':\\(', '1');
$lexer->assign (':o', '2');
$lexer->assign (':)', '3');
$lexer->assign (':p', '4');
$lexer->assign (';)', '5');
$lexer->assign ('=)', '6');
$lexer->assign ('%)', '7');
$lexer->assign (':|', '8');
$lexer->assign (':S', '9');
$lexer->assign ('##<(0-9A-Za-z){1,}>##', 'smiley!perso');
$lexer->assign ('#<(0-9A-Za-z){1,}>#', 'smiley!native');
$lexer->assign ('[spoiler]', 'spoiler+');
$lexer->assign ('[/spoiler]', 'spoiler-');
$lexer->assign ('[source=<(0-9){1,}>]', 'source!');
$lexer->assign ('[sub]', 'sub+');
$lexer->assign ('[/sub]', 'sub-');
$lexer->assign ('[sup]', 'sup+');
$lexer->assign ('[/sup]', 'sup-');
$lexer->assign ('[u]', 'underline+');
$lexer->assign ('[/u]', 'underline-');

foreach ($files as $name => $path)
{
	$plain = file_get_contents ($path);
	$tags = array ();

	$lexer->scan ($plain, function ($offset, $length, $match, $captures) use (&$tags)
	{
		$tags[] = array ($offset, $length, $match, $captures);

		return true;
	});

	for ($i = count ($tags) - 1; $i >= 0; --$i)
	{
		list ($offset, $length, $match, $captures) = $tags[$i];

		$hint = 'tag: ' . htmlspecialchars ($match);

		if (count ($captures) > 0)
			$hint .= ', captures: ' . htmlspecialchars (implode (', ', $captures));

		$plain = substr ($plain, 0, $offset) . '<span class="tag"><span class="hint">' . $hint . '</span>' . substr ($plain, $offset, $length) . '</span>' . substr ($plain, $offset + $length);
	}

	echo '<div class="block"><legend>' . $name . '</legend>' . $plain . '</div>';
}

?>
