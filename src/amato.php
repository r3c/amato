<?php

/*
** Agnostic Markup Tokenizer
*/

namespace Amato;

define ('AMATO', '1.0.0.0');

require (dirname (__FILE__) . '/tag.php');

function autoload ()
{
	static $loaded;

	if (isset ($loaded))
		return;

	$loaded = true;

	spl_autoload_register (function ($class)
	{
		$path = dirname (__FILE__);

		switch ($class)
		{
			case 'Amato\TagConverter':
				require_once ($path . '/converter.php');
				require ($path . '/converters/tag.php');

				break;

			case 'Amato\CompactEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/compact.php');

				break;

			case 'Amato\ConcatEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/concat.php');

				break;

			case 'Amato\JSONEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/json.php');

				break;

			case 'Amato\SleepEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/sleep.php');

				break;

			case 'Amato\FormatRenderer':
				require_once ($path . '/renderer.php');
				require ($path . '/renderers/format.php');

				break;

			case 'Amato\PregScanner':
				require_once ($path . '/scanner.php');
				require ($path . '/scanners/preg.php');

				break;
		}
	});
}

?>
