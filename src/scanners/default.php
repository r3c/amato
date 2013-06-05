<?php

namespace Umen;

defined ('UMEN') or die;

class	DefaultScanner extends Scanner
{
	const CAPTURE_BEGIN		= '<';
	const CAPTURE_END		= '>';
	const CAPTURE_NAME		= ':';
	const DECODE_CAPTURE	= 0;
	const DECODE_STRING		= 1;
	const GROUP_BEGIN		= '(';
	const GROUP_END			= ')';
	const GROUP_ESCAPE		= '\\';
	const GROUP_RANGE		= '-';
	const REPEAT_BEGIN		= '{';
	const REPEAT_END		= '}';
	const REPEAT_SPLIT		= ',';

	public function	__construct ($escape = '\\')
	{
		$this->escape = $escape;
		$this->start = new UmenScannerState ();
		$this->table = array ();
	}

	/*
	** Override for Scanner::assign.
	*/
	public function	assign ($pattern, $match)
	{
		$accept = count ($this->table);
		$capture = null;
		$decode = array ();
		$length = strlen ($pattern);
		$tails = array (array ($this->start, false)); // FIXME: ugly

		for ($i = 0; $i < $length; )
		{
			// Parse capture instructions
			if ($i < $length && $pattern[$i] === self::CAPTURE_BEGIN)
			{
				$capture = '';

				++$i;

				while ($i < $length && $pattern[$i] !== self::CAPTURE_NAME)
					$capture .= $pattern[$i++];

				$decode[] = array (self::DECODE_CAPTURE, $capture);

				++$i;
			}

			if ($i < $length && $pattern[$i] === self::CAPTURE_END)
			{
				$capture = null;

				++$i;
			}

			// Parse character or group
			if ($i >= $length)
				continue;

			if ($pattern[$i] === self::GROUP_BEGIN)
			{
				$characters = array ();

				for (++$i; $i < $length && $pattern[$i] !== self::GROUP_END; )
				{
					if ($i + 1 < $length && $pattern[$i] === self::GROUP_ESCAPE)
					{
						$characters[] = $pattern[$i + 1];

						$i += 2;
					}
					else if ($i + 2 < $length && $pattern[$i + 1] === self::GROUP_RANGE)
					{
						for ($ord = ord ($pattern[$i]); $ord <= ord ($pattern[$i + 2]); ++$ord)
							$characters[] = chr ($ord);

						$i += 3;
					}
					else
					{
						$characters[] = $pattern[$i];

						$i += 1;
					}
				}

				if ($i >= $length || $pattern[$i] !== self::GROUP_END)
					throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . self::GROUP_END . '"');
			}
			else
			{
				if ($i + 1 < $length && $pattern[$i] === self::GROUP_ESCAPE)
					++$i;

				$characters = array ($pattern[$i]);
			}

			++$i;

			// Parse repeat modifiers
			if ($i < $length && $pattern[$i] === self::REPEAT_BEGIN)
			{
				for ($j = ++$i; $i < $length && $pattern[$i] >= '0' && $pattern[$i] <= '9'; )
					++$i;

				$min = $i > $j ? (int)substr ($pattern, $j, $i - $j) : 0;

				if ($i < $length && $pattern[$i] == self::REPEAT_SPLIT)
				{
					for ($j = ++$i; $i < $length && $pattern[$i] >= '0' && $pattern[$i] <= '9'; )
						++$i;

					$max = $i > $j ? (int)substr ($pattern, $j, $i - $j) : 0;
				}
				else
					$max = $min;

				if ($i >= $length || $pattern[$i] !== self::REPEAT_END)
					throw new \Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . self::REPEAT_END . '"');

				++$i;
			}
			else
			{
				$max = 1;
				$min = 1;
			}

			// Update scanner states
			$actives = $tails;
			$repeat = max ($min, $max);
			$tails = array ();

			for ($occurrence = 0; $occurrence < $repeat; ++$occurrence)
			{
				if ($occurrence >= $min)
					$tails = array_merge ($tails, $actives);

				$follows = array ();
				$target = null;

				foreach ($actives as $active)
					$follows = array_merge ($follows, $active[0]->connect ($characters, $target, $active[1])); // FIXME: ugly

				$actives = $follows;

				if ($capture !== null)
				{
					foreach ($actives as $active)
						$active[0]->captures[$accept] = $capture; // FIXME: ugly
				}
			}

			if ($max === 0)
			{
				$follows = array ();
				$target = null;

				foreach ($actives as $active)
					$follows = array_merge ($follows, $active[0]->cycle ($characters, $target)); // FIXME: ugly

				$actives = $follows;

				if ($capture !== null)
				{
					foreach ($actives as $active)
						$active[0]->captures[$accept] = $capture; // FIXME: ugly
				}
			}

			$tails = array_merge ($tails, $actives);

			// Register constant characters to decode array
			if ($capture === null)
			{
				$constant = str_repeat ($characters[0], $min);
				$count = count ($decode);

				if ($count > 0 && $decode[$count - 1][0] === self::DECODE_STRING)
					$decode[$count - 1][1] .= $constant;
				else
					$decode[] = array (self::DECODE_STRING, $constant);
			}
		}

		foreach ($tails as $state)
			$state[0]->accepts[] = $accept;

		$this->table[] = array ($decode, $match);

		return $accept;
	}

	/*
	** Override for Scanner::escape.
	*/
	public function	escape ($string, $verify)
	{
		$cursors = array ();
		$size = strlen ($string);

		for ($offset = 0; $offset < $size; ++$offset)
		{
			$character = $string[$offset];

			if ($character === $this->escape)
				$insert = $offset;
			else
			{
				$cursors[] = new UmenScannerCursor ($this->start, $offset);
				$insert = null;

				for ($i = count ($cursors) - 1; $insert === null && $i >= 0; --$i)
				{
					$cursor = $cursors[$i];

					if ($cursor->move ($character))
					{
						foreach ($cursor->accepts as $accept => $dummy)
						{
							if ($verify ($this->table[$accept][1]))
							{
								$insert = $cursor->offset;

								break;
							}
						}
					}
					else
						array_splice ($cursors, $i, 1);
				}
			}

			if ($insert !== null)
			{
				$cursors = array ();
				$string = substr_replace ($string, $this->escape, $insert, 0);

				++$offset;
				++$size;
			}
		}

		return $string;
	}

	/*
	** Override for Scanner::make.
	*/
	public function	make ($accept, $captures)
	{
		$decode = $this->table[$accept][0];
		$string = '';

		foreach ($decode as $segment)
		{
			if ($segment[0] === self::DECODE_STRING)
				$string .= $segment[1];
			else if (isset ($captures[$segment[1]]))
				$string .= $captures[$segment[1]];
		}

		return $string;
	}

	/*
	** Override for Scanner::scan.
	*/
	public function	scan ($string, $process, $verify)
	{
		$cursors = array ();
		$size = strlen ($string);

		for ($offset = 0; $offset < $size; ++$offset)
		{
			$character = $string[$offset];

			// Kill cursors and remove escape character from original string
			if ($character === $this->escape && $offset + 1 < $size)
			{
				foreach ($cursors as $cursor)
					$cursor->kill ();

				$string = substr_replace ($string, '', $offset, 1);

				--$size;

				continue;
			}

			// Move cursors and drop dead ones with no accepts
			$cursors[] = new UmenScannerCursor ($this->start, $offset);
			$flush = true;

			for ($i = count ($cursors) - 1; $i >= 0; --$i)
			{
				$cursor = $cursors[$i];

				if ($cursor->move ($character))
					$flush = false;
				else if (count ($cursor->accepts) === 0)
					array_splice ($cursors, $i, 1);
			}

			// Search for matches and drop all cursors
			if ($flush)
			{
				$this->resolve ($string, $cursors, $process);

				$cursors = array ();
			}
		}

		$this->resolve ($string, $cursors, $process);

		return $string;
	}

	/*
	** Resolve first acceptable match from current cursors.
	** $string:		plain text string
	** $cursors:	cursors array
	** $process:	match processing callback (match, offset, length, captures)
	*/
	private function	resolve ($string, &$cursors, $process)
	{
		$count = count ($cursors);

		// Browse cursor from lowest to highest starting offset
		for ($i = 0; $i < $count; ++$i)
		{
			$cursor = $cursors[$i];

			// Browse accepted indices sorted by length descending order
			foreach ($cursor->accepts as $accept => $size)
			{
				$captures = isset ($cursor->captures[$accept]) ? $cursor->captures[$accept] : array ();
				$match = $this->table[$accept][1];

				$offset = mb_strlen (substr ($string, 0, $cursor->offset));
				$length = mb_strlen (substr ($string, $cursor->offset, $size));

				if ($process ($match, $offset, $length, $captures))
				{
					// Remove all cursors covered by this one
					while ($i + 1 < $count && $cursors[$i + 1]->offset < $cursor->offset + $size)
					{
						array_splice ($cursors, $i + 1, 1);

						--$count;
					}

					break;
				}
			}
		}
	}
}

// FIXME: should not use hash tables but ranges, and support union/except operations
class	UmenScannerBranch
{
	public function	__construct ($to, $hash)
	{
		$this->hash = $hash;
		$this->to = $to;
	}

	public function	contains ($character)
	{
		return isset ($this->hash[$character]);
	}
}

class	UmenScannerCursor
{
	public function	__construct ($state, $offset)
	{
		$this->accepts = array ();
		$this->captures = array ();
		$this->length = 0;
		$this->offset = $offset;
		$this->state = $state;
	}

	public function	kill ()
	{
		$this->state = null;
	}

	public function	move ($character)
	{
		// Cancel is cursor is already in a dead state
		if ($this->state === null)
			return false;

		// Follow branch to next state if possible
		$state = $this->state->follow ($character);

		if ($state === null)
		{
			$this->state = null;

			return false;
		}

		// Increase match length and save current state
		$this->length += 1;
		$this->state = $state;

		// Append character to captures
		foreach ($state->captures as $accept => $capture)
		{
			if (!isset ($this->captures[$accept]))
				$this->captures[$accept] = array ();

			if (!isset ($this->captures[$accept][$capture]))
				$this->captures[$accept][$capture] = '';

			$this->captures[$accept][$capture] .= $character;
		}

		// Store accepted indices and sort by length descending order
		foreach ($state->accepts as $accept)
			$this->accepts[$accept] = $this->length;

		arsort ($this->accepts, SORT_NUMERIC);

		return true;
	}
}

class	UmenScannerGroup
{
	public function	__construct ($inclusive)
	{
		$this->inclusive = $inclusive;
		$this->ranges = array ();
	}

	public function	contains ($character)
	{
		throw new \Exception ('not implemented');
	}

	public function	getExcept ($group)
	{
		throw new \Exception ('not implemented');
	}

	public function	getShare ($group)
	{
		throw new \Exception ('not implemented');
	}

	public function	merge ($lower, $upper)
	{
		$count = count ($this->ranges);

		for ($i = 0; $i < $count && $lower > $this->ranges[$i][0]; )
			++$i;

		for ($j = $count; $j > 0 && ($this->ranges[$j - 1][1] === null || $upper < $this->ranges[$j - 1][1]); )
			--$j;

		$l_over = $i > 0 && ($this->ranges[$i - 1][1] === null || $lower <= $this->ranges[$i - 1][1]);
		$u_over = $j < $count && $upper >= $this->ranges[$j][0];
echo "bounds: $i, $j ($l_over, $u_over)<br />";
		if ($l_over && $u_over)
		{
			$this->ranges[$i][1] = $this->ranges[$j - 1][1];

			array_splice ($this->ranges, $i + 1, $j - $i);
		}
		else if ($l_over)
		{
			$this->ranges[$i][1] = $upper;

			array_splice ($this->ranges, $i + 1, $j - $i - 1);
		}
		else if ($u_over)
		{
			$this->ranges[$j - 1][0] = $lower;

			array_splice ($this->ranges, $i, $j - $i - 1);
		}
		else
			array_splice ($this->ranges, $i + 1, $j - $i - 2, array (array ($lower, $upper)));
	}

	public function	size ()
	{
		throw new \Exception ('not implemented');
	}
}

class	UmenScannerState
{
	public function	__construct ()
	{
		$this->accepts = array ();
		$this->branches = array ();
		$this->captures = array ();
		$this->parents = 0;
	}

	public function	connect ($characters, &$target, $can_have_cycle)
	{
		// Push any existing cycle to another state if this one can't be used
		if (!$can_have_cycle) // FIXME: ugly
		{
			foreach ($this->branches as $branch)
			{
				if ($branch->to === $this)
				{
					$next = $this->fork ();

					$branch->to = $next;

					++$next->parents;
				}
			}
		}

		// Intersect with available characters from existing branches
		$hash = array_flip ($characters);
		$tails = array ();

		for ($i = count ($this->branches); $i-- > 0; )
		{
			$branch = $this->branches[$i];
			$shares = array_intersect_key ($hash, $branch->hash);
//			$share = $range->getShare ($branch->range);

			if (count ($shares) > 0)
//			if ($share->size () !== 0)
			{
				if ($branch->to === $this)
					throw new \Exception ('can\'t exit from cycle with one of cycle\'s characters');

				// Remove shared characters from those to be branched
				$hash = array_diff_key ($hash, $shares);
//				$range = $range->getExcept ($share);
				$next = $branch->to;

				// Move unwanted characters to another branch if any
				$excludes = array_diff_key ($branch->hash, $shares);
//				$exclude = $branch->range->getExcept ($share);

				if (count ($excludes) > 0)
//				if ($exclude->size () !== 0)
				{
					$state = $next->fork ();

					$this->branches[] = new UmenScannerBranch ($state, $excludes);
//					$this->branches[] = new UmenScannerBranch ($state, $exclude);

					++$state->parents;
				}

				$branch->hash = $shares;
//				$branch->range = $share;

				// Fork target state if we share it with another parent
				if ($next->parents > 1)
				{
					--$next->parents;

					$next = $next->fork ();

					$branch->to = $next;

					++$next->parents;
				}

				$tails[] = array ($next, false); // FIXME: ugly
			}
		}

		// Create new branch for remaining characters if any
		if (count ($hash) > 0)
//		if ($range->size () !== 0)
		{
			if ($target === null)
			{
				$target = new UmenScannerState ();
				$tails[] = array ($target, true); // FIXME: ugly
			}

			$this->branches[] = new UmenScannerBranch ($target, $hash);
//			$this->branches[] = new UmenScannerBranch ($target, $range);

			++$target->parents;
		}

		return $tails;
	}

	public function	cycle ($characters, &$target)
	{
		$hash = array_flip ($characters);

		// Create cycle on current state if it has no branches and no accepts
		if (count ($this->accepts) === 0 && count ($this->branches) === 0)
		{
			$this->branches[] = new UmenScannerBranch ($this, $hash);
//			$this->branches[] = new UmenScannerBranch ($this, $range);

			return array (array ($this, true)); // FIXME: ugly
		}

		// Update existing cycle if one can be found
		for ($i = count ($this->branches); $i-- > 0; )
		{
			$branch = $this->branches[$i];

			// Reuse previous cycle if possible, else split into excluded,
			// included and shared characters branches otherwise
			if ($branch->to === $this)
			{
				$shares = array_intersect_key ($branch->hash, $hash);
//				$share = $range->getShare ($branch->range);
				$tails = array ();

				// Move unwanted characters to another branch if any
				$excludes = array_diff_key ($branch->hash, $shares);
//				$exclude = $branch->range->getExcept ($share);

				if (count ($excludes) > 0)
//				if ($exclude->size () !== 0)
				{
					$state = $this->fork ();

					$this->branches[] = new UmenScannerBranch ($state, $excludes);
//					$this->branches[] = new UmenScannerBranch ($state, $exclude);

					++$state->parents;
				}

				// Create new branch for exclusive characters
				$includes = array_diff_key ($hash, $shares);
//				$include = $range->getExcept ($share);

				if (count ($includes) > 0)
//				if ($include->size () !== 0)
				{
					$state = new UmenScannerState ();

					$this->branches[] = new UmenScannerBranch ($state, $includes);
//					$this->branches[] = new UmenScannerBranch ($state, $include);

					++$state->parents;

					$tails = array_merge ($tails, $state->cycle ($characters, $target));
//					$tails = array_merge ($tails, $state->cycle ($range, $target));
				}

				// Update or remove current cycle branch
				if (count ($shares) < 1)
//				if ($share->size () === 0)
					array_splice ($this->branches, $i, 1);
				else
					$branch->hash = $shares;
//					$branch->range = $share;

				$tails[] = array ($this, true); // FIXME: ugly

				return $tails;
			}
		}

		// Disambiguate cycles by forwarding them to branches
		$tails = array ();

		foreach ($this->connect ($characters, $target, false) as $state) // FIXME: ugly
//		foreach ($this->connect ($range, $target, false) as $state) // FIXME: ugly
			$tails = array_merge ($tails, $state[0]->cycle ($characters, $target)); // FIXME: ugly
//			$tails = array_merge ($tails, $state[0]->cycle ($range, $target)); // FIXME: ugly

		$tails[] = array ($this, false); // FIXME: ugly

		return $tails;
	}

	public function	follow ($character)
	{
/* FIXME: cache hack */
if (!isset ($this->lookup))
{
	$this->lookup = array ();

	foreach ($this->branches as $branch)
	{
		foreach ($branch->hash as $c => $dummy)
		{
			if (ord ($c) < 256)
				$this->lookup[$c] = $branch->to;
		}
	}
}

if (ord ($character) < 256)
{
	if (isset ($this->lookup[$character]))
		return $this->lookup[$character];

	return null;
}
/* FIXME: cache hack */

		foreach ($this->branches as $branch)
		{
			if ($branch->contains ($character))
				return $branch->to;
		}

		return null;
	}

	public function	fork ()
	{
		$clone = new UmenScannerState ();
		$clone->accepts = $this->accepts;
		$clone->captures = $this->captures;

		foreach ($this->branches as $branch)
		{
			$target = $branch->to;

			if ($target !== $this)
				++$target->parents;
			else
				$target = $clone;

			$clone->branches[] = new UmenScannerBranch ($target, $branch->hash);
		}

		return $clone;
	}
}

?>
