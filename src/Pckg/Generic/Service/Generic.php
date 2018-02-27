<?php

namespace Pckg\Generic\Service;

use Pckg\Auth\Middleware\RestrictGenericAccess;
use Pckg\Concept\Reflect;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\MorphedBy;
use Pckg\Framework\Exception\NotFound;
use Pckg\Framework\Router;
use Pckg\Generic\Controller\Generic as GenericController;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action as ActionRecord;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Resolver\Route as RouteResolver;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\Block;
use Pckg\Locale\Entity\Languages;
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

    public function readRoute(Route $route, $resolvers = false)
    {
        $this->route = $route;

        /**
         * Route resolvers.
         */
        $resolved = [];//(new Router\Command\ResolveDependencies(json_decode($route->resolvers, true)));
        if ($resolvers && $route->resolvers) {
            $router = router()->get();
            foreach (json_decode($route->resolvers, true) as $key => $conf) {
                if (is_array($conf)) {
                    $resolver = array_keys($conf)[0];
                    $resolved[$key] = Reflect::create($resolver)->resolve($conf[$resolver]);
                } else {
                    $resolved[$key] = Reflect::create($conf)->resolve($router[$key] ?? router()->getCleanUri());
                }
                router()->resolve($key, $resolved[$key]);
            }
        }

        $actions = $route->actions(
            function(MorphedBy $actions) {
                // $actions->getMiddleEntity()->joinPermissionTo('read');
                $actions->getMiddleEntity()->withContent(function(BelongsTo $content) {
                    $content->withContents();
                })->withSettings()->withVariable();
            }
        );

        $actions = $actions->sortBy(function($item) {
            return $item->pivot->order;
        })->tree(function($action) {
            return $action->pivot->parent_id;
        }, function($action) {
            return $action->pivot->id;
        });

        $actions->each(
            function(ActionRecord $action) use ($resolved) {
                $this->addAction(
                    $action,
                    $this->route,
                    $resolved
                );
            }
        );
        if ($route->layout) {
            $layoutActions = $route->layout->actions(
                function(MorphedBy $actions) {
                    // $actions->getMiddleEntity()->joinPermissionTo('read');
                    $actions->getMiddleEntity()->withContent(function(BelongsTo $content) {
                        $content->withContents();
                    })->withSettings()->withVariable();
                }
            );
            $layoutActions = $layoutActions->sortBy(function($item) {
                return $item->pivot->order;
            })->tree(function($action) {
                return $action->pivot->parent_id;
            }, function($action) {
                return $action->pivot->id;
            });
            $layoutActions->each(
                function(ActionRecord $action) use ($resolved, $route) {
                    /**
                     * Filter out hidden and shown.
                     */
                    $hide = $action->pivot->getSettingValue('pckg.generic.pageStructure.wrapperLockHide', []);
                    $show = $action->pivot->getSettingValue('pckg.generic.pageStructure.wrapperLockShow', []);

                    if ($show && !in_array($route->id, $show)) {
                        /**
                         * If action has defined show values, hide action if route is not defined.
                         */
                        return;
                    }

                    if ($hide && in_array($route->id, $hide)) {
                        /**
                         * If action has defined hide values, hide actions on current route.
                         */
                        return;
                    }

                    $this->addAction(
                        $action,
                        $this->route,
                        $resolved
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
    public function addAction(
        \Pckg\Generic\Record\Action $action,
        Route $route,
        $resolved = []
    ) {
        $block = $this->touchBlock($action->pivot->variable_id ? $action->pivot->variable->slug : null);

        $block->addAction($action = new Action($action, $route, $resolved));

        return $action;
    }

    /**
     * @return mixed
     */
    public function getVariables()
    {
        $order = $this->makeOrderFromBlocks();
        $variables = $this->getVariablesFromOrder($order);

        return $this->mergeVariables($variables);
    }

    public function getRouteTree()
    {
        $order = $this->makeOrderFromBlocks();

        return $this->getTreeFromOrder($order);
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

    private function getTreeFromOrder($order)
    {
        $tree = [];
        foreach ($order as $ord => $blocks) {
            foreach ($blocks as $block => $actions) {
                foreach ($actions as $action) {
                    $tree[] = $action->getTree();
                }
            }
        }

        return $tree;
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
    public static function addRoutesFromDb()
    {
        $router = router();
        $languages = (new Languages())->/*where('frontend')->*/all()->keyBy('slug');
        $defaultLanguage = $languages->first();
        $multilingual = $languages->count() > 1 && config('multilingual');

        if ($multilingual) {
            /**
             * Copy existing routes to all languages with prefix so translated api things works.
             */
            $existingRoutes = $router->getRoutes();
            foreach ($existingRoutes as $url => $routes) {
                $route = $routes[0];
                router()->removeRouteByName($route['name']);

                foreach ($languages as $language) {
                    /**
                     * Copy only first route.
                     */
                    $route['language'] = $language->slug;
                    if ($language->id != $defaultLanguage->id && !$language->domain) {
                        $router->add(
                            '/' . $language->slug . $url,
                            $route,
                            $route['name'] . ':' . $language->slug,
                            first($language->domain, $defaultLanguage->domain)
                        );
                    } else {
                        $router->add(
                            $url,
                            $route,
                            $route['name'] . ':' . $language->slug,
                            first($language->domain, $defaultLanguage->domain)
                        );
                    }
                }
            }
        }

        $routes = new Routes();
        if (!$routes->getRepository()->getCache()->hasTable('routes')) {
            return;
        }
        $onDefaultDomain = $defaultLanguage->domain == server('HTTP_HOST');

        $arrRoutes = $routes->nonDeleted()
                            ->withAllTranslations()
                            ->all();

        /**
         * Should we load routes by domain?
         */

        foreach ($arrRoutes AS $route) {
            /**
             * Add route to router.
             */
            $existingRouteByName = router()->getRouteByName($route->slug);

            $resolvers = [
                'route' => RouteResolver::class,
            ];
            if ($existingRouteByName) {
                /**
                 * Route already exists, remove and replace it.
                 * Keep existing resolvers.
                 */
                foreach ($existingRouteByName['resolvers'] ?? [] as $key => $res) {
                    $resolvers[$key] = $res;
                }
                router()->removeRouteByName($route->slug);
            }

            /**
             * Generic controller will take care of rendering and all actions.
             */
            $newRoute = [
                "controller" => GenericController::class,
                "view"       => "generic",
                'resolvers'  => $resolvers,
                'tags'       => explode(',', $route->tags),
            ];

            /**
             * Register all translated routes.
             */
            $route->_translations->each(function($routeTranslation) use (
                $route, $multilingual, $defaultLanguage, $existingRouteByName, $newRoute, $router,
                $languages, $onDefaultDomain
            ) {
                /**
                 * Single-lingual is really simple. :)
                 */
                if (!$multilingual) {
                    $router->add(
                        $routeTranslation->route,
                        $newRoute,
                        $route->slug,
                        first($defaultLanguage->domain, server('HTTP_HOST'), config('domain'))
                    );

                    return;
                }

                $routesLanguage = $languages->getKey($routeTranslation->language_id);
                if (!$routesLanguage) {
                    /**
                     * Language is not enabled on frontend?
                     */
                    return;
                }

                $newRoute['language'] = $routeTranslation->language_id;
                $domain = $routesLanguage->domain ?? $defaultLanguage->domain;
                $url = $routesLanguage->domain
                    ? $routeTranslation->route
                    : rtrim('/' . $routeTranslation->language_id . $routeTranslation->route, '/');

                $router->add(
                    $url,
                    $newRoute,
                    $route->slug . ':' . $routeTranslation->language_id,
                    $domain
                );
            });
        }
    }

}