<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Framework\Router\Command\ResolveDependencies;
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

    protected $width;

    protected $background;

    /**
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct(
        $class, $method, $args = [], $order = null, $template = null, $width = null, $background = null
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
        $this->args = $args;
        $this->template = $template;
        $this->width = $width;
        $this->background = $background;
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

            if (isset($args['resolvers'])) {
                $resolved = (new ResolveDependencies(router(), $args['resolvers']))->execute();
                foreach ($resolved as $key => $val) {
                    $args[$key] = $val;
                }
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
            if ($result instanceof View\Twig && $this->template) {
                $result->setFile(str_replace(':', '/View/', $this->template));
            }

            /**
             * Parse view to string in all cases.
             */
            $result = (string)$result;

            /**
             * Add some width and background classes.
             */
            $classes = [];
            if ($this->width) {
                $classes[] = 'width-' . $this->width;
            }

            if ($this->background) {
                $classes[] = 'background-' . $this->background;
            }

            if ($classes) {
                $result = '<div class="generic-action ' . implode(' ', $classes) . '">' . $result . '</div>';
            }

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