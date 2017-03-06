<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Concept\Reflect;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Record\Setting;
use Throwable;

/**
 * Class Action
 *
 * @package Pckg\Generic\Service\Generic
 */
class Action
{

    /**
     * @var
     */
    protected $class;

    /**
     * @var
     */
    protected $method;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var null
     */
    protected $order;

    /**
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct($class, $method, $args = [], $order = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
        $this->args = $args;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHtml()
    {
        if ($this->class && $this->method) {

            $prefix = strtolower(request()->method());

            $args = array_merge($this->args, ['action' => $this]);
            $method = ($prefix ? $prefix . ucfirst($this->method) : $this->method) . 'Action';

            if (isset($args['settings'])) {
                /**
                 * We need to resolve dependencies. ;-)
                 */
                $args['settings']->each(
                    function(Setting $setting) use (&$args) {
                        $setting->pivot->resolve($args);
                    }
                );
            }

            $pluginService = new Plugin();
            $result = $pluginService->make($this->class, $this->method, $args, true);

            if (is_array($result)) {
                return $result;
            } else {
                return '<!-- start action ' . $this->class . '::' . $method . ' -->' .
                       $result .
                       '<!-- end action ' . $this->class . '::' . $method . ' -->';
            }
        }
    }

}