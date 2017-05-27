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

    protected $container;

    protected $type;

    /**
     * @var \Pckg\Generic\Record\Action
     */
    protected $action;

    /**
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct(
        $class, $method, $args = [], $order = null, $template = null, $width = null, $background = null,
        $container = null, $type = null, \Pckg\Generic\Record\Action $actionRecord
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
        $this->args = $args;
        $this->template = $template;
        $this->width = $width;
        $this->background = $background;
        $this->container = $container;
        $this->type = $type;
        $this->action = $actionRecord;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getBackground()
    {
        return $this->background;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed|null|Content
     */
    public function getContent()
    {
        return $this->args['content'] ?? null;
    }

    public function getSubHtml()
    {
        $html = [];

        foreach ($this->action->getChildren as $action) {
            $genericAction = new Action(
                $action->class,
                $action->method,
                [
                    'content'   => $action->pivot->content,
                    'settings'  => $action->pivot->settings,
                    'route'     => $this->args['route'],
                    'resolvers' => $this->args['resolvers'],
                ],
                $action->pivot->order,
                $action->pivot->template,
                $action->pivot->width,
                $action->pivot->background,
                $action->pivot->container,
                $action->pivot->type,
                $action
            );

            $html[] = $genericAction->getHtml();
        }

        return implode($html);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHtml()
    {
        if ($this->type == 'container') {
            return '<div class="container container-' . $this->action->pivot->id . '">' . $this->getSubHtml() . '</div>';
        } else if ($this->type == 'row') {
            return '<div class="row row-' . $this->action->pivot->id . '">' . $this->getSubHtml() . '</div>';
        } else if ($this->type == 'column') {
            return '<div class="col-md-12 column-' . $this->action->pivot->id . '">' . $this->getSubHtml() . '</div>';
        }

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