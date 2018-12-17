<?php

namespace Amato;

defined('AMATO') or die;

abstract class Converter
{
    /*
    ** Convert markup string to tokenized string.
    ** $markup:		markup string
    ** $context:	optional context information
    ** return:		tokenized string
    */
    abstract public function convert($markup, $context = null);

    /*
    ** Convert tokenized string back to markup string.
    ** $token:		tokenized string
    ** $context:	optional context information
    ** return:		markup string
    */
    abstract public function revert($token, $context = null);
}
