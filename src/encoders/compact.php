<?php

namespace Umen;

defined ('UMEN') or die;

define ('UMEN_ENCODER_COMPACT_TOKEN_ESCAPE',	'\\');
define ('UMEN_ENCODER_COMPACT_TOKEN_PARAM',		',');
define ('UMEN_ENCODER_COMPACT_TOKEN_PLAIN',		'|');
define ('UMEN_ENCODER_COMPACT_TOKEN_SCOPE',		';');
define ('UMEN_ENCODER_COMPACT_TOKEN_VALUE',		'=');

define ('UMEN_ENCODER_COMPACT_VERSION',			2);

class	CompactEncoder extends Encoder
{
	private static	$actionsDecode = array
	(
		'/'	=> UMEN_ACTION_ALONE,
		'<'	=> UMEN_ACTION_START,
		'-'	=> UMEN_ACTION_STEP,
		'>'	=> UMEN_ACTION_STOP
	);

	private static	$actionsEncode = array
	(
		UMEN_ACTION_ALONE	=> '/',
		UMEN_ACTION_START	=> '<',
		UMEN_ACTION_STEP	=> '-',
		UMEN_ACTION_STOP	=> '>'
	);

	private static	$escapesDecode = array
	(
		UMEN_ENCODER_COMPACT_TOKEN_PARAM	=> true,
		UMEN_ENCODER_COMPACT_TOKEN_PLAIN	=> true,
		UMEN_ENCODER_COMPACT_TOKEN_SCOPE	=> true,
		UMEN_ENCODER_COMPACT_TOKEN_VALUE	=> true
	);

	private static	$escapesEncode = array
	(
		UMEN_ENCODER_COMPACT_TOKEN_ESCAPE	=> true,
		UMEN_ENCODER_COMPACT_TOKEN_PARAM		=> true,
		UMEN_ENCODER_COMPACT_TOKEN_PLAIN		=> true,
		UMEN_ENCODER_COMPACT_TOKEN_SCOPE		=> true,
		UMEN_ENCODER_COMPACT_TOKEN_VALUE		=> true
	);

	public function	decode ($token)
	{
		$length = strlen ($token);
		$scopes = array ();

		// Parse version
		for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
			++$i;

		$version = (int)substr ($token, 0, $i);

		if ($version !== UMEN_ENCODER_COMPACT_VERSION)
			return null;

		// Parse header
		while ($i < $length && $token[$i] === UMEN_ENCODER_COMPACT_TOKEN_SCOPE)
		{
			++$i;

			// Read tag delta
			for ($j = $i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
				++$i;

			if ($i > $j)
				$delta = (int)substr ($token, $j, $i - $j);
			else
				continue;

			// Read tag action
			if ($i < $length && isset (self::$actionsDecode[$token[$i]]))
				$action = self::$actionsDecode[$token[$i++]];
			else
				continue;

			// Read tag name
			$name = '';

			for ($i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
			{
				if ($token[$i] === UMEN_ENCODER_COMPACT_TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$name .= $token[$i];
			}

			// Read tag flag
			$flag = '';

			if ($i < $length && $token[$i] === UMEN_ENCODER_COMPACT_TOKEN_VALUE)
			{
				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === UMEN_ENCODER_COMPACT_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$flag .= $token[$i];
				}
			}

			// Read tag captures
			for ($captures = array (); $i < $length && $token[$i] === UMEN_ENCODER_COMPACT_TOKEN_PARAM; )
			{
				$cName = '';

				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === UMEN_ENCODER_COMPACT_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$cName .= $token[$i];
				}

				$cValue = '';

				if ($i < $length && $token[$i] === UMEN_ENCODER_COMPACT_TOKEN_VALUE)
				{
					for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
					{
						if ($token[$i] === UMEN_ENCODER_COMPACT_TOKEN_ESCAPE && $i + 1 < $length)
							++$i;

						$cValue .= $token[$i];
					}
				}

				$captures[$cName] = $cValue;
			}

			$scopes[] = array ($delta, $name, $action, $flag, $captures);
		}

		if ($i >= $length || $token[$i++] !== UMEN_ENCODER_COMPACT_TOKEN_PLAIN)
			return null;

		return array ($scopes, substr ($token, $i));
	}

	public function	encode ($scopes, $plain)
	{
		$token = UMEN_ENCODER_COMPACT_VERSION;

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Append offset delta and action to tokenized header
			$token .= UMEN_ENCODER_COMPACT_TOKEN_SCOPE . $delta . self::$actionsEncode[$action];

			// Write tag name
			foreach (str_split ($name) as $character)
			{
				if (isset (self::$escapesEncode[$character]))
					$token .= UMEN_ENCODER_COMPACT_TOKEN_ESCAPE;

				$token .= $character;
			}

			// Write tag flag
			if ($flag !== '')
			{
				$token .= UMEN_ENCODER_COMPACT_TOKEN_VALUE;

				foreach (str_split ($flag) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= UMEN_ENCODER_COMPACT_TOKEN_ESCAPE;

					$token .= $character;
				}
			}

			// Write tag parameters
			foreach ($captures as $cName => $cValue)
			{
				$token .= UMEN_ENCODER_COMPACT_TOKEN_PARAM;

				foreach (str_split ($cName) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= UMEN_ENCODER_COMPACT_TOKEN_ESCAPE;

					$token .= $character;
				}

				if ($cValue !== '')
				{
					$token .= UMEN_ENCODER_COMPACT_TOKEN_VALUE;

					foreach (str_split ($cValue) as $character)
					{
						if (isset (self::$escapesEncode[$character]))
							$token .= UMEN_ENCODER_COMPACT_TOKEN_ESCAPE;

						$token .= $character;
					}
				}
			}
		}

		return $token . UMEN_ENCODER_COMPACT_TOKEN_PLAIN . $plain;
	}
}

?>
