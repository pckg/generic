<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Generic\Service\Generic as GenericService;

class GenericRoutes extends Provider
{

    public function routes()
    {
        /**
         * When theme setup is not finished.
         */
        if (!config('platform.themed')) {
            if (auth()->isAdmin()) {
                router()->add(
                    '/',
                    [
                        'tags' => ['layout:backend', 'layout:focused'],
                        'view' => function () {
                            return '<derive-setup-themify></derive-setup-themify>';
                        }
                    ],
                    'homepage'
                );
            }

            return [];
        }

        return [];

        return [
            'method' => [
                GenericService::class . '::addRoutesFromDb',
            ],
        ];
    }

}