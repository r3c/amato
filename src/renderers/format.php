<?php

namespace Amato;

defined('AMATO') or die;

class FormatRendererState implements \ArrayAccess
{
    private $params;
    private $values;

    public function __construct($params)
    {
        $this->params = $params;
        $this->values = array();
    }

    public function append($params)
    {
        $this->values = $this->params + $this->values;
        $this->params = $params;
    }

    public function forget($offset)
    {
        unset($this->values[$offset]);
    }

    public function get($offset, $default = null)
    {
        if (isset($this->params[$offset])) {
            return $this->params[$offset];
        }

        return $this->last($offset, $default);
    }

    public function last($offset, $default = null)
    {
        if (isset($this->values[$offset])) {
            return $this->values[$offset];
        }

        return $default;
    }

    public function offsetExists($offset)
    {
        return isset($this->params[$offset]) || isset($this->values[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset !== null) {
            $this->params[$offset] = $value;
        } else {
            $this->params[] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
        unset($this->params[$offset]);
    }
}

class FormatRenderer extends Renderer
{
    /*
    ** Initialize a new renderer.
    ** $encoder:	encoder instance
    ** $format:		render (id, render) declaration
    ** $escape:		optional plain text escape callback (string) -> string
    */
    public function __construct($encoder, $format, $escape = null)
    {
        $this->encoder = $encoder;
        $this->escape = $escape;
        $this->format = $format;
    }

    /*
    ** Override for Renderer::render.
    */
    public function render($token, $context = null)
    {
        // Parse tokenized string
        $pack = $this->encoder->decode($token);

        if ($pack === null) {
            return null;
        }

        list($render, $groups) = $pack;

        // Process all marker groups
        $escape = $this->escape;
        $last = 0;
        $scopes = array();
        $stop = 0;

        for ($cursors = Encoder::begin($groups); Encoder::next($groups, $cursors, $next);) {
            list($id, $offset, $is_first, $is_last, $params) = $next;

            // Get start and stop offsets of plain text since last position
            $start = $stop;
            $stop += $offset - $last;
            $last = $offset;

            // Escape plain text using provided callback if any
            if ($escape !== null) {
                $length = $stop - $start;
                $plain = $escape(mb_substr($render, $start, $length));

                $render = mb_substr($render, 0, $start) . $plain . mb_substr($render, $stop);
                $stop += mb_strlen($plain) - $length;
            }

            // Get formatting rule for current marker if any
            if (!isset($this->format[$id]) || !isset($this->format[$id][0])) {
                continue;
            }

            // Create and insert new scope according to its precedence level
            if ($is_first) {
                $callback = $this->format[$id][0];
                $level = isset($this->format[$id][1]) ? $this->format[$id][1] : 1;

                for ($scope_shift = count($scopes); $scope_shift > 0 && $level > $scopes[$scope_shift - 1][3];) {
                    --$scope_shift;
                }

                array_splice($scopes, $scope_shift, 0, array(array($id, $stop, $callback, $level, new FormatRendererState($params))));

                $scope_current = $scope_shift + ($is_last ? 0 : 1);
            }

            // Find existing scope matching current marker id, cancel if none
            else {
                for ($scope_shift = count($scopes) - 1; $scope_shift >= 0 && $scopes[$scope_shift][0] !== $id;) {
                    --$scope_shift;
                }

                if ($scope_shift < 0) {
                    continue;
                }

                $scopes[$scope_shift][4]->append($params);
                $scope_current = $scope_shift;
            }

            // Invoke callback of both crossed scopes and current one
            for ($i = count($scopes) - 1; $i >= $scope_current; --$i) {
                list($id, $start, $callback, $level, $state) = $scopes[$i];

                // Fast-forward offset of current one if just added and about to be closed
                if ($i === $scope_current && $is_first && $is_last) {
                    $start = $stop;
                }

                // Invoke callback to generate markup code and insert to string
                $length = $stop - $start;
                $markup = $callback(mb_substr($render, $start, $length), $state, $i !== $scope_current || $is_last, $context);

                $render = mb_substr($render, 0, $start) . $markup . mb_substr($render, $stop);
                $stop += mb_strlen($markup) - $length;
            }

            // Remove scope from stack when closed
            if ($is_last) {
                array_splice($scopes, $scope_current, 1);
            }

            // Shift offset of both crossed scopes and current one
            for ($i = count($scopes) - 1; $i >= $scope_shift; --$i) {
                $scopes[$i][1] = $stop;
            }
        }

        // Escape remaining plain text using provided callback if any
        if ($escape !== null) {
            $render = mb_substr($render, 0, $stop) . $escape(mb_substr($render, $stop));
        }

        return $render;
    }
}
