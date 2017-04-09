<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Menu;

class Generic extends Provider
{

    public function providers()
    {
        return [
            PageStructure::class,
            GenericPaths::class,
            GenericAssets::class,
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

    public function viewObjects()
    {
        return [
            '_menuService'   => Menu::class,
            '_pluginService' => Plugin::class,
        ];
    }

}