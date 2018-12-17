<?php

namespace Amato;

defined('AMATO') or die;

abstract class Renderer
{
    /*
    ** Render tokenized string.
    ** $token:	tokenized string
    ** $state:	custom state
    ** return:	rendered string
    */
    abstract public function render($token, $state = null);
}
