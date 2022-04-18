<?php

namespace Pckg\Generic\Service\Generic;

class CustomAction extends Action
{
    protected $view;
    public function __construct($view)
    {
        $this->view = $view;
    }

    public function getHtml($innerOnly = false)
    {
        return (string)$this->view;
    }

    public function getOrder()
    {
        return null;
    }

    public function getClass()
    {
        return null;
    }

    public function getMethod()
    {
        return null;
    }
}
