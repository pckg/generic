<?php

namespace Pckg\Generic\Service\Generic;

/**
 * Class Action
 *
 * @package Pckg\Generic\Service\Generic
 */
class CallableAction extends Action
{

    public function __construct($callable, $order = null)
    {
        $this->order = $order;
        $this->callable = $callable;
    }

    public function getHtml()
    {
        $callable = $this->callable;

        return $callable();
    }

}