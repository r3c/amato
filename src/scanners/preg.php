<?php

namespace Amato;

defined('AMATO') or die;

class PregScanner implements Scanner
{
    const CAPTURE_BEGIN		= '<';
    const CAPTURE_DEFAULT	= '#';
    const CAPTURE_END		= '>';
    const CAPTURE_NAME		= '@';
    const DECODE_CAPTURE	= 0;
    const DECODE_PLAIN		= 1;
    const DELIMITER			= '/';
    const ESCAPE			= '%';

    public function __construct($escape = '\\')
    {
        $this->escape = $escape;
        $this->options = preg_match('/^utf-/i', mb_internal_encoding()) ? 'u' : '';
        $this->rules = array();
    }

    /*
    ** Override for Scanner::assign.
    */
    public function assign($pattern)
    {
        $length = strlen($pattern);
        $names = array();
        $parts = array();
        $regex = '';

        for ($i = 0; $i < $length; ++$i) {
            if ($pattern[$i] === self::CAPTURE_BEGIN) {
                $expression = '';

                for (++$i; $i < $length && $pattern[$i] !== self::CAPTURE_DEFAULT && $pattern[$i] !== self::CAPTURE_NAME; ++$i) {
                    $expression .= $pattern[$i] === self::ESCAPE && $i + 1 < $length ? $pattern[++$i] : $pattern[$i];
                }

                if ($i >= $length) {
                    throw new \Exception('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . self::CAPTURE_DEFAULT . '" or "' . self::CAPTURE_NAME . '"');
                }

                $expression = str_replace(self::DELIMITER, '\\' . self::DELIMITER, $expression);
                $mode = $pattern[$i];
                $value = '';

                for (++$i; $i < $length && $pattern[$i] !== self::CAPTURE_END; ++$i) {
                    $value .= $pattern[$i] === self::ESCAPE && $i + 1 < $length ? $pattern[++$i] : $pattern[$i];
                }

                if ($i >= $length) {
                    throw new \Exception('parse error for pattern "' . $pattern . '" at character ' . $i . ', expected "' . self::CAPTURE_END . '"');
                }

                switch ($mode) {
                    case self::CAPTURE_DEFAULT:
                        if (!preg_match(self::DELIMITER . $expression . self::DELIMITER . $this->options, $value)) {
                            throw new \Exception('default value "' . $value . '" must match pattern "' . $expression . '"');
                        }

                        $parts[] = array(self::DECODE_PLAIN, $value);
                        $option = '?:';

                        break;

                    case self::CAPTURE_NAME:
                        $names[] = $value;
                        $parts[] = array(self::DECODE_CAPTURE, $value);
                        $option = '';

                        break;
                }

                $regex .= '(' . $option . $expression . ')';
            } else {
                $count = count($parts);
                $plain = $pattern[$i];

                if ($count > 0 && $parts[$count - 1][0] === self::DECODE_PLAIN) {
                    $parts[$count - 1][1] .= $plain;
                } else {
                    $parts[] = array(self::DECODE_PLAIN, $plain);
                }

                $regex .= preg_quote($plain, self::DELIMITER);
            }
        }

        // Use look-ahead assertion to capture all overlapped matches
        // See: http://stackoverflow.com/questions/22454032/preg-match-all-how-to-get-all-combinations-even-overlapping-ones
        $this->rules[] = array(self::DELIMITER . '(?=(' . $regex . '))' . self::DELIMITER . 'ms' . $this->options, $names, $parts);

        return array(count($this->rules) - 1, $names);
    }

    /*
    ** Override for Scanner::build.
    */
    public function build($key, $captures)
    {
        $tag = '';

        foreach ($this->rules[$key][2] as $part) {
            $tag .= $part[0] === self::DECODE_CAPTURE ? $captures[$part[1]] : $part[1];
        }

        return $tag;
    }

    /*
    ** Override for Scanner:escape.
    */
    public function escape($plain)
    {
        return $this->escape . $plain;
    }

    /*
    ** Override for Scanner::find.
    */
    public function find($string)
    {
        $order = 0;
        $tags = array();

        // Match all escape tags in input string
        if (preg_match_all('/(?=(' . preg_quote($this->escape, '/') . '))/m' . $this->options, $string, $matches, PREG_OFFSET_CAPTURE) === false) {
            throw new \Exception('invalid escape pattern "' . $this->escape . '"');
        }

        foreach ($matches[1] as $match) {
            $length = mb_strlen($match[0]);
            $offset = mb_strlen(substr($string, 0, $match[1]));

            $tags[self::index($offset, $length, $order)] = array(null, $offset, $length);
        }

        // Match all sequence tags in input string
        foreach ($this->rules as $key => $rule) {
            list($pattern, $names) = $rule;

            if (preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false) {
                throw new \Exception('invalid tag pattern "' . $pattern . '"');
            }

            ++$order;

            foreach ($matches as $match) {
                // Copy named groups to captures array
                $captures = array();

                for ($i = min(count($match) - 2, count($names)); $i-- > 0;) {
                    $captures[$names[$i]] = $match[$i + 2][0];
                }

                // Append to tags array, using custom key for fast sorting
                $length = mb_strlen($match[1][0]);
                $offset = mb_strlen(substr($string, 0, $match[1][1]));

                $tags[self::index($offset, $length, $order)] = array($key, $offset, $length, $captures);
            }
        }

        // Order tags by offset ascending, length descending, rule ascending
        ksort($tags);

        return array_values($tags);
    }

    /*
    ** Build array index to allow sorting of tags using "ksort" instead
    ** of "usort" (ugly but way faster).
    */
    private static function index($offset, $length, $order)
    {
        return str_pad($offset, 8, '0', STR_PAD_LEFT) . ':' . str_pad(100000000 - $length, 8, '0', STR_PAD_LEFT) . ':' . str_pad($order, 8, '0', STR_PAD_LEFT);
    }
}
