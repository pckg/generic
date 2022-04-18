<?php

namespace Pckg\Generic\Service\Generic;

/**
 * Class Block
 *
 * @package Pckg\Generic\Service\Generic
 */
class Block
{
    /**
     * @var
     */
    protected $name;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Action ...$actions
     *
     * @return $this
     */
    public function addAction(Action ...$actions)
    {
        foreach ($actions as $action) {
            $this->actions[] = $action;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $html = '';

        foreach ($this->actions as $action) {
            $html .= $action->make();
        }

        return $html;
    }
}
