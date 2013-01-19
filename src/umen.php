<?php

/*
** Universal Markup ENgine
*/

namespace Umen;

define ('UMEN',	'1.0.3.0');

require (dirname (__FILE__) . '/action.php');

function	autoload ()
{
	spl_autoload_register (function ($class)
	{
		$path = dirname (__FILE__);

		switch ($class)
		{
			case 'Umen\SyntaxConverter':
				require_once ($path . '/converter.php');
				require ($path . '/converters/syntax.php');

				break;

			case 'Umen\CompactEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/compact.php');

				break;

			case 'Umen\ConcatEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/concat.php');

				break;

			case 'Umen\JSONEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/json.php');

				break;

			case 'Umen\SleepEncoder':
				require_once ($path . '/encoder.php');
				require ($path . '/encoders/sleep.php');

				break;

			case 'Umen\FormatRenderer':
				require_once ($path . '/renderer.php');
				require ($path . '/renderers/format.php');

				break;

			case 'Umen\DefaultScanner':
				require_once ($path . '/scanner.php');
				require ($path . '/scanners/default.php');

				break;

			case 'Umen\RegExpScanner':
				require_once ($path . '/scanner.php');
				require ($path . '/scanners/regexp.php');

				break;
		}
	});
}

?>
