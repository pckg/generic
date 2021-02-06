<?php

namespace Pckg\Generic\Controller;

use Derive\Internal\Cache\Service\Cache;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\CustomAction;

/**
 * Class Generic
 *
 * @package Pckg\Generic\Controller
 */
class Generic
{

    /**
     * @var GenericService
     */
    protected $genericService;

    /**
     * @var
     */
    protected $route;

    public function __construct(
        GenericService $genericService
    ) {
        $this->genericService = $genericService;
    }

    public function getGenericAction(Route $route)
    {
        /**
         * Make sure all cache dependencies are used in cache name.
         * And that route is actually updated.
         */
        if (false && strpos($route->route, '[') === false) {
            //$lastUpdatedAt = Cache::timestamp($route);
            $cacheName = Cache::name($route, [
                GenericService::class . '.getGenericAction',
                'userGroup' => auth()->user('user_group_id') ?? 'guest',
            ]);

            return cache($cacheName, function () use ($route) {
                return $this->getGenericActionWrapper($route);
            }, 'app', '5minutes');
        }

        return $this->getGenericActionWrapper($route);
    }

    public function getGenericActionWrapper(Route $route)
    {
        measure('Reading route', function() use ($route) {
            $this->genericService->readRoute($route);
        });

        $route->applySeoSettings();

        measure('Building actions', function() {
            $this->genericService->build();
        });

        return measure('Stringifying output', function() use ($route) {
            $auth = auth();
            //$structure = '<pckg-app data-frontend></pckg-app>';

            /**
             * Check for themed page?
             */
            /*if (false) {
                $slug = $route->slug;
                $themePath = path('src') . 'theme/comms/test/dist/';
                $pagesPath = $themePath . 'pages/';
                $layoutsPath = $themePath . 'layouts/';
                $file = $pagesPath . $slug .'.html';
                if (file_exists($file)) {
                    $fileContent = file_get_contents($file);
                    $layoutContent = file_get_contents($layoutsPath . 'for-homepage.html');
                    $structure = str_replace('##PAGE##', $fileContent, $layoutContent);
                }
            }*/

            /*$vars = [
                'content' => $structure,
            ];*/
            $vars = [];

            trigger(static::class . '.stringifying');

            return $route->layout
                ? view($route->layout->template ?: 'Pckg/Generic:backend', $vars)->autoparse()
                : $vars;
        });
    }

    public function postGenericAction(Route $route)
    {
        return null;
    }

    public function headGenericAction()
    {
        return null;
    }

    public function optionsGenericAction()
    {
        return null;
    }

    public function propfindGenericAction()
    {
        return null;
    }

    public function wrapIntoGeneric($view, $template = 'Pckg/Generic:frontend')
    {
        message('Wrapping into generic ' . $template);
        $center = $this->genericService->touchBlock('content');

        /**
         * We add view action to center:0.
         */
        $center->addAction(new CustomAction($view));

        $this->genericService->readSystemRoute($template);

        $vars = $this->genericService->getVariables();

        return view($template, $vars);
    }

    public function wrapIntoGenericContainer($view, $template = 'Pckg/Generic:frontend')
    {
        message('Wrapping into container');
        $class = 'container';
        if (in_array('container:100', router()->get('tags'))) {
            $class = 'container-100';
        } else if (in_array('container:wide', router()->get('tags'))) {
            $class = 'container-wide';
        }
        $view = '<div class="' . $class . '" data-frontend>' . $view . '</div>';

        return $this->wrapIntoGeneric($view, $template);
    }

    public function getContentAction(Action $action)
    {
        return view('contents', [
                                  'contents' => (new Contents())->all(),
                              ]);
    }

    public function getLanguagesAction()
    {
        return view('languages', [
                                   'languages' => localeManager()->getFrontendLanguages(),
                               ]);
    }
}
