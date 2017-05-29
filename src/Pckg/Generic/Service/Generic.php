<?php

namespace Pckg\Generic\Service;

use Pckg\Auth\Middleware\RestrictGenericAccess;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\MorphedBy;
use Pckg\Framework\Exception\NotFound;
use Pckg\Framework\Router;
use Pckg\Generic\Controller\Generic as GenericController;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action as ActionRecord;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Resolver\Route as RouteResolver;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\Block;
use Throwable;

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

    public function authCheckRoute()
    {
        try {
            $route = (new RouteResolver())->resolve(router()->getUri());
        } catch (NotFound $e) {
            return true;
        }

        return $route->hasPermissionToView();
    }

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

        /**
         * Custom resolvers.
         */
        $resolvers = [];
        if ($route->resolvers) {
            foreach (json_decode($route->resolvers) as $key => $resolver) {
                $resolvers[$key] = $resolver;
            }
        }

        $actions = $route->actions(
            function(MorphedBy $actions) {
                // $actions->getMiddleEntity()->joinPermissionTo('read');
                $actions->getMiddleEntity()->withContent(function(BelongsTo $content) {
                    $content->joinTranslations();
                });
            }
        );

        $actions = $actions->sortBy(function($item){
            return $item->pivot->order;
        })->tree(function($action) {
            return $action->pivot->parent_id;
        }, function($action) {
            return $action->pivot->id;
        });

        $actions->each(
            function(ActionRecord $action) use ($resolvers) {
                $this->addAction(
                    $action,
                    $this->route,
                    $resolvers
                );
            }
        );

        /*if ($route->layout) {
            $layoutActions = $route->layout->actions(
                function(MorphedBy $actions) {
                    // $actions->getMiddleEntity()->joinPermissionTo('read');
                    $actions->getMiddleEntity()->withContent(function(BelongsTo $content) {
                        $content->joinTranslations();
                    });
                }
            );
            $layoutActions->each(
                function(ActionRecord $action) use ($resolvers) {
                    $this->addAction(
                        $action,
                        $this->route,
                        $resolvers
                    );
                }
            );
        }*/
    }

    /**
     * @param $variable
     * @param $class
     * @param $method
     *
     * @return Action
     */
    public function addAction(
        \Pckg\Generic\Record\Action $action,
        Route $route,
        $resolvers = []
    ) {
        $block = $this->touchBlock($action->pivot->variable_id ? $action->pivot->variable->slug : null);

        $block->addAction($action = new Action($action, $route, $resolvers));

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

        ksort($order);

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
        foreach ($order as $ord => $blocks) {
            foreach ($blocks as $block => $actions) {
                foreach ($actions as $action) {
                    startMeasure(
                        'Getting output: ' . $action->getClass() . ' ' . $action->getMethod() . ' ' . $block . ' ' .
                        $ord
                    );
                    try {
                        $html = $action->getHtml();

                        if (is_array($html)) {
                            $variables[$block][] = $html;
                            continue;
                        }

                        //$html = '<div class="pckg-action">' . $html . '</div>';

                        $variables[$block][] = (string)$html;
                    } catch (Throwable $e) {
                        if (dev()) {
                            throw $e;
                        }
                    }
                    stopMeasure(
                        'Getting output: ' . $action->getClass() . ' ' . $action->getMethod() . ' ' . $block . ' ' .
                        $ord
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
        if (request()->isPost() && is_array($variables['content'][0])) {
            return $variables['content'][0];
        }

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
            /**
             * Add route to router.
             */
            $url = $route->getRoute(false);
            $existingRoute = router()->getRoute($url);
            if ($existingRoute) {
                $router->replace($url, [
                    "controller" => GenericController::class,
                    "view"       => "generic",
                    'resolvers'  => [
                        'route' => RouteResolver::class,
                    ],
                    'tags'       => explode(',', $route->tags),
                ]);
            } else {
                $router->add(
                    $url,
                    [
                        "controller" => GenericController::class,
                        "view"       => "generic",
                        'resolvers'  => [
                            'route' => RouteResolver::class,
                        ],
                        'tags'       => explode(',', $route->tags),
                    ],
                    $route->slug
                );
            }
        }
    }

}