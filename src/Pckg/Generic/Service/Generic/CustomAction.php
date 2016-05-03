<?php namespace Pckg\Generic\Service\Generic;

use Pckg\Framework\View;

class CustomAction extends Action
{

    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
        $this->class = null;
        $this->method = null;
        $this->order = 0;
    }

    public function getHtml()
    {
        return (string)$this->view;
    }

}