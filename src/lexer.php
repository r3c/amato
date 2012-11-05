<?php

define ('LEXER_CHARACTER_CAPTURE_BEGIN',	'<');
define ('LEXER_CHARACTER_CAPTURE_END',		'>');
define ('LEXER_CHARACTER_GROUP_BEGIN',		'(');
define ('LEXER_CHARACTER_GROUP_END',		')');
define ('LEXER_CHARACTER_GROUP_ESCAPE',		'\\');
define ('LEXER_CHARACTER_GROUP_RANGE',		'-');
define ('LEXER_CHARACTER_REPEAT_BEGIN',		'{');
define ('LEXER_CHARACTER_REPEAT_END',		'}');
define ('LEXER_CHARACTER_REPEAT_SPLIT',		',');

// FIXME: should not use hash tables but ranges, and support union/except operations
class	Branch
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

class	Cursor
{
	public function	__construct ($state, $offset)
	{
		$this->accepts = array ();
		$this->captures = array ();
		$this->length = 0;
		$this->offset = $offset;
		$this->orders = array ();
		$this->state = $state;
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
		foreach ($state->captures as $accept => $flags)
		{
			if (($flags & 1) === 1 && !isset ($this->orders[$accept]))
			{
				if (!isset ($this->captures[$accept]))
					$this->captures[$accept] = array ();

				$this->captures[$accept][] = '';
				$this->orders[$accept] = count ($this->captures[$accept]) - 1;
			}

			if (($flags & 2) === 2)
				unset ($this->orders[$accept]);
		}

		foreach ($this->orders as $accept => $order)
			$this->captures[$accept][$order] .= $character;

		// Store accepted indices and sort by length descending order
		foreach ($state->accepts as $accept)
			$this->accepts[$accept] = $this->length;

		arsort ($this->accepts, SORT_NUMERIC);

		return true;
	}
}

class	Lexer
{
	public function	__construct ()
	{
		$this->matches = array ();
		$this->start = new State ();
	}

	public function	assign ($pattern, $match)
	{
		$this->matches[] = $match;

		$accept = count ($this->matches) - 1;
		$length = strlen ($pattern);
		$tails = array (array ($this->start, false)); // FIXME: ugly

		for ($i = 0; $i < $length; )
		{
			// Parse capture directives
			$capture = 0;

			if ($i < $length && $pattern[$i] === LEXER_CHARACTER_CAPTURE_BEGIN)
			{
				$capture |= 1;

				++$i;
			}

			if ($i < $length && $pattern[$i] === LEXER_CHARACTER_CAPTURE_END)
			{
				$capture |= 2;

				++$i;
			}

			// Parse character or group
			if ($i >= $length)
				continue;

			if ($pattern[$i] === LEXER_CHARACTER_GROUP_BEGIN)
			{
				$characters = array ();

				for (++$i; $i < $length && $pattern[$i] !== LEXER_CHARACTER_GROUP_END; )
				{
					if ($i + 1 < $length && $pattern[$i] === LEXER_CHARACTER_GROUP_ESCAPE)
					{
						$characters[] = $pattern[$i + 1];

						$i += 2;
					}
					else if ($i + 2 < $length && $pattern[$i + 1] === LEXER_CHARACTER_GROUP_RANGE)
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

				if ($i >= $length || $pattern[$i] !== LEXER_CHARACTER_GROUP_END)
					throw new Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . LEXER_CHARACTER_GROUP_END . '"');
			}
			else
			{
				if ($i + 1 < $length && $pattern[$i] === LEXER_CHARACTER_GROUP_ESCAPE)
					++$i;

				$characters = array ($pattern[$i]);
			}

			++$i;

			// Parse repeat modifiers
			if ($i < $length && $pattern[$i] === LEXER_CHARACTER_REPEAT_BEGIN)
			{
				for ($j = ++$i; $i < $length && $pattern[$i] >= '0' && $pattern[$i] <= '9'; )
					++$i;

				$min = $i > $j ? (int)substr ($pattern, $j, $i - $j) : 0;

				if ($i < $length && $pattern[$i] == LEXER_CHARACTER_REPEAT_SPLIT)
				{
					for ($j = ++$i; $i < $length && $pattern[$i] >= '0' && $pattern[$i] <= '9'; )
						++$i;

					$max = $i > $j ? (int)substr ($pattern, $j, $i - $j) : 0;
				}
				else
					$max = $min;

				if ($i >= $length || $pattern[$i] !== LEXER_CHARACTER_REPEAT_END)
					throw new Exception ('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . LEXER_CHARACTER_REPEAT_END . '"');

				++$i;
			}
			else
			{
				$max = 1;
				$min = 1;
			}

			// Update lexer states
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

				if ($capture !== 0)
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

				if ($capture !== 0)
				{
					foreach ($actives as $active)
						$active[0]->captures[$accept] = $capture; // FIXME: ugly
				}
			}

			$tails = array_merge ($tails, $actives);
		}

		foreach ($tails as $state)
			$state[0]->accepts[] = $accept;
	}

	public function resolve (&$cursors, $callback)
	{
		$count = count ($cursors);

		// Browse cursor from lowest to highest starting offset
		for ($i = 0; $i < $count; ++$i)
		{
			$cursor = $cursors[$i];

			// Browse accepted indices sorted by length descending order
			foreach ($cursor->accepts as $accept => $length)
			{
				$captures = isset ($cursor->captures[$accept]) ? $cursor->captures[$accept] : array ();
				$match = $this->matches[$accept];

				if ($callback ($cursor->offset, $length, $match, $captures))
				{
					// Remove all cursors covered by this one
					while ($i + 1 < $count && $cursors[$i + 1]->offset < $cursor->offset + $length)
					{
						array_splice ($cursors, $i + 1, 1);

						--$count;
					}

					break;
				}
			}
		}
	}

	public function	scan ($string, $callback)
	{
		$cursors = array ();
		$offset = 0;

		foreach (str_split ($string) as $character)
		{
			$cursors[] = new Cursor ($this->start, $offset++);

			// Move cursors and remove dead (locked with no accepts) ones
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
				$this->resolve ($cursors, $callback);

				$cursors = array ();
			}
		}

		$this->resolve ($cursors, $callback);
	}
}

class	State
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

			if (count ($shares) > 0)
			{
				if ($branch->to === $this)
					throw new Exception ('can\'t exit from cycle with one of cycle\'s characters');

				// Remove shared characters from those to be branched
				$hash = array_diff_key ($hash, $shares);
				$next = $branch->to;

				// Move unwanted characters to another branch if any
				$excludes = array_diff_key ($branch->hash, $shares);

				if (count ($excludes) > 0)
				{
					$state = $next->fork ();

					$this->branches[] = new Branch ($state, $excludes);

					++$state->parents;
				}

				$branch->hash = $shares;

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
		{
			if ($target === null)
			{
				$target = new State ();
				$tails[] = array ($target, true); // FIXME: ugly
			}

			$this->branches[] = new Branch ($target, $hash);

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
			$this->branches[] = new Branch ($this, $hash);

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
				$tails = array ();

				// Move unwanted characters to another branch if any
				$excludes = array_diff_key ($branch->hash, $shares);

				if (count ($excludes) > 0)
				{
					$state = $this->fork ();

					$this->branches[] = new Branch ($state, $excludes);

					++$state->parents;
				}

				// Create new branch for exclusive characters
				$includes = array_diff_key ($hash, $shares);

				if (count ($includes) > 0)
				{
					$state = new State ();

					$this->branches[] = new Branch ($state, $includes);

					++$state->parents;

					$tails = array_merge ($tails, $state->cycle ($characters, $target));
				}

				// Update or remove current cycle branch
				if (count ($shares) < 1)
					array_splice ($this->branches, $i, 1);
				else
					$branch->hash = $shares;

				$tails[] = array ($this, true); // FIXME: ugly

				return $tails;
			}
		}

		// Disambiguate cycles by forwarding them to branches
		$tails = array ();

		foreach ($this->connect ($characters, $target, true) as $state) // FIXME: ugly, possibly wrong
			$tails = array_merge ($tails, $state[0]->cycle ($characters, $target)); // FIXME: ugly

		$tails[] = array ($this, false); // FIXME: ugly

		return $tails;
	}

	public function	follow ($character)
	{
/* FIXME: hack */
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
/* FIXME: hack */

		foreach ($this->branches as $branch)
		{
			if ($branch->contains ($character))
				return $branch->to;
		}

		return null;
	}

	public function	fork ()
	{
		$clone = new State ();
		$clone->accepts = $this->accepts;
		$clone->captures = $this->captures;

		foreach ($this->branches as $branch)
		{
			$target = $branch->to;

			if ($target !== $this)
				++$target->parents;
			else
				$target = $clone;

			$clone->branches[] = new Branch ($target, $branch->hash);
		}

		return $clone;
	}
}
