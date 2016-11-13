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
	$html = '';
	$index = 0;

	while (preg_match ('@[\\s]*(<(/?)[^<>]*?(/?)>|[^<>]+)@s', $input, $matches, PREG_OFFSET_CAPTURE, $index))
	{
		if ($matches[1][0][0] == '<')
		{
			if ($matches[2][0])
				$depth = max ($depth - 1, 0);

			$html .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '<span style="color: #800;">' . escape ($matches[1][0]) . '</span><br />';

			if ($matches[2][0] == '' && $matches[3][0] == '')
				$depth = min ($depth + 1, 16);
		}
		else if ($matches[1][0] != '')
			$html .= str_repeat ('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . escape ($matches[1][0]) . '<br />';

		$index = $matches[0][1] + strlen ($matches[0][0]);
	}

	return $html;
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
		' . $input . '
	</body>
</html>');
}

function print_select ($name, $options)
{
	$current = isset ($_REQUEST[$name]) ? (string)$_REQUEST[$name] : null;
	$html = '<select name="' . escape ($name) . '">';

	foreach ($options as $value => $caption)
		$html .= '<option' . ($current === $value ? ' selected="selected"' : '') . ' value="' . escape ($value) . '">' . escape ($caption) . '</option>';

	$html .= '</select>';

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
					<textarea name="markup" rows="10" style="box-sizing: border-box; width: 100%;"><?php echo escape (isset ($_REQUEST['markup']) ? $_REQUEST['markup'] : file_get_contents ('data/demo.txt')); ?></textarea>
					<div class="buttons" id="actions">
						Convert
						<?php echo print_select ('tag', array ('bbcode' => 'BBCode', 'wiki' => 'Wiki Markup')); ?>
						into
						<?php echo print_select ('format', array ('html' => 'HTML')); ?>
						and
						<?php echo print_select ('action', array ('print' => 'print result', 'html' => 'show HTML', 'debug' => 'debug cycle')); ?>
						<input type="submit" value="Submit" />
						<input onclick="var p = document.getElementById('options_panel'); p.style.display = p.style.display === 'none' ? 'block' : 'none';" type="button" value="Options" />
					</div>
					<div class="buttons" id="options_panel" style="display: none;">
						Tokenize using
						<?php echo print_select ('scanner', array ('preg' => 'preg')); ?>
						scanner and
						<?php echo print_select ('converter', array ('tag' => 'tag')); ?>
						converter, serialize using
						<?php echo print_select ('encoder', array ('compact' => 'compact', 'json' => 'json', 'sleep' => 'sleep')); ?>
						encoder, render with
						<?php echo print_select ('renderer', array ('format' => 'format')); ?>
						renderer
					</div>
				</form>
			</div>
		</div>
<?php

if (isset ($_REQUEST['action']) && isset ($_REQUEST['markup']))
{
	switch (isset ($_REQUEST['encoder']) ? $_REQUEST['encoder'] : null)
	{
		case 'compact':
			$encoder = new Amato\CompactEncoder ();

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

	switch (isset ($_REQUEST['scanner']) ? $_REQUEST['scanner'] : null)
	{
		case 'preg':
			$scanner = new Amato\PregScanner ();

			break;

		default:
			throw new Exception ('invalid scanner');
	}

	switch (isset ($_REQUEST['converter']) ? $_REQUEST['converter'] : null)
	{
		case 'tag':
			switch (isset ($_REQUEST['tag']) ? $_REQUEST['tag'] : null)
			{
				case 'bbcode':
					require ('config/tag/bbcode.php');

					break;

				case 'wiki':
					require ('config/tag/wiki.php');

					break;

				default:
					throw new Exception ('invalid tag');
			}

			$converter = new Amato\TagConverter ($encoder, $scanner, $tags);

			break;

		default:
			throw new Exception ('invalid converter');
	}

	switch (isset ($_REQUEST['renderer']) ? $_REQUEST['renderer'] : null)
	{
		case 'format':
			switch (isset ($_REQUEST['format']) ? $_REQUEST['format'] : null)
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

	$markup = str_replace ("\r", '', $_REQUEST['markup']);
	$token = $converter->convert ($markup);
	$render = $renderer->render ($token);

	switch ($_REQUEST['action'])
	{
		case 'debug':
			$revert = $converter->revert ($token);

			$output = '
<blockquote class="panel">
	<p class="label">Markup string (' . mb_strlen ($markup) . ' characters:</p>
	<div class="code">' . escape ($markup) . '</div>
</blockquote>
<blockquote class="panel">
	<p class="label">Token string (' . mb_strlen ($token) . ' characters):</p>
	<div class="code">' . escape ($token) . '</div>
</blockquote>
<blockquote class="panel">
	<p class="label">Render string (' . mb_strlen ($render) . ' characters):</p>
	<div class="code">' . escape ($render) . '</div>
</blockquote>
<blockquote class="panel">
	<p class="label">Revert string (' . mb_strlen ($revert) . ' characters):</p>
	<div class="code" style="color: ' . ($markup === $revert ? 'green' : 'red') . ';">' . escape ($revert) . '</div>
</blockquote>';

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
