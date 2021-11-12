<?php

namespace Pckg\Generic\Service;

use Comms\Hub\Api;
use Derive\Platform\Entity\Platforms;
use Pckg\Auth\Middleware\RestrictGenericAccess;
use Pckg\Collection;
use Pckg\Concept\Reflect;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\MorphedBy;
use Pckg\Framework\Exception\NotFound;
use Pckg\Framework\Router;
use Pckg\Generic\Controller\Generic as GenericController;
use CommsCenter\Pagebuilder\Entity\Actions;
use Pckg\Generic\Entity\DataAttributes;
use CommsCenter\Pagebuilder\Entity\Layouts;
use CommsCenter\Pagebuilder\Entity\Routes;
use Pckg\Generic\Entity\SettingsMorphs;
use CommsCenter\Pagebuilder\Record\Action as ActionRecord;
use CommsCenter\Pagebuilder\Record\ActionsMorph;
use CommsCenter\Pagebuilder\Record\Layout;
use CommsCenter\Pagebuilder\Record\Route;
use Pckg\Generic\Record\Setting;
use CommsCenter\Pagebuilder\Resolver\Route as RouteResolver;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\Block;
use Pckg\Generic\Service\Partial\AbstractPartial;
use Throwable;

/**
 * Class Generic
 *
 * @package Pckg\Generic\Service
 * @property ActionsMorph pivot
 */
class Generic
{

    /**
     * @var array
     */
    protected $blocks = [];

    protected $route;

    /**
     * @var Collection
     */
    protected $actions;

    protected $metadata = [];

    public function __construct()
    {
        /**
         * Force this to be singleton.
         */
        $this->actions = new Collection();
        if (!context()->exists(static::class)) {
            context()->bind(static::class, $this);
        }
    }

    public function pushMetadata($actionId, $key, $value, $store = true)
    {
        if ($store) {
            $this->metadata[$actionId][$key] = $value;
        }

        return '$store.state.generic.metadata[\'' . $actionId . '\'][\'' . $key . '\']';
    }

    public function getMetaData()
    {
        return $this->metadata;
    }

    public function setActions(Collection $actions)
    {
        $this->actions = $actions;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getKeyedFlatActions()
    {
        return collect($this->actions)->map(function (ActionRecord $action) {
            return new Action($action);
        })->keyBy(function (Action $action) {
            return $action->getAction()->pivot->id;
        })->map(function (Action $action) {
            return $action->getFlat();
        })->all();
    }

    /**
     * Return all custom styles from the database.
     */
    public function getStyles()
    {
        return (new DataAttributes())->whereArr([
            'morph_id' => Generic::class,
            'slug' => 'styles',
        ])->orderBy('poly_id')
            ->all()
            ->keyBy('poly_id') // selector that holds array with {device, state, css}
            ->mapFn('json_decode')
            ->all();
    }

    public function authCheckRoute()
    {
        try {
            $route = (new RouteResolver())->resolve(router()->getUri());
        } catch (NotFound $e) {
            return true;
        }

        return $route->hasPermissionToView();
    }

    public function getGenericRoutes()
    {
        return (new Routes())->joinTranslations()->withSettings()->nonDeleted()->all()->map(function (Route $route) {
                return $route->forPageStructure();
        })->all();
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
     * @param string ...$blocks
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

    public function hasAction(array $actions = [])
    {
        return $this->actions && $this->actions->has(function (\CommsCenter\Pagebuilder\Record\Action $action) use ($actions) {
            return in_array($action->slug, $actions);
        });
    }

    public function readRoute(Route $route, $resolvers = true, $nativeResolvers = null)
    {
        $this->route = $route;

        /**
         * Route resolvers.
         */
        $resolved = [];
        if ($resolvers && $route->resolvers) {
            $router = is_null($nativeResolvers) ? router()->get() : $nativeResolvers;
            $decoded = @json_decode($route->resolvers, true);
            foreach ($decoded ?? [] as $key => $conf) {
                if (is_array($conf)) {
                    $resolver = array_keys($conf)[0];
                    $resolved[$key] = Reflect::create($resolver)->resolve($conf[$resolver]);
                } else {
                    $resolved[$key] = Reflect::create($conf)->resolve($router[$key] ?? router()->getCleanUri());
                }
                router()->resolve($key, $resolved[$key]);
            }
        }

        $this->actions = $route->actions(function (MorphedBy $actions) {
            // $actions->getMiddleEntity()->joinPermissionTo('read');
            $actions->getMiddleEntity()->withContent(function (BelongsTo $content) {
                $content->withContents();
            })->withSettings(function (MorphedBy $settings) {
                $settings->getMiddleEntity()->withSetting();
            })->withAction();
        });

        $actions = $this->actions->sortBy(function ($item) {
            return $item->pivot->order;
        })->tree(function ($action) {
            return $action->pivot->parent_id;
        }, function ($action) {
            return $action->pivot->id;
        });

        $actions->each(
            function (ActionRecord $action) use ($resolved) {
                $this->addAction(
                    $action,
                    $this->route,
                    $resolved
                );
            }
        );

        $this->readLayout($route->layout, $resolved, $route);
    }

    public function readSystemRoute($template)
    {
        $repository = (new Layouts())->getRepository();
        if (!$repository || !$repository->getCache()->hasTable('layouts')) {
            return;
        }

        $layout = Layout::gets(['template' => $template]);

        if (!$layout) {
            return;
        }

        $this->readLayout($layout);
    }

    protected function readLayout(Layout $layout = null, $resolved = [], Route $route = null)
    {
        if (!$layout) {
            return;
        }

        $layoutActions = true
            ? $layout->actions(function (MorphedBy $actions) {
            // $actions->getMiddleEntity()->joinPermissionTo('read');
                $actions->getMiddleEntity()->withContent(function (BelongsTo $content) {
                    $content->withContents();
                })->withSettings(function (MorphedBy $settings) {
                    $settings->getMiddleEntity()->withSetting();
                })->withAction();
            })
            : cache(
                Generic::class . ':readLayout:' . $layout->id,
                function () use ($layout) {
                    return $layout->actions(function (MorphedBy $actions) {
                        // $actions->getMiddleEntity()->joinPermissionTo('read');
                        $actions->getMiddleEntity()->withContent(function (BelongsTo $content) {
                            $content->withContents();
                        })->withSettings(function (MorphedBy $settings) {
                            $settings->getMiddleEntity()->withSetting();
                        });
                    });
                },
                'app',
                1
            );

        $layoutActions = $layoutActions->sortBy(function ($item) {
            return $item->pivot->order;
        })->tree(function ($action) {
            return $action->pivot->parent_id;
        }, function ($action) {
            return $action->pivot->id;
        })->filter(function (ActionRecord $action) use ($route) {
            /**
             * Filter out hidden and shown.
             */
            if ($route) {
                $hide = $action->pivot->getSettingValue('pckg.generic.pageStructure.wrapperLockHide', []);
                if ($hide && in_array($route->id, $hide)) {
                    /**
                     * If action has defined hide values, hide actions on current route.
                     */
                    return false;
                }

                $show = $action->pivot->getSettingValue('pckg.generic.pageStructure.wrapperLockShow', []);
                if ($show && !in_array($route->id, $show)) {
                    /**
                     * If action has defined show values, hide action if route is not defined.
                     */
                    return false;
                }
            } else {
                $system = $action->pivot->getSettingValue('pckg.generic.pageStructure.wrapperLockSystem', []);
                if (!in_array(router()->getName(), $system)) {
                    return false;
                }
            }

            return true;
        });

        $layoutActions->each(
            function (ActionRecord $action) use ($resolved, $route) {

                $this->addAction(
                    $action,
                    $route ?? new Route(),
                    $resolved
                );

                //if ($route) {
                //$layoutActions->each(function(ActionRecord $action){
                    $this->recursiveAddAction($action);
                //});
                //}
            }
        );
    }

    protected function recursiveAddAction(ActionRecord $action)
    {
        $this->actions->push($action);
        collect($action->getChildren)->each(function (ActionRecord $action) {
            $this->recursiveAddAction($action);
        });
    }

    /**
     * @param $variable
     * @param $class
     * @param $method
     *
     * @return Action
     */
    public function addAction(
        \CommsCenter\Pagebuilder\Record\Action $action,
        Route $route,
        $resolved = []
    ) {
        $block = $this->touchBlock($action->pivot->variable ?? 'content');

        $block->addAction($genericAction = new Action($action, $route, $resolved));

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

                        $string = (string)$html;
                        $variables[$block][] = $string;
                    } catch (Throwable $e) {
                        if (!prod()) {
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

    public function build()
    {
        $args = array_merge(router()->get('data'), router()->getResolves());

        $this->actions->each(function (ActionRecord $action) use ($args) {
            (new Action($action))->build($args);
        });
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
        /**
         * We should cache routes.
         */
        $router = router();
        /*$cacheName = Generic::class . ':addRoutesFromDb';
        $cache = cache()->getAppCache();

        if (false && $cache->contains($cacheName)) {
            $routes = unserialize($cache->fetch($cacheName));
            $router->setRoutes($routes);
            return;
        }*/

        /**
         *
         */
        $localeManager = localeManager();
        $languages = $localeManager->getFrontendLanguages()->keyBy('slug');
        $defaultLanguage = $localeManager->getDefaultFrontendLanguage();
        $multilingual = $localeManager->isMultilingual();

        if (false && $multilingual) {
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
        if (!$defaultLanguage || !$routes->getRepository()->getCache()->hasTable('routes')) {
            return;
        }
        $onDefaultDomain = $defaultLanguage->domain == server('HTTP_HOST');

        /*if (!auth()->isLoggedIn() || (!auth()->isAdmin() && auth()->getGroupId() != 8)) {
            $routes->where('published_at', date('Y-m-d H:i'), '<=');
        }*/

        $arrRoutes = $routes->nonDeleted()
                            ->whereHas('routes.slug')
                            ->withLayout()
                            ->withAllTranslations(function (HasMany $translations) use ($languages) {
                                /**
                                 * Load only active translations.
                                 */
                                $translations->where('language_id', $languages->keys());
                                $translations->getRightEntity()->joinLanguage()->orderBy('`default` ASC');
                            })
                            ->all();

        /**
         * When theme setup is not finished.
         * We need config loaded here. :/
         */
        if (!$arrRoutes->count()) {
            router()->add(
                '/',
                [
                    'tags' => ['vue:route', 'layout:backend', 'layout:focused', 'group:admin', 'vue:route:template' => '<derive-setup-themify></derive-setup-themify>'],
                    'view' => function () {
                        return view('Pckg/Generic/View/backend', ['content' => '<pckg-app data-frontend></pckg-app>']);
                    }
                ],
                'homepage'
            );

            return;
        }

        /**
         * Should we load routes by domain?
         */
        foreach ($arrRoutes as $route) {
            $resolvers = [
                'route' => RouteResolver::class,
            ];

            /**
             * Add route to router.
             * @deprecated
             */
            /*$existingRouteByName = router()->getRouteByName($route->slug);

            if ($existingRouteByName) {
                / **
                 * Route already exists, remove and replace it.
                 * Keep existing resolvers.
                 * Where was this used? Kalypso offers? Old system?
                 * /
                foreach ($existingRouteByName['resolvers'] ?? [] as $key => $res) {
                    $resolvers[$key] = $res;
                }
                router()->removeRouteByName($route->slug);
                message('Removing system route ' . $route->slug);
            }*/

            $routeResolvers = $route->resolvers;
            if ($routeResolvers) {
                $routeResolvers = (array)json_decode($routeResolvers, true);
                foreach ($routeResolvers as $key => $res) {
                    $resolvers[$key] = $res;
                }
            }

            /**
             * Generic controller will take care of rendering and all actions.
             */
            $tags = stringify($route->tags)->explodeToCollection(',')->removeEmpty();

            if ($route->layout && strpos($route->layout->template, 'frontend') !== false) {
                $tags->push('layout:frontend');
            }

            /**
             * Mark as Vue route.
             */
            $tags->push('vue:route');
            $tags->push('session:close');
            $tags->push($route->id, 'generic:id');
            $tags->push('<pckg-app data-frontend></pckg-app>', 'vue:route:template');

            $tags = $tags->all();

            $newRoute = [
                "controller" => GenericController::class,
                "view"       => "generic",
                'resolvers'  => $resolvers,
                'tags'       => $tags,
            ];

            /**
             * Register all translated routes.
             */
            $route->_translations->each(function ($routeTranslation) use (
                $route,
                $multilingual,
                $defaultLanguage,
                $newRoute,
                $router,
                $languages
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
                $langPrefix = !$routesLanguage->default ? '/' . $routeTranslation->language_id : '';
                $url = $routesLanguage->domain
                    ? $routeTranslation->route
                    : ($langPrefix . $routeTranslation->route);

                $router->add(
                    $url,
                    $newRoute,
                    $route->slug . ':' . $routeTranslation->language_id,
                    $domain
                );
            });
        }

        /**
         * This is where cache should be dumped?
         */
        return;
        cache($cacheName, function () use ($router) {
            return serialize($router->getRoutes());
        }, 'app', '2minutes');
    }

    /**
     * @return mixed|object|AbstractPartial
     * @throws \Exception
     */
    public function prepareHubPartial($uuid)
    {
        /**
         * Get definition from hub?
         *
         * @var $hub Api
         */
        $hub = resolve(Api::class);
        $shareDefinition = $hub->getApi('share/' . $uuid . '/definition')->getApiResponse('share');

        /**
         * Share definition now holds:
         *  - object (partial) or extends
         *  - content
         *  - attributes
         *  - settings
         */
        $partial = Reflect::create($shareDefinition['props']['extends'] ?? $shareDefinition['props']['object']);

        if (isset($shareDefinition['props']['content'])) {
            $partial->setContent($shareDefinition['content']);
        }

        if (isset($shareDefinition['props']['settings'])) {
            $partial->setSettings($shareDefinition['props']['settings']);
        }

        if (isset($shareDefinition['props']['attributes'])) {
            $partial->setAttributes($shareDefinition['props']['attributes']);
        }

        /**
         * Add multiple shares to the parent?
         */
        $multi = $shareDefinition['props']['multi'] ?? [];
        if ($multi) {
            // what to do when multi sub-shares are re-used?
            // - link group, button group
            // we should add first element (which needs a wrapper if needed), and then add all siblings to his parent?
        }

        /**
         * Basic events table on Overdose.
         */
        $style = $shareDefinition['props']['style'] ?? [];
        if ($style) {
            // this is when style is shared
            // marked span styles, custom heading afters, styled table styles, ...
        }

        return $partial;
    }
}
