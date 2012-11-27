<?php

define ('CHARSET',	'utf-8');

include ('src/parser.php');
include ('src/viewer.php');

include ('src/formats/html.php');
include ('src/markups/yml.php');

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

$actions = array
(
	'print'	=> array ('umen', 'Show actual rendering'),
	'tree'	=> array ('code', 'Display syntax tree'),
	'test'	=> array ('code', 'Render and reverse')
);

$action = isset ($_POST['mode']) ? $_POST['mode'] : 'html';

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<link href="res/umen.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>Universal Markup Engine Test Page</title>
	</head>
	<body>
		<div class="box">
			<div class="head">
				Input string:
			</div>
			<div class="body">
				<form action="" method="POST">
					<textarea name="string" rows="10" style="box-sizing: border-box; width: 100%;">' . htmlspecialchars (isset ($_POST['string']) ? $_POST['string'] : file_get_contents ('res/sample.txt'), ENT_COMPAT, CHARSET) . '</textarea>
					<select name="mode">';

foreach ($actions as $name => $attributes)
{
	echo '
						<option' . ($action === $name ? ' selected="selected"' : '') . ' value="' . htmlspecialchars ($name, ENT_COMPAT, CHARSET) . '">' . htmlspecialchars ($attributes[1], ENT_COMPAT, CHARSET) . '</option>';
}

echo '
					</select>
					using
					<select name="markups">
						<option value="yml">yML</option>
					</select>
					to
					<select name="formats">
						<option value="html">HTML</option>
					</select>
					<input type="submit" value="Format" />
				</form>
			</div>
		</div>';

if (isset ($actions[$action]) && isset ($_POST['string']))
{
	$caption = $actions[$action][0];
	$string = str_replace (array ("\n\r", "\r\n"), "\n", $_POST['string']);

	$parser = new UmenParser ($ymlMarkup, '\\');
	$viewer = new UmenViewer ($htmlFormat);

	$token = $parser->parse (null, htmlspecialchars ($string, ENT_COMPAT, CHARSET));
	$print = $viewer->view ($token);

	switch ($action)
	{
		case 'print':
			$output = $print;

			break;

		case 'test':
			$inverse = htmlspecialchars_decode ($parser->inverse (null, $token), ENT_COMPAT);

			$output =
				'<b>string (' . strlen ($string) . ' characters):</b><br />' . 
				htmlspecialchars ($string, ENT_COMPAT, CHARSET) . '<hr />' .
				'<b>token (' . strlen ($token) . ' characters):</b><br />' .
				htmlspecialchars ($token, ENT_COMPAT, CHARSET) . '<hr />' .
				'<b>print (' . strlen ($print) . ' characters):</b><br />' .
				htmlspecialchars ($print, ENT_COMPAT, CHARSET) . '<hr />' .
				'<b>inverse (' . strlen ($inverse) . ' characters):</b><br />' .
				'<span style="color: ' . (str_replace ('\\', '', $inverse) === str_replace ('\\', '', $string) ? 'green' : 'red') . ';">' . htmlspecialchars ($inverse, ENT_COMPAT, CHARSET) . '</span>';

			break;

		case 'tree':
			$output = formatHTML ($print);

			break;

		default:
			$output = '';

			break;
	}

	echo '
		<div class="box">
			<div class="head">
				Output render:
			</div>
			<div class="body ' . htmlspecialchars ($caption, ENT_COMPAT, CHARSET) . '">
				' . $output . '
			</div>
			<div class="body">
				<form action="http://validator.w3.org/check" method="POST" target="_blank">
					<textarea cols="1" name="fragment" rows="1" style="display: none;">' . formatW3C ($print) . '</textarea>
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
