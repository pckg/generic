<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Controller\Generic as GenericController;
use Pckg\Generic\Resolver\Route as RouteResolver;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Menu;

class Generic extends Provider
{

    public function providers()
    {
        return [
            PageStructure::class,
        ];
    }

    public function routes()
    {
        return [
            /*'url'    => [
                '/generic' => [
                    'view'       => 'generic',
                    'controller' => GenericController::class,
                    'name'       => 'home',
                    'resolvers'  => [
                        'route' => RouteResolver::class,
                    ],
                ],
            ],*/
            'method' => [
                GenericService::class . '::addRoutesFromDb',
            ],
        ];
    }

    public function paths()
    {
        $paths = $this->getViewPaths();
        $paths[] = str_replace('Generic', 'Maestro', $paths[0]);

        return $paths;
    }

    public function viewObjects()
    {
        return [
            '_menuService'   => Menu::class,
            '_pluginService' => Plugin::class,
        ];
    }

    public function assets()
    {
        return [
            'footer'    => [
                'vue/pckg-generic-app.js',
            ],
            'libraries' => [
                'vue/pckg-generic-app-top.js',
            ],
        ];
    }

}