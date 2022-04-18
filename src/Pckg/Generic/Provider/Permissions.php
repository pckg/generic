<?php

namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Generic\Controller\Permissions as PermissionsController;

class Permissions extends Provider
{
    public function routes()
    {
        return [
            routeGroup([
                           'controller' => PermissionsController::class,
                           'namePrefix' => 'api.pckg.generic.permissions',
                           'urlPrefix'  => '/api/permissions',
                       ], [
                           '' => route('', 'permissions'),
                       ]),
        ];
    }
}
