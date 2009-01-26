<?php

define ('VARIABLE',		'0');
define ('ENV_CHARSET',	'utf-8');

require ('code/format.php');
require ('code/format.yn.php');

function	formatHTML ($str)
{
	$str = htmlspecialchars ($str);

	$break = -1;
	$level = 0;

	for ($i = 0; preg_match ('|&lt;(/?).*?(/?)&gt;|', $str, $matches, PREG_OFFSET_CAPTURE, $i); )
	{
		if ($matches[1][0])
		{
			$level = max ($level - 1, 0);
			$break = $level;

		}
		else if (!$matches[2][0])
		{
			$break = $level;
			$level = min ($level + 1, 16);
		}
		else
			$break = $level;

		if ($break != -1)
		{
			$sub = '<br />' . str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $break);
			$str = substr ($str, 0, $matches[0][1]) . $sub . substr ($str, $matches[0][1]);
			$i = $matches[0][1] + strlen ($sub) + 1;
		}
	}

	return $str;
}

function	formatW3C ($str)
{
	return htmlspecialchars ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . ENV_CHARSET . '" />
		<title>Fragment</title>
	</head>
	<body>
		<div>
			' . $str . '
		</div>
	</body>
</html>', ENT_COMPAT, ENV_CHARSET);
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="res/style.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=' . ENV_CHARSET . '" />
		<title>Mirari Format Test</title>
	</head>
	<body>
		<div class="box">
			<div class="head">
				Input BBCode:
			</div>
			<div class="body">
				<form action="" method="POST">
					<textarea cols="80" name="text" rows="16">' . htmlspecialchars ($_POST['text']) . '</textarea><br />
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
	$str = formatString ($_POST['text'], formatCompile ($_formatModifiers, $_formatArguments));

	echo '
		<div class="box">
			<div class="head">
				Formatted output:
			</div>
			<div class="body">
				' . ($_POST['mode'] == 'code' ? formatHTML ($str) : $str) . '<br />
				<br />
				<form action="http://validator.w3.org/check" method="POST" target="_blank">
					<textarea cols="1" name="fragment" rows="1" style="display: none;">' . formatW3C ($str) . '</textarea>
					<input name="charset" type="hidden" value="' . ENV_CHARSET . '" />
					<input type="submit" value="Submit to w3c validator" />
				</form>
			</div>
		</div>';
}

echo '
	</body>
</html>';

?>
