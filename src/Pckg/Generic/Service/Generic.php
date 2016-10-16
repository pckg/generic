<?php

namespace Pckg\Generic\Service;

use Pckg\Framework\Router;
use Pckg\Generic\Controller\Generic as GenericController;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action as ActionRecord;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Resolver\Route as RouteResolver;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\Block;

/**
 * Class Generic
 *
 * @package Pckg\Generic\Service
 */
class Generic
{

    /**
     * @var array
     */
    protected $blocks = [];

    protected $route;

    /**
     * @param Block ...$blocks
     *
     * @return $this
     */
    public function addBlock(Block ...$blocks)
    {
        foreach ($blocks as $block) {
            $this->blocks[] = $block;
        }

        return $this;
    }

    /**
     * @param Block ...$blocks
     *
     * @return Block
     */
    public function touchBlock(...$blocks)
    {
        foreach ($blocks as $block) {
            if (!isset($this->blocks[$block])) {
                $this->blocks[$block] = new Block($block);
            }
        }

        return $this->blocks[$block];
    }

    public function readRoute(Route $route)
    {
        $this->route = $route;

        $route->actions->each(
            function(ActionRecord $action) {
                $this->addAction(
                    $action->pivot->variable->slug,
                    $action->class,
                    $action->method,
                    [
                        'content'  => $action->pivot->content,
                        'settings' => $action->pivot->settings,
                        'route'    => $this->route,
                    ]
                );
            }
        );

        if ($route->layout) {
            $route->layout->actions->each(
                function(ActionRecord $action) {
                    $this->addAction(
                        $action->pivot->variable->slug,
                        $action->class,
                        $action->method,
                        [
                            'content'  => $action->pivot->content,
                            'settings' => $action->pivot->settings,
                            'route'    => $this->route,
                        ]
                    );
                }
            );
        }
    }

    /**
     * @param $variable
     * @param $class
     * @param $method
     *
     * @return Action
     */
    public function addAction($variable, $class, $method, $args = [])
    {
        $block = $this->touchBlock($variable);

        $block->addAction($action = new Action($class, $method, $args));

        return $action;
    }

    /**
     * @return mixed
     */
    public function getVariables()
    {
        return $this->mergeVariables($this->getVariablesFromOrder($this->makeOrderFromBlocks()));
    }

    /**
     * @return array
     */
    private function makeOrderFromBlocks()
    {
        $order = [];
        foreach ($this->blocks as $block) {
            foreach ($block->getActions() as $action) {
                $order[$action->getOrder()][$block->getName()][] = $action;
            }
        }

        asort($order);

        return $order;
    }

    /**
     * @param $order
     *
     * @return array
     */
    private function getVariablesFromOrder($order)
    {
        $variables = [];
        foreach ($order as $order => $blocks) {
            foreach ($blocks as $block => $actions) {
                foreach ($actions as $action) {
                    startMeasure(
                        'Getting output: ' . $action->getClass() . ' ' . $action->getMethod(
                        ) . ' ' . $block . ' ' . $order
                    );
                    $variables[$block][] = $action->getHtml();
                    stopMeasure(
                        'Getting output: ' . $action->getClass() . ' ' . $action->getMethod(
                        ) . ' ' . $block . ' ' . $order
                    );
                }
            }
            
        }

        return $variables;
    }

    /**
     * @param $variables
     *
     * @return mixed
     */
    private function mergeVariables($variables)
    {
        foreach ($variables as &$contents) {
            $isArray = false;
            foreach ($contents as $content) {
                if (is_array($content)) {
                    $isArray = true;
                    break;
                }
            }
            
            if (!$isArray) {
                $contents = implode($contents);
            }
        }

        return $variables;
    }

    /**
     * @param Routes $routes
     * @param Router $router
     */
    public static function addRoutesFromDb(Routes $routes, Router $router)
    {
        if (!$routes->getRepository()->getCache()->hasTable('routes')) {
            return;
        }

        $arrRoutes = $routes->joinTranslation()->all();

        foreach ($arrRoutes AS $route) {
            $router->add(
                $route->getRoute(false),
                [
                    "controller" => GenericController::class,
                    "view"       => "generic",
                    'resolvers'  => [
                        'route' => RouteResolver::class,
                    ],
                ],
                $route->slug
            );
        }
    }

}