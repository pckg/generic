<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Framework\Router\Route\Group;
use Pckg\Framework\Router\Route\Route;
use Pckg\Generic\Controller\PageStructure as PageStructureController;

class PageStructure extends Provider
{

    public function routes()
    {
        return [
            (new Group([
                           'urlPrefix'  => '/api/pckg/generic/pageStructure',
                           'namePrefix' => 'pckg.generic.pageStructure',
                           'controller' => PageStructureController::class,
                       ]))->routes([
                                       '.getRoutes'                  => new Route('/routes', 'routes'),
                                       '.getVariables'               => new Route('/variables', 'variables'),
                                       '.getRoute'                   => new Route('/routes/[route]', 'route'),
                                       '.getRouteActions'            => new Route('/routes/[route]/actions',
                                                                                  'routeActions'),
                                       '.getLayoutActions'           => new Route('/layout/[layout]/actions',
                                                                                  'layoutActions'),
                                       '.setActionsMorphPermissions' => new Route('/actionsMorph/[actionsMorph]/permissions',
                                                                                  'actionsMorphPermissions'),
                                       '.actionsMorph'               => new Route('/actionsMorph/[actionsMorph]',
                                                                                  'actionsMorph'),
                                   ]),
        ];
    }

}