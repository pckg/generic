<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Concept\Reflect;
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
            $prefix = strtolower(request()->getMethod());

            $args = array_merge($this->args, ['action' => $this]);
            $controller = Reflect::create($this->class, $args);
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

            $result = null;
            $e = null;
            try {
                $result = Reflect::method($controller, $method, $args);
            } catch (Throwable $e) {
                if (prod()) {
                    return null;
                }

                throw $e;
            }

            if (is_array($result)) {
                return $result;

            } else {
                return '<!-- ' . $this->class . '::' . $method . ' start -->' . ($e ? 'Exception: ' . exception(
                        $e
                    ) : $result) . '<!-- ' . $this->class . '::' . $method . ' end -->';

            }
        }
    }

}