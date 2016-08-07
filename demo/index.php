<?php

define ('CHARSET', 'utf-8');

header ('Content-Type: text/html; charset=' . CHARSET);

mb_internal_encoding (CHARSET);

require ('../src/amato.php');

Amato\autoload ();

function escape ($input)
{
	return htmlspecialchars ($input, ENT_COMPAT, CHARSET);
}

function format_html ($input)
{
	$depth = 0;
	$offset = 0;
	$out = '';

	while (preg_match ('@[\\s]*(<(/?)[^<>]*?(/?)>|[^<>]+)@s', $input, $matches, PREG_OFFSET_CAPTURE, $offset))
	{
		if ($matches[1][0][0] == '<')
		{
			if ($matches[2][0])
				$depth = max ($depth - 1, 0);

			$out .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '<span style="color: #666666;">' . escape ($matches[1][0]) . '</span><br />';

			if ($matches[2][0] == '' && $matches[3][0] == '')
				$depth = min ($depth + 1, 16);
		}
		else if ($matches[1][0] != '')
			$out .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . escape ($matches[1][0]) . '<br />';

		$offset = $matches[0][1] + strlen ($matches[0][0]);
	}

	return $out;
}

function format_w3c ($input)
{
	return escape ('<!DOCTYPE html>
<html>
	<head>
		<meta charset="' . CHARSET . '" /> 
		<title>Fragment</title>
	</head>
	<body>
		<div>
			' . $input . '
		</div>
	</body>
</html>');
}

function render_options ($options, $selected)
{
	$html = '';

	foreach ($options as $value => $caption)
		$html .= '<option' . ($selected === $value ? ' selected="selected"' : '') . ' value="' . escape ($value) . '">' . escape ($caption) . '</option>';

	return $html;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<link href="res/amato.css" rel="stylesheet" type="text/css" />
		<link href="res/demo.css" rel="stylesheet" type="text/css" />
		<title>Agnostic Markup Tokenizer v<?php echo escape (AMATO); ?> Demo</title>
	</head>
	<body>
		<div class="window">
			<h2>Markup</h2>
			<div class="body">
				<form action="" method="POST">
					<textarea name="markup" rows="10" style="box-sizing: border-box; width: 100%;"><?php echo escape (isset ($_POST['markup']) ? $_POST['markup'] : file_get_contents ('data/demo.txt')); ?></textarea>
					<div class="buttons" id="actions">
						Convert
						<select name="syntax"><?php echo render_options (array ('bbcode' => 'BBCode', 'wiki' => 'Wiki Markup'), isset ($_POST['syntax']) ? $_POST['syntax'] : null); ?></select>
						into
						<select name="format"><?php echo render_options (array ('html' => 'HTML'), isset ($_POST['format']) ? $_POST['format'] : null); ?></select>
						and
						<select name="action"><?php echo render_options (array ('print' => 'print result', 'html' => 'show HTML', 'debug' => 'debug cycle'), isset ($_POST['action']) ? $_POST['action'] : 'print'); ?></select>
						<input type="submit" value="Submit" />
						<input onclick="var p = document.getElementById('options_panel'); p.style.display = p.style.display === 'none' ? 'block' : 'none';" type="button" value="Options" />
					</div>
					<div class="buttons" id="options_panel" style="display: <?php echo (isset ($_POST['options']) && $_POST['options'] ? 'block' : 'none'); ?>;">
						Tokenize using
						<select name="scanner"><?php echo render_options (array ('preg' => 'preg'), isset ($_POST['scanner']) ? $_POST['scanner'] : null); ?></select>
						scanner and
						<select name="converter"><?php echo render_options (array ('tag' => 'tag'), isset ($_POST['converter']) ? $_POST['converter'] : null); ?></select>
						converter, serialize using
						<select name="encoder"><?php echo render_options (array ('compact' => 'compact', 'concat' => 'concat', 'json' => 'json', 'sleep' => 'sleep'), isset ($_POST['encoder']) ? $_POST['encoder'] : null); ?></select>
						encoder, render with
						<select name="renderer"><?php echo render_options (array ('format' => 'format'), isset ($_POST['renderer']) ? $_POST['renderer'] : null); ?></select>
						renderer
					</div>
				</form>
			</div>
		</div>
<?php

if (isset ($_POST['action']) && isset ($_POST['markup']))
{
	switch (isset ($_POST['encoder']) ? $_POST['encoder'] : null)
	{
		case 'compact':
			$encoder = new Amato\CompactEncoder ();

			break;

		case 'concat':
			$encoder = new Amato\ConcatEncoder ();

			break;

		case 'json':
			$encoder = new Amato\JSONEncoder ();

			break;

		case 'sleep':
			$encoder = new Amato\SleepEncoder ();

			break;

		default:
			throw new Exception ('invalid encoder');
	}

	switch (isset ($_POST['scanner']) ? $_POST['scanner'] : null)
	{
		case 'preg':
			$scanner = new Amato\PregScanner ();

			break;

		default:
			throw new Exception ('invalid scanner');
	}

	switch (isset ($_POST['converter']) ? $_POST['converter'] : null)
	{
		case 'tag':
			switch (isset ($_POST['syntax']) ? $_POST['syntax'] : null)
			{
				case 'bbcode':
					require ('config/syntax/bbcode.php');

					break;

				case 'wiki':
					require ('config/syntax/wiki.php');

					break;

				default:
					throw new Exception ('invalid syntax');
			}

			$converter = new Amato\TagConverter ($encoder, $scanner, $syntax);

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
					include ('config/format/html.php');

					break;

				default:
					throw new Exception ('invalid format');
			}
		
			$renderer = new Amato\FormatRenderer ($encoder, $format, 'escape');

			break;

		default:
			throw new Exception ('invalid renderer');
	}

	$markup = str_replace ("\r", '', $_POST['markup']);
	$token = $converter->convert ($markup);
	$render = $renderer->render ($token);

	switch ($_POST['action'])
	{
		case 'debug':
			$revert = $converter->revert ($token);

			$output =
				'<h3>markup string (' . mb_strlen ($markup) . ' characters):</h3>' . 
				'<div class="code">' . escape ($markup) . '</div><hr />' .
				'<h3>token string (' . mb_strlen ($token) . ' characters):</h3>' .
				'<div class="code">' . escape ($token) . '</div><hr />' .
				'<h3>render string (' . mb_strlen ($render) . ' characters):</h3>' .
				'<div class="code">' . escape ($render) . '</div><hr />' .
				'<h3>revert string (' . mb_strlen ($revert) . ' characters):</h3>' .
				'<div class="code" style="color: ' . ($markup === $revert ? 'green' : 'red') . ';">' . escape ($revert) . '</div>';

			break;

		case 'html':
			$output = '<div class="code">' . format_html ($render) . '</div>';

			break;

		case 'print':
			$output = '<div class="amato">' . $render . '</div>';

			break;

		default:
			$output = '';

			break;
	}

?>
		<div class="window">
			<h2>Output</h2>
			<div class="body">
				<?php echo $output; ?>
			</div>
			<div class="body">
				<form action="http://validator.w3.org/check" method="POST" enctype="multipart/form-data" target="_blank">
					<textarea name="fragment" style="display: none;"><?php echo format_w3c ($render); ?></textarea>
					<input name="charset" type="hidden" value="<?php echo CHARSET; ?>" />
					<input type="submit" value="Submit to w3c validator" />
				</form>
			</div>
		</div>
<?php

}

?>
	</body>
</html>
