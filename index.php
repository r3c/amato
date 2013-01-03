<?php

define ('CHARSET',	'utf-8');

require ('src/umen.php');

Umen\autoload ();

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

function	getOptions ($options, $selected)
{
	$html = '';

	foreach ($options as $value => $caption)
		$html .= '<option' . ($selected === $value ? ' selected="selected"' : '') . ' value="' . htmlspecialchars ($value, ENT_COMPAT, CHARSET) . '">' . htmlspecialchars ($caption, ENT_COMPAT, CHARSET) . '</option>';

	return $html;
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<link href="res/umen.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . CHARSET . '" />
		<title>Universal Markup Engine v' . htmlspecialchars (UMEN, ENT_COMPAT, CHARSET) . ' - Test Page</title>
	</head>
	<body>
		<div class="box">
			<h1>Input string:</h1>
			<div class="body">
				<form action="" method="POST">
					<textarea name="string" rows="10" style="box-sizing: border-box; width: 100%;">' . htmlspecialchars (isset ($_POST['string']) ? $_POST['string'] : file_get_contents ('res/sample.txt'), ENT_COMPAT, CHARSET) . '</textarea>
					<div class="buttons" id="actions">
						Display
						<select name="action">' . getOptions (array ('print' => 'actual rendering', 'tree' => 'syntax tree', 'debug' => 'debug mode'), isset ($_POST['action']) ? $_POST['action'] : 'html') . '</select>
						using
						<select name="markup">' . getOptions (array ('yml' => 'yML', 'test' => 'Test'), isset ($_POST['markup']) ? $_POST['markup'] : null) . '</select>
						to
						<select name="format">' . getOptions (array ('html' => 'HTML'), isset ($_POST['format']) ? $_POST['format'] : null) . '</select>
						<input type="submit" value="Submit" />
						<input onclick="var i = document.getElementById(\'options_input\'), p = document.getElementById(\'options_panel\'); if (i.value) { i.value = \'\'; p.style.display = \'none\'; } else { i.value = \'1\'; p.style.display = \'block\'; }" type="button" value="Options" />
						<input id="options_input" name="options" type="hidden" value="' . htmlspecialchars (isset ($_POST['options']) ? $_POST['options'] : '', ENT_COMPAT, CHARSET)  . '" />
					</div>
					<div class="buttons" id="options_panel" style="display: ' . (isset ($_POST['options']) && $_POST['options'] ? 'block' : 'none') . ';">
						Parse using
						<select name="scanner">' . getOptions (array ('default' => 'default', 'regex' => 'regexp'), isset ($_POST['scanner']) ? $_POST['scanner'] : null) . '</select>
						scanner and
						<select name="converter">' . getOptions (array ('markup' => 'markup'), isset ($_POST['converter']) ? $_POST['converter'] : null) . '</select>
						converter, store using
						<select name="encoder">' . getOptions (array ('compact' => 'compact', 'concat' => 'concat', 'json' => 'json', 'sleep' => 'sleep'), isset ($_POST['encoder']) ? $_POST['encoder'] : null) . '</select>
						encoding, render with
						<select name="renderer">' . getOptions (array ('format' => 'format'), isset ($_POST['renderer']) ? $_POST['renderer'] : null) . '</select>
						renderer
					</div>
				</form>
			</div>
		</div>';

if (isset ($_POST['action']) && isset ($_POST['string']))
{
	$string = str_replace (array ("\n\r", "\r\n"), "\n", $_POST['string']);

	switch (isset ($_POST['encoder']) ? $_POST['encoder'] : null)
	{
		case 'compact':
			$encoder = new Umen\CompactEncoder ();

			break;

		case 'concat':
			$encoder = new Umen\ConcatEncoder ();

			break;

		case 'json':
			$encoder = new Umen\JSONEncoder ();

			break;

		case 'sleep':
			$encoder = new Umen\SleepEncoder ();

			break;

		default:
			throw new Exception ('invalid encoder');
	}

	switch (isset ($_POST['scanner']) ? $_POST['scanner'] : null)
	{
		case 'default':
			$scanner = new Umen\DefaultScanner ();

			break;

		case 'regex':
			$scanner = new Umen\RegExpScanner ();

			break;

		default:
			throw new Exception ('invalid scanner');
	}

	switch (isset ($_POST['converter']) ? $_POST['converter'] : null)
	{
		case 'markup':
			switch (isset ($_POST['markup']) ? $_POST['markup'] : null)
			{
				case 'test':
					require ('test/markups/test.php');

					break;

				case 'yml':
					require ('test/markups/yml.php');

					break;

				default:
					throw new Exception ('invalid markup');
			}

			$converter = new Umen\MarkupConverter ($encoder, $scanner, $markup);

			break;

		default:
			throw new Exception ('invalid converter');
	}

	switch (isset ($_POST['renderer']) ? $_POST['renderer'] : null)
	{
		case 'format':
			switch (isset ($_POST['format']) ? $_POST['format'] : null)
			{
				case 'html':
					include ('test/formats/html.php');

					break;

				default:
					throw new Exception ('invalid format');
			}
		
			$renderer = new Umen\FormatRenderer ($encoder, $format);

			break;

		default:
			throw new Exception ('invalid renderer');
	}

	$token = $converter->convert ($string);
	$print = $renderer->render ($token, function ($plain) { return htmlspecialchars ($plain, ENT_COMPAT, CHARSET); });

	switch ($_POST['action'])
	{
		case 'debug':
			$inverse = $converter->revert ($token);

			$output =
				'<h2>string (' . strlen ($string) . ' characters):</h2>' . 
				'<div class="code">' . htmlspecialchars ($string, ENT_COMPAT, CHARSET) . '</div><hr />' .
				'<h2>token (' . strlen ($token) . ' characters):</h2>' .
				'<div class="code">' . htmlspecialchars ($token, ENT_COMPAT, CHARSET) . '</div><hr />' .
				'<h2>print (' . strlen ($print) . ' characters):</h2>' .
				'<div class="code">' . htmlspecialchars ($print, ENT_COMPAT, CHARSET) . '</div><hr />' .
				'<h2>inverse (' . strlen ($inverse) . ' characters):</h2>' .
				'<div class="code" style="color: ' . (str_replace ('\\', '', $inverse) === str_replace ('\\', '', $string) ? 'green' : 'red') . ';">' . htmlspecialchars ($inverse, ENT_COMPAT, CHARSET) . '</div>';

			break;

		case 'print':
			$output = '<div class="umen">' . $print . '</div>';

			break;

		case 'tree':
			$output = '<div class="code">' . formatHTML ($print) . '</div>';

			break;

		default:
			$output = '';

			break;
	}

	echo '
		<div class="box">
			<h1>Output render:</h1>
			<div class="body">
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
