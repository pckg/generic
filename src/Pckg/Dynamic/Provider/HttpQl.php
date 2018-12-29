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
                           'api.httpql'        => route('/api/http-ql', 'index'),
                           'api.httpql.export' => route('/api/http-ql/export', 'export'),
                           'api.httpql.download' => route('/api/http-ql/download', 'download'),
                       ]),
        ];
    }

}