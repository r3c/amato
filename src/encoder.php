<?php

require_once (dirname (__FILE__) . '/definition.php');

define ('UMEN_ENCODER_TOKEN_ESCAPE',	'\\');
define ('UMEN_ENCODER_TOKEN_FLAG',		'=');
define ('UMEN_ENCODER_TOKEN_PARAM',		',');
define ('UMEN_ENCODER_TOKEN_PLAIN',		'|');
define ('UMEN_ENCODER_TOKEN_SCOPE',		';');

define ('UMEN_ENCODER_VERSION',			1);

class	UmenEncoder
{
	private static	$actionsDecode = array ('!' => UMEN_ACTION_LITERAL, '/' => UMEN_ACTION_ALONE, '<' => UMEN_ACTION_START, '-' => UMEN_ACTION_STEP, '>' => UMEN_ACTION_STOP);
	private static	$actionsEncode = array (UMEN_ACTION_LITERAL => '!', UMEN_ACTION_ALONE => '/', UMEN_ACTION_START => '<', UMEN_ACTION_STEP => '-', UMEN_ACTION_STOP => '>');
	private static	$escapesDecode = array (UMEN_ENCODER_TOKEN_PARAM => true, UMEN_ENCODER_TOKEN_PLAIN => true, UMEN_ENCODER_TOKEN_SCOPE => true, UMEN_ENCODER_TOKEN_FLAG => true);
	private static	$escapesEncode = array (UMEN_ENCODER_TOKEN_ESCAPE => true, UMEN_ENCODER_TOKEN_PARAM => true, UMEN_ENCODER_TOKEN_PLAIN => true, UMEN_ENCODER_TOKEN_SCOPE => true, UMEN_ENCODER_TOKEN_FLAG => true);

	/*
	** Decode tokenized string into tag scopes and plain string.
	** $token:	tokenized string
	** return:	(scopes, plain) array or null on parsing error
	*/
	public function	decode ($token)
	{
		$length = strlen ($token);
		$scopes = array ();

		// Parse version
		for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
			++$i;

		$version = (int)substr ($token, 0, $i);

		if ($version !== UMEN_ENCODER_VERSION)
			return null;

		// Parse header
		while ($i < $length && $token[$i] === UMEN_ENCODER_TOKEN_SCOPE)
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
				if ($token[$i] === UMEN_ENCODER_TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$name .= $token[$i];
			}

			// Read tag flag
			$flag = '';

			if ($i < $length && $token[$i] === UMEN_ENCODER_TOKEN_FLAG)
			{
				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === UMEN_ENCODER_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$flag .= $token[$i];
				}
			}

			// Read tag params
			for ($params = array (); $i < $length && $token[$i] === UMEN_ENCODER_TOKEN_PARAM; )
			{
				$param = '';

				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === UMEN_ENCODER_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$param .= $token[$i];
				}

				$params[] = $param;
			}

			$scopes[] = array ($delta, $name, $action, $flag, $params);
		}

		if ($i >= $length || $token[$i++] !== UMEN_ENCODER_TOKEN_PLAIN)
			return null;

		return array ($scopes, substr ($token, $i));
	}

	/*
	** Encode tag scopes and plain string into tokenized string.
	** $scopes:	resolved tag scopes
	** $plain:	plain string
	** return:	tokenized string
	*/
	public function	encode ($scopes, $plain)
	{
		$token = UMEN_ENCODER_VERSION;

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $params) = $scope;

			// Append offset delta and action to tokenized header
			$token .= UMEN_ENCODER_TOKEN_SCOPE . $delta . self::$actionsEncode[$action];

			// Write tag name
			foreach (str_split ($name) as $character)
			{
				if (isset (self::$escapesEncode[$character]))
					$token .= UMEN_ENCODER_TOKEN_ESCAPE;

				$token .= $character;
			}

			// Write tag flag
			if ($flag !== null)
			{
				$token .= UMEN_ENCODER_TOKEN_FLAG;

				foreach (str_split ($flag) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= UMEN_ENCODER_TOKEN_ESCAPE;

					$token .= $character;
				}
			}

			// Write tag parameters
			foreach ($params as $param)
			{
				$token .= UMEN_ENCODER_TOKEN_PARAM;

				foreach (str_split ($param) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= UMEN_ENCODER_TOKEN_ESCAPE;

					$token .= $character;
				}
			}
		}

		return $token . UMEN_ENCODER_TOKEN_PLAIN . $plain;
	}
}

?>
