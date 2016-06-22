<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Concept\Reflect;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Generic\Record\Action as ActionRecord;
use Pckg\Generic\Record\ActionsLayout;
use Pckg\Generic\Record\ActionsRoute;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Record\Setting;
use Pckg\Generic\Record\SettingsMorph;

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
    public function __construct($class, $method, $args = [], $order = null) {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
        $this->args = $args;
    }

    public function getOrder() {
        return $this->order;
    }

    public function getClass() {
        return $this->class;
    }

    public function getMethod() {
        return $this->method;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHtml() {
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
                        $setting->poly->resolve($args);
                    }
                );
            }

            try {
                $result = (string)Reflect::method($controller, $method, $args);
            } catch (\Exception $e) {
                if (env()->isDev()) {
                    throw $e;
                }
                throw $e;

                // @T00D00 - log error!
                return;
            }

            return '<!-- ' . $this->class . '::' . $method . ' start -->' . $result . '<!-- ' . $this->class . '::' . $method . ' end -->';
        }
    }

}