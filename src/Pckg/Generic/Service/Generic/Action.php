<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Framework\Router\Command\ResolveDependencies;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View;
use Pckg\Generic\Record\Action as ActionRecord;
use Pckg\Generic\Record\Content;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Record\Setting;

/**
 * Class Action
 *
 * @package Pckg\Generic\Service\Generic
 */
class Action
{

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var ActionRecord
     */
    protected $action;

    /**
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct(ActionRecord $action, Route $route, $resolvers = [])
    {
        $this->args = [
            'content'   => $action->pivot->content,
            'settings'  => $action->pivot->settings,
            'route'     => $route,
            'resolvers' => $resolvers,
        ];
        $this->action = $action;
    }

    public function getOrder()
    {
        return $this->action->pivot->order;
    }

    public function getWidth()
    {
        return $this->action->pivot->width;
    }

    public function getContainer()
    {
        return $this->action->pivot->container;
    }

    public function getBackground()
    {
        return $this->action->pivot->background;
    }

    public function getClass()
    {
        return $this->action->class;
    }

    public function getMethod()
    {
        return $this->action->method;
    }

    public function getType()
    {
        return $this->action->pivot->type;
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
            $genericAction = new Action($action, $this->args['route'], $this->args['resolvers']);

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
        $return = null;
        if (in_array($this->getType(), ['wrapper', 'container', 'row', 'column'])) {
            return '<div class="' . $this->action->htmlClass . '" style="' . $this->action->htmlStyle . '">' .
                   $this->getSubHtml() . '</div>';
        }

        $return = '<div class="' . $this->action->htmlClass . '" style="' . $this->action->htmlStyle . '">';
        if ($this->getClass() && $this->getMethod()) {
            $prefix = strtolower(request()->method());

            $args = array_merge($this->args, ['action' => $this, 'content' => $this->getContent()]);
            $method = ($prefix ? $prefix . ucfirst($this->getMethod()) : $this->getMethod()) . 'Action';

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
            $result = $pluginService->make($this->getClass(), $this->getMethod(), $args, true, false);

            /**
             * Array should be returned directly.
             */
            if (is_array($result)) {
                return $result;
            }

            /**
             * Allow custom template.
             */
            if ($result instanceof View\Twig && $this->action->pivot->template) {
                $result->setFile(str_replace(':', '/View/', $this->action->pivot->template));
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
                $devPrefix = '<!-- start action ' . $this->getClass() . '::' . $method . ' -->' . "\n";
                $devPrefix .= '<a href="/dev.php/tools/page-structure?route=' . router()->resolved('route')->id .
                              '&action=' .
                              $this->action->pivot->id .
                              '" style="position: absolute; z-index: 9999;" class="btn btn-xs btn-info" target="_blank">Edit action</a>';
                $devSuffix = '<!-- end action ' . $this->getClass() . '::' . $method . ' -->' . "\n";
            }

            /**
             * Return built output.
             */
            $return .= $devPrefix . $result . $devSuffix;
        }
        $return .= '</div>';

        return $return;
    }

}