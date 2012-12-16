<?php

namespace Umen;

defined ('UMEN') or die;

class	RegExpScanner extends Scanner
{
	public function	__construct ($escape = '\\')
	{
		throw new \Exception ('RegExpScanner not implemented!');

		$this->escape = $escape;
	}

	public function	assign ($pattern, $match)
	{
	}

	public function	escape ($string, $callback)
	{
	}

	public function	make ($id, $captures)
	{
	}

	public function	scan ($string, $callback)
	{
	}
}

?>
