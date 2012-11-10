<?php

define ('ENCODER_ACTION_LITERAL',	0);
define ('ENCODER_ACTION_SINGLE',	1);
define ('ENCODER_ACTION_START',		2);
define ('ENCODER_ACTION_STEP',		3);
define ('ENCODER_ACTION_STOP',		4);

define ('ENCODER_TOKEN_ESCAPE',		'\\');
define ('ENCODER_TOKEN_FLAG',		'=');
define ('ENCODER_TOKEN_PARAM',		',');
define ('ENCODER_TOKEN_PLAIN',		'|');
define ('ENCODER_TOKEN_SCOPE',		';');

define ('ENCODER_VERSION',			1);

class	Encoder
{
	private static	$actionsDecode = array ('!' => ENCODER_ACTION_LITERAL, '/' => ENCODER_ACTION_SINGLE, '<' => ENCODER_ACTION_START, '-' => ENCODER_ACTION_STEP, '>' => ENCODER_ACTION_STOP);
	private static	$actionsEncode = array (ENCODER_ACTION_LITERAL => '!', ENCODER_ACTION_SINGLE => '/', ENCODER_ACTION_START => '<', ENCODER_ACTION_STEP => '-', ENCODER_ACTION_STOP => '>');
	private static	$escapesDecode = array (ENCODER_TOKEN_PARAM => true, ENCODER_TOKEN_PLAIN => true, ENCODER_TOKEN_SCOPE => true, ENCODER_TOKEN_FLAG => true);
	private static	$escapesEncode = array (ENCODER_TOKEN_ESCAPE => true, ENCODER_TOKEN_PARAM => true, ENCODER_TOKEN_PLAIN => true, ENCODER_TOKEN_SCOPE => true, ENCODER_TOKEN_FLAG => true);

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

		if ($version !== ENCODER_VERSION)
			return null;

		// Parse header
		while ($i < $length && $token[$i] === ENCODER_TOKEN_SCOPE)
		{
			++$i;

			// Parse delta
			for ($j = $i; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
				++$i;

			if ($i > $j)
				$delta = (int)substr ($token, $j, $i - $j);
			else
				continue;

			// Parse action
			if ($i < $length && isset (self::$actionsDecode[$token[$i]]))
				$action = self::$actionsDecode[$token[$i++]];
			else
				continue;

			// Parse name
			$name = '';

			for ($i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
			{
				if ($token[$i] === ENCODER_TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$name .= $token[$i];
			}

			// Parse flag
			$flag = '';

			if ($i < $length && $token[$i] === ENCODER_TOKEN_FLAG)
			{
				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === ENCODER_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$flag .= $token[$i];
				}
			}

			// Parse params
			for ($params = array (); $i < $length && $token[$i] === ENCODER_TOKEN_PARAM; )
			{
				$param = '';

				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === ENCODER_TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$param .= $token[$i];
				}

				$params[] = $param;
			}

			$scopes[] = array ($delta, $name, $action, $flag, $params);
		}

		if ($i >= $length || $token[$i++] !== ENCODER_TOKEN_PLAIN)
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
		$token = ENCODER_VERSION;

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $params) = $scope;

			// Append offset delta and action to tokenized header
			$token .= ENCODER_TOKEN_SCOPE . $delta . self::$actionsEncode[$action];

			// Write tag name
			foreach (str_split ($name) as $character)
			{
				if (isset (self::$escapesEncode[$character]))
					$token .= ENCODER_TOKEN_ESCAPE;

				$token .= $character;
			}

			// Write tag flag
			if ($flag !== null)
			{
				$token .= ENCODER_TOKEN_FLAG;

				foreach (str_split ($flag) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= ENCODER_TOKEN_ESCAPE;

					$token .= $character;
				}
			}

			// Write tag parameters
			foreach ($params as $param)
			{
				$token .= ENCODER_TOKEN_PARAM;

				foreach (str_split ($param) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= ENCODER_TOKEN_ESCAPE;

					$token .= $character;
				}
			}
		}

		return $token . ENCODER_TOKEN_PLAIN . $plain;
	}
}

?>
