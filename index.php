<?php

define ('CHARSET',	'utf-8');

include ('src/formats/html.php');
include ('src/rules/demo.php');

function	formatHTML ($str)
{
	$depth = 0;
	$offset = 0;
	$out = '';

	while (preg_match ('@[\\s]*(<(/?)[^<>]*?(/?)>|[^<>]+)@s', $str, $matches, PREG_OFFSET_CAPTURE, $offset))
	{
		if ($matches[1][0][0] == '<')
		{
			if ($matches[2][0])
				$depth = max ($depth - 1, 0);

			$out .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '<span style="color: #666666;">' . htmlspecialchars ($matches[1][0], ENT_COMPAT, CHARSET) . '</span><br />';

			if ($matches[2][0] == '' && $matches[3][0] == '')
				$depth = min ($depth + 1, 16);
		}
		else if ($matches[1][0] != '')
			$out .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . htmlspecialchars ($matches[1][0], ENT_COMPAT, CHARSET) . '<br />';

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

if (!isset ($_POST['text']))
	$_POST['text'] = file_get_contents ('res/sample.txt');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<link href="res/yml.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>yML Parser Test Page</title>
	</head>
	<body>
		<div class="box">
			<div class="head">
				Input text:
			</div>
			<div class="body">
				<form action="" method="POST">
					<textarea name="text" rows="10" style="width: 100%;">' . htmlspecialchars ($_POST['text']) . '</textarea>
					<select name="mode">
						<option' . (isset ($_POST['mode']) && $_POST['mode'] != 'code' ? ' selected="selected"' : '') . ' value="yml">Render as HTML</option>
						<option' . (isset ($_POST['mode']) && $_POST['mode'] == 'code' ? ' selected="selected"' : '') . ' value="code">Render as tree</option>
					</select>
					<input type="submit" value="Format" />
				</form>
			</div>
		</div>';

if (isset ($_POST['mode']) && isset ($_POST['text']))
{
	$parser = ymlCompile ($ymlRulesDemo, $ymlParamsDemo);
	$result = nl2br (ymlRender (ymlEncode (htmlspecialchars ($_POST['text'], ENT_COMPAT, CHARSET), $parser), $ymlFormatsHTML));

	if ($_POST['mode'] == 'code')
		$output = formatHTML ($result);
	else
		$output = $result;

	echo '
		<div class="box">
			<div class="head">
				Formatted output:
			</div>
			<div class="body ' . htmlspecialchars ($_POST['mode'], ENT_COMPAT, CHARSET) . '">
				' . $output . '
			</div>
			<div class="body">
				<form action="http://validator.w3.org/check" method="POST" target="_blank">
					<textarea cols="1" name="fragment" rows="1" style="display: none;">' . formatW3C ($result) . '</textarea>
					<input name="charset" type="hidden" value="' . CHARSET . '" />
					<input type="submit" value="Submit to w3c validator" />
				</form>
			</div>
		</div>';

/* FIXME */
	echo '
		<div class="box">
			<div class="head">
				Debug rendering:
			</div>';

	$parser = ymlCompile ($ymlRulesDemo, $ymlParamsDemo);
	$plain = $_POST['text'];

	echo '
			<div class="body">
				<b>plain:</b>
				<div class="code">
					' . htmlspecialchars ($plain, ENT_COMPAT, CHARSET) . '
				</div>
			</div>';

	$token = ymlEncode ($plain, $parser);

	echo '
			<div class="body">
				<b>token:</b>
				<div class="code">' . htmlspecialchars ($token, ENT_COMPAT, CHARSET) . '</div>
			</div>';

	$render = ymlRender ($token, $ymlFormatsHTML);

	echo '
			<div class="body">
				<b>render:</b>
				<div class="code">' . htmlspecialchars ($render, ENT_COMPAT, CHARSET) . '</div>
			</div>';

	$plain = ymlDecode ($token, $parser);

	echo '
			<div class="body">
				<b>plain:</b>
				<div class="code">' . htmlspecialchars ($plain, ENT_COMPAT, CHARSET) . '</div>
			</div>';
/* FIXME */
}

echo '
	</body>
</html>';

?>
