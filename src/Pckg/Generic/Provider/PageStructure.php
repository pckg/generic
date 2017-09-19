<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;
use Pckg\Framework\Router\Route\Group;
use Pckg\Framework\Router\Route\Route;
use Pckg\Generic\Controller\PageStructure as PageStructureController;
use Pckg\Generic\Resolver\ActionsMorph;
use Pckg\Generic\Resolver\Content;
use Pckg\Generic\Resolver\Route as RouteResolver;

class PageStructure extends Provider
{

    public function routes()
    {
        return [
            (new Group([
                           'controller' => PageStructureController::class,
                       ]))->routes([
                                       'pckg.generic.pageStructure' => new Route('/tools/page-structure',
                                                                                 'pageStructure'),
                                   ]),
            (new Group([
                           'urlPrefix'  => '/api/pckg/generic/pageStructure',
                           'namePrefix' => 'pckg.generic.pageStructure',
                           'controller' => PageStructureController::class,
                       ]))->routes([
                                       '.getRoutes'                    => new Route('/routes', 'routes'),
                                       '.getVariables'                 => new Route('/variables', 'variables'),
                                       '.getContents'                  => new Route('/contents', 'contents'),
                                       '.getActions'                   => new Route('/actions', 'actions'),
                                       '.getRoute'                     => new Route('/routes/[route]', 'route'),
                                       '.getRouteActions'              => new Route('/routes/[route]/actions',
                                                                                    'routeActions'),
                                       '.routeExport'                  => new Route('/routes/[route]/export',
                                                                                    'routeExport'),
                                       '.routeImport'                  => new Route('/routes/[route]/import',
                                                                                    'routeImport'),
                                       '.getLayoutActions'             => new Route('/layout/[layout]/actions',
                                                                                    'layoutActions'),
                                       '.getActionsMorphs'             => (new Route('/actionsMorph/forRoute/[route]',
                                                                                     'actionsMorphsForRoute'))->resolvers([
                                                                                                                              'route' => (new RouteResolver())->by('id',
                                                                                                                                                                   'route'),
                                                                                                                          ]),
                                       '.setActionsMorphPermissions'   => new Route('/actionsMorph/[actionsMorph]/permissions',
                                                                                    'actionsMorphPermissions'),
                                       '.addActionsMorph'              => new Route('/actionsMorph/add',
                                                                                    'addActionsMorph'),
                                       '.postActionsMorphsOrders'      => new Route('/actionsMorph/orders',
                                                                                    'ordersActionsMorph'),
                                       '.actionsMorph'                 => (new Route('/actionsMorph/[actionsMorph]',
                                                                                     'actionsMorph'))->resolvers([
                                                                                                                     'actionsMorph' => ActionsMorph::class,
                                                                                                                 ]),
                                       '.content'                      => (new Route('/content/[content]', 'content'))
                                           ->resolvers([
                                                           'content' => Content::class,
                                                       ]),
                                       '.actionsMorphSettings'         => (new Route('/actionsMorph/[actionsMorph]/settings',
                                                                                     'actionsMorphSettings'))->resolvers([
                                                                                                                             'actionsMorph' => ActionsMorph::class,
                                                                                                                         ]),
                                       '.actionsMorphContent'          => (new Route('/actionsMorph/[actionsMorph]/content',
                                                                                     'actionsMorphContent'))->resolvers([
                                                                                                                            'actionsMorph' => ActionsMorph::class,
                                                                                                                        ]),
                                       '.duplicateActionsMorphContent' => (new Route('/actionsMorph/[actionsMorph]/duplicateContent',
                                                                                     'duplicateActionsMorphContent'))->resolvers([
                                                                                                                                     'actionsMorph' => ActionsMorph::class,
                                                                                                                                 ]),
                                       '.createActionsMorphContent'    => (new Route('/actionsMorph/[actionsMorph]/createContent',
                                                                                     'createActionsMorphContent'))->resolvers([
                                                                                                                                  'actionsMorph' => ActionsMorph::class,
                                                                                                                              ]),
                                       '.actionsMorphBackgroundImage'  => (new Route('/actionsMorph/[actionsMorph]/backgroundImage',
                                                                                     'actionsMorphBackgroundImage'))->resolvers([
                                                                                                                                    'actionsMorph' => ActionsMorph::class,
                                                                                                                                ]),
                                   ]),
        ];
    }

}