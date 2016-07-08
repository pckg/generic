<?php

namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Controller\Generic as GenericController;
use Pckg\Generic\Resolver\Route as RouteResolver;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Menu;

class Generic extends Provider
{

    public function routes()
    {
        return [
            'url'    => [
                '/' => [
                    'view'       => 'generic',
                    'controller' => GenericController::class,
                    'name'       => 'home',
                    'resolvers'  => [
                        'route' => RouteResolver::class,
                    ],
                ],
            ],
            'method' => [
                GenericService::class . '::addRoutesFromDb',
            ],
        ];
    }

    public function paths()
    {
        return $this->getViewPaths();
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
            'footer' => [
                'vue/pckg-generic-app.js',
            ],
        ];
    }

}