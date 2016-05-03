<?php

namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Generic\Controller\Generic as GenericController;
use Pckg\Generic\Service\Generic as GenericService;

class Config extends Provider
{

    public function routes()
    {
        return [
            'url'    => [
                '/' => [
                    'view'       => 'generic',
                    'controller' => GenericController::class,
                    'name'       => 'home',
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

}