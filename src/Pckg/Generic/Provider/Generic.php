<?php namespace Pckg\Generic\Provider;

use Derive\Orders\Controller\Orders;
use Pckg\Framework\Provider;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Menu;

class Generic extends Provider
{

    protected $translations = true;

    public function providers()
    {
        return [
            PageStructure::class,
            GenericPaths::class,
            GenericAssets::class,
            Permissions::class,
        ];
    }

    public function routes()
    {
        return [
            'url'    => [
                '/maestro' => [
                    'view'       => 'stats',
                    'controller' => Orders::class,
                    'name'       => 'maestro',
                    'tags'       => [
                        'group:admin',
                    ],
                ],
            ],
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