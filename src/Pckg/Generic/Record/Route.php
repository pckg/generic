<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Generic\Entity\Routes;

/**
 * Class Route
 * @package Pckg\Generic\Record
 */
class Route extends Record
{

    /**
     * @var
     */
    protected $entity = Routes::class;

    public function getLayoutName()
    {
        return $this->layout
            ? $this->layout->slug
            : null;
    }

    public function getRoute()
    {
        return env()->getUrlPrefix() . $this->getValue('route');
    }

}