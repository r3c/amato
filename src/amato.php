<?php

/*
** Agnostic Markup Tokenizer
*/

namespace Amato;

define('AMATO', '1.0.0.0');

interface Converter
{
    /*
    ** Convert markup string to tokenized string.
    ** $markup:		markup string
    ** $context:	optional context information
    ** return:		tokenized string
    */
    public function convert($markup, $context = null);

    /*
    ** Convert tokenized string back to markup string.
    ** $token:		tokenized string
    ** $context:	optional context information
    ** return:		markup string
    */
    public function revert($token, $context = null);
}

interface Encoder
{
    /*
    ** Decode tokenized string into tag groups and plain string.
    ** $token:	tokenized string
    ** return:	(plain string, tag groups) or null on parsing error
    */
    public function decode($token);

    /*
    ** Encode tag groups and plain string into tokenized string.
    ** $plain:	plain string
    ** $groups:	tag groups
    ** return:	tokenized string
    */
    public function encode($plain, $groups);
}

interface Renderer
{
    /*
    ** Render tokenized string.
    ** $token:	tokenized string
    ** $state:	custom state
    ** return:	rendered string
    */
    public function render($token, $state = null);
}

interface Scanner
{
    /*
    ** Register a new pattern into this scanner instance.
    ** $pattern:	registered pattern
    ** return:		[pattern accept key, names]
    */
    public function assign($pattern);

    /*
    ** Build tag from given pattern accept key and captures array.
    ** $key:		pattern accept key
    ** $captures:	captures array
    ** return:		tag string
    */
    public function build($key, $captures);

    /*
    ** Escape plain string into safe markup string.
    ** $plain:	plain text string
    ** return:	escaped markup string
    */
    public function escape($plain);

    /*
    ** Find matching candidates within given plain string.
    ** $string:	plain text string
    ** return:	array of (key, offset, length, captures) candidates
    */
    public function find($string);
}

class GroupIterator
{
    public function __construct($groups)
    {
        $this->cursors = count($groups) > 0 ? array(0 => 0) : array();
        $this->groups = $groups;
        $this->shift = 0;
    }

    public function next(&$value)
    {
        if (count($this->cursors) < 1) {
            return false;
        }

        // First best group and marker indices in current cursors by offset ascending, marker descending
        $best_marker_index = 0;
        $best_offset = null;

        foreach ($this->cursors as $last_group_index => $last_marker_index) {
            $offset = $this->groups[$last_group_index][1][$last_marker_index][0];

            if ($best_offset === null || ($offset < $best_offset) || ($offset === $best_offset && $last_marker_index >= $best_marker_index)) {
                $best_marker_index = $last_marker_index;
                $best_offset = $offset;

                $group_index = $last_group_index;
                $marker_index = $last_marker_index;
            }
        }

        // Process current group and marker
        list($id, $markers) = $this->groups[$group_index];
        list($offset, $params) = $markers[$marker_index];

        $is_first = $marker_index === 0;
        $is_last = $marker_index + 1 === count($markers);

        // Append next group to cursors when processing first marker of last group
        if ($group_index === $last_group_index && $marker_index === 0 && $group_index + 1 < count($this->groups)) {
            $this->cursors[$group_index + 1] = 0;
        }

        // Remove current group from cursors when processing its last marker
        if (++$this->cursors[$group_index] >= count($markers)) {
            unset($this->cursors[$group_index]);
        }

        $value = array($id, $offset - $this->shift++, $is_first, $is_last, $params);

        return true;
    }
}

class Tag
{
    const ALONE = 0;
    const FLIP = 1;
    const PULSE = 2;
    const START = 3;
    const STEP = 4;
    const STOP = 5;
}

function autoload()
{
    static $loaded;

    if (isset($loaded)) {
        return;
    }

    $loaded = true;

    spl_autoload_register(function ($class) {
        $path = dirname(__FILE__);

        switch ($class) {
            case 'Amato\TagConverter':
                require($path . '/converters/tag.php');

                break;

            case 'Amato\CompactEncoder':
                require($path . '/encoders/compact.php');

                break;

            case 'Amato\JSONEncoder':
                require($path . '/encoders/json.php');

                break;

            case 'Amato\SleepEncoder':
                require($path . '/encoders/sleep.php');

                break;

            case 'Amato\FormatRenderer':
                require($path . '/renderers/format.php');

                break;

            case 'Amato\PregScanner':
                require($path . '/scanners/preg.php');

                break;
        }
    });
}
