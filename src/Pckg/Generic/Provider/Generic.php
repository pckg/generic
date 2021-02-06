<?php

namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Console\ImportGenericBackend;
use Pckg\Generic\Console\CreateListData;
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

    public function viewObjects()
    {
        return [
            '_menuService'   => Menu::class,
            '_pluginService' => Plugin::class,
        ];
    }

    public function consoles()
    {
        return [
            ImportGenericBackend::class,
        ];
    }

    public function services()
    {
        return [
            \Pckg\Generic\Service\Generic::class => function () {
                return new \Pckg\Generic\Service\Generic();
            },
        ];
    }
}
