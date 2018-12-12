<?php namespace Pckg\Dynamic\Provider;

use Pckg\Framework\Provider;

class HttpQl extends Provider
{

    public function routes()
    {
        return [
            routeGroup([
                           'controller' => \Pckg\Dynamic\Controller\HttpQl::class,
                       ], [
                           'api.httpql' => route('/api/http-ql', 'index'),
                       ]),
        ];
    }

}