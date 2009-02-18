<?php

define ('CHARSET',	'utf-8');

require ('code/format.php');
require ('code/format.mirari.php');

function	formatHTML ($str)
{
	$offset = 0;
	$level = 0;

	while (preg_match ('@[\\s]*(<(/?)[^<>]*?(/?)>|[^<>]+)@s', $str, $matches, PREG_OFFSET_CAPTURE, $offset))
	{
		if ($matches[1][0][0] == '<')
		{
			if ($matches[2][0])
				$level = max ($level - 1, 0);

			$out .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . '<span style="color: #666666;">' . htmlspecialchars ($matches[1][0], ENT_COMPAT, CHARSET) . '</span><br />';

			if ($matches[2][0] == '' && $matches[3][0] == '')
				$level = min ($level + 1, 16);
		}
		else if ($matches[1][0] != '')
			$out .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . htmlspecialchars ($matches[1][0], ENT_COMPAT, CHARSET) . '<br />';

		$offset = $matches[0][1] + strlen ($matches[0][0]);
	}

	return $out;
}

function	formatW3C ($str)
{
	return htmlspecialchars ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>Fragment</title>
	</head>
	<body>
		<div>
			' . $str . '
		</div>
	</body>
</html>', ENT_COMPAT, CHARSET);
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>Mirari Format Test</title>
	</head>
	<body>
		<div class="box">
			<div class="head">
				<a href="#" onclick="var node = this.parentNode.parentNode.getElementsByTagName (\'DIV\')[1]; if (node.style.display == \'block\') node.style.display = \'none\'; else node.style.display = \'block\'; return false;" style="float: right;">Display help</a> Input BBCode:
			</div>
			<div class="body" style="display: none;">
				Available tags:<br />
				<br />
				<ul>
					<li>[align=left]...[/align], [align=center]...[/align], [align=right]...[/align]: align paragraph</li>
					<li>[b]...[/b]: bold text</li>
					<li>[color=FF0000]...[/color]: change text color</li>
					<li>[color=FF0000,bg]...[/color]: change background color</li>
					<li>[float=left]...[/float], [float=right]...[/float]: make floatable block</li>
					<li>[img]...[/img], [img=32,32]...[/img]: insert image</li>
					<li>[i]...[/i]: make text italic</li>
					<li>[hr]: insert horizontal line</li>
					<li>[list]...[/list]: make list (* or # to start new item)</li>
					<li>[size=200]...[/size]: change text size</li>
					<li>[s]...[/s]: strikeout text</li>
					<li>[sub]...[/sub]: make text subscript</li>
					<li>[sup]...[/sup]: make text superscript</li>
					<li>[table]...[/table], [table=50]...[/table]: make table (| to create colum, ^ to create header, $ to finish row)</li>
					<li>[u]...[/u]: underline text</li>
					<li>[url]...[/url], [url=...]...[/url]: make text an hyperlink</li>
				</ul>
			</div>
			<div class="body">
				<form action="" method="POST">
					<textarea name="text" rows="16" style="width: 100%;">' . htmlspecialchars ($_POST['text']) . '</textarea>
					<br />
					<select name="mode">
						<option' . ($_POST['mode'] != 'code' ? ' selected="selected"' : '') . ' value="html">Display result as HTML</option>
						<option' . ($_POST['mode'] == 'code' ? ' selected="selected"' : '') . ' value="code">Display result as code</option>
					</select>
					<input type="submit" value="Format" />
				</form>
			</div>
		</div>';

if (isset ($_POST['mode']) && isset ($_POST['text']))
{
	$str = formatString ($_POST['text'], formatCompile ($_formatModifiers, $_formatArguments), CHARSET);

	if ($_POST['mode'] == 'code')
	{
		$output = formatHTML ($str);
		$style = 'font: normal normal normal 11px monospace;';
	}
	else
	{
		$output = $str;
		$style = '';
	}

	echo '
		<div class="box">
			<div class="head">
				Formatted output:
			</div>
			<div class="body">
				<div style="' . $style . '">' . $output . '</div>
				<br />
				<form action="http://validator.w3.org/check" method="POST" target="_blank">
					<textarea cols="1" name="fragment" rows="1" style="display: none;">' . formatW3C ($str) . '</textarea>
					<input name="charset" type="hidden" value="' . CHARSET . '" />
					<input type="submit" value="Submit to w3c validator" />
				</form>
			</div>
		</div>';
}

echo '
	</body>
</html>';

?>
