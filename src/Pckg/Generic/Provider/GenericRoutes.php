<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Generic\Service\Generic as GenericService;

class GenericRoutes extends Provider
{

    public function routes()
    {
        return [
            'method' => [
                GenericService::class . '::addRoutesFromDb',
            ],
        ];
    }

}