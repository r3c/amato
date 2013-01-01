<?php

namespace Umen;

defined ('UMEN') or die;

class	CompactEncoder extends Encoder
{
	const TOKEN_ESCAPE	= '\\';
	const TOKEN_PARAM	= ',';
	const TOKEN_PLAIN	= '|';
	const TOKEN_SCOPE	= ';';
	const TOKEN_VALUE	= '=';

	const VERSION		= 3;

	private static	$actionsDecode = array
	(
		'/'	=> Action::ALONE,
		'<'	=> Action::START,
		'-'	=> Action::STEP,
		'>'	=> Action::STOP
	);

	private static	$actionsEncode = array
	(
		Action::ALONE	=> '/',
		Action::START	=> '<',
		Action::STEP	=> '-',
		Action::STOP	=> '>'
	);

	private static	$escapesDecode = array
	(
		self::TOKEN_PARAM	=> true,
		self::TOKEN_PLAIN	=> true,
		self::TOKEN_SCOPE	=> true,
		self::TOKEN_VALUE	=> true
	);

	private static	$escapesEncode = array
	(
		self::TOKEN_ESCAPE	=> true,
		self::TOKEN_PARAM	=> true,
		self::TOKEN_PLAIN	=> true,
		self::TOKEN_SCOPE	=> true,
		self::TOKEN_VALUE	=> true
	);

	/*
	** Override for Encoder::decode.
	*/
	public function	decode ($token)
	{
		$length = strlen ($token);
		$scopes = array ();

		// Parse version
		for ($i = 0; $i < $length && $token[$i] >= '0' && $token[$i] <= '9'; )
			++$i;

		$version = (int)substr ($token, 0, $i);

		if ($version !== self::VERSION)
			return null;

		// Parse header
		while ($i < $length && $token[$i] === self::TOKEN_SCOPE)
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
				if ($token[$i] === self::TOKEN_ESCAPE && $i + 1 < $length)
					++$i;

				$name .= $token[$i];
			}

			// Read tag flag
			$flag = '';

			if ($i < $length && $token[$i] === self::TOKEN_VALUE)
			{
				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === self::TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$flag .= $token[$i];
				}
			}

			// Read tag captures
			$captures = array ();

			while ($i < $length && $token[$i] === self::TOKEN_PARAM)
			{
				$cName = '';

				for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
				{
					if ($token[$i] === self::TOKEN_ESCAPE && $i + 1 < $length)
						++$i;

					$cName .= $token[$i];
				}

				$cValue = '';

				if ($i < $length && $token[$i] === self::TOKEN_VALUE)
				{
					for (++$i; $i < $length && !isset (self::$escapesDecode[$token[$i]]); ++$i)
					{
						if ($token[$i] === self::TOKEN_ESCAPE && $i + 1 < $length)
							++$i;

						$cValue .= $token[$i];
					}
				}

				$captures[$cName] = $cValue;
			}

			$scopes[] = array ($delta, $name, $action, $flag, $captures);
		}

		if ($i >= $length || $token[$i++] !== self::TOKEN_PLAIN)
			return null;

		return array ($scopes, substr ($token, $i));
	}

	/*
	** Override for Encoder::encode.
	*/
	public function	encode ($scopes, $plain)
	{
		$token = self::VERSION;

		foreach ($scopes as $scope)
		{
			list ($delta, $name, $action, $flag, $captures) = $scope;

			// Append offset delta and action to tokenized header
			$token .= self::TOKEN_SCOPE . $delta . self::$actionsEncode[$action];

			// Write tag name
			foreach (str_split ($name) as $character)
			{
				if (isset (self::$escapesEncode[$character]))
					$token .= self::TOKEN_ESCAPE;

				$token .= $character;
			}

			// Write tag flag
			if ($flag !== '')
			{
				$token .= self::TOKEN_VALUE;

				foreach (str_split ($flag) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= self::TOKEN_ESCAPE;

					$token .= $character;
				}
			}

			// Write tag parameters
			foreach ($captures as $cName => $cValue)
			{
				$token .= self::TOKEN_PARAM;

				foreach (str_split ($cName) as $character)
				{
					if (isset (self::$escapesEncode[$character]))
						$token .= self::TOKEN_ESCAPE;

					$token .= $character;
				}

				if ($cValue !== '')
				{
					$token .= self::TOKEN_VALUE;

					foreach (str_split ($cValue) as $character)
					{
						if (isset (self::$escapesEncode[$character]))
							$token .= self::TOKEN_ESCAPE;

						$token .= $character;
					}
				}
			}
		}

		return $token . self::TOKEN_PLAIN . $plain;
	}
}

?>
