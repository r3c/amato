<?php

define ('CHARSET',	'utf-8');

include ('src/formats/html.php');
require_once ('src/legacy/debug.php');
include ('src/rules/yml.php');

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

$mode = isset ($_POST['mode']) ? $_POST['mode'] : '';
$text = isset ($_POST['text']) ? $_POST['text'] : file_get_contents ('res/sample.txt');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<link href="res/mapa.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>MaPa Test Page</title>
	</head>
	<body>
		<div class="box">
			<div class="head">
				Input text:
			</div>
			<div class="body">
				<form action="" method="POST">
					<textarea name="text" rows="10" style="box-sizing: border-box; width: 100%;">' . htmlspecialchars ($text) . '</textarea>
					<select name="mode">
						<option' . (isset ($mode) && $mode != 'code' && $mode != 'debug' ? ' selected="selected"' : '') . ' value="mapa">Render as HTML</option>
						<option' . (isset ($mode) && $mode == 'code' ? ' selected="selected"' : '') . ' value="code">View render tree</option>
'/* FIXME */.'			<option' . (isset ($mode) && $mode == 'debug' ? ' selected="selected"' : '') . ' value="debug">Debug processing</option>
					</select>
					<input type="submit" value="Format" />
				</form>
			</div>
		</div>';

if ($mode && $text)
{
	$codes = MaPa::compile ($mapaRulesYML, $mapaClassesYML);
/* FIXME */
	if ($mode == 'debug')
	{
		echo '
		<div class="box">
			<div class="head">
				Debug:
			</div>';

		$plain = $text;

		echo '
			<div class="body">
				<b>plain (' . strlen ($plain) . ' characters):</b>
				<div class="code">
					' . htmlspecialchars ($plain, ENT_COMPAT, CHARSET) . '
				</div>
			</div>';

		$token = MaPa::encode ($plain, $codes);

		echo '
			<div class="body">
				<b>token (' . strlen ($token) . ' characters):</b>
				<div class="code">' . htmlspecialchars ($token, ENT_COMPAT, CHARSET) . '</div>
			</div>';

		$render = MaPa::render ($token, $mapaFormatsHTML);

		echo '
			<div class="body">
				<b>render (' . strlen ($render) . ' characters):</b>
				<div class="code">' . htmlspecialchars ($render, ENT_COMPAT, CHARSET) . '</div>
			</div>';

		$plain2 = MaPa::decode ($token, $codes);

		echo '
			<div class="body">
				<b>plain (' . strlen ($plain2) . ' characters):</b>
				<div class="code" style="color: ' . ($plain == $plain2 ? 'green' : 'red') . ';">' . htmlspecialchars ($plain2, ENT_COMPAT, CHARSET) . '</div>
			</div>';
	}
	else
	{
/* FIXME */
		$result = MaPa::render (MaPa::encode (htmlspecialchars ($text, ENT_COMPAT, CHARSET), $codes), $mapaFormatsHTML);

		if ($mode == 'code')
			$output = formatHTML ($result);
		else
			$output = $result;

		echo '
			<div class="box">
				<div class="head">
					Formatted output:
				</div>
				<div class="body ' . htmlspecialchars ($mode, ENT_COMPAT, CHARSET) . '">
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
	}
/* FIXME */
}

profile (null);

echo '
	</body>
</html>';

?>
