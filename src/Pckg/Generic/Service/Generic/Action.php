<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View;
use Pckg\Generic\Record\Content;
use Pckg\Generic\Record\Setting;

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
     * @var
     */
    protected $template;

    /**
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct($class, $method, $args = [], $order = null, $template = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
        $this->args = $args;
        $this->template = $template;
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
     * @return mixed|null|Content
     */
    public function getContent()
    {
        return $this->args['content'] ?? null;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHtml()
    {
        if ($this->class && $this->method) {
            $prefix = strtolower(request()->method());

            $args = array_merge($this->args, ['action' => $this, 'content' => $this->getContent()]);
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

            /**
             * Get plugin output.
             */
            $pluginService = new Plugin();
            $result = $pluginService->make($this->class, $this->method, $args, true, false);

            /**
             * Array should be returned directly.
             */
            if (is_array($result)) {
                return $result;
            }

            /**
             * Allow custom template.
             */
            if ($this->template && $result instanceof View\Twig) {
                $result->setFile($this->template);
            }

            /**
             * Parse view to string in all cases.
             */
            $result = (string)$result;

            /**
             * Prepare comments for dev environment.
             */
            $devPrefix = null;
            $devSuffix = null;
            if (dev() || implicitDev()) {
                $devPrefix = '<!-- start action ' . $this->class . '::' . $method . ' -->' . "\n";
                $devSuffix = '<!-- end action ' . $this->class . '::' . $method . ' -->' . "\n";
            }

            /**
             * Return built output.
             */
            return $devPrefix . $result . $devSuffix;
        }
    }

}