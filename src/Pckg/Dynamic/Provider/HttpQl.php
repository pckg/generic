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
                'api.httpql' => route('/api/http-ql', 'index')/*->methods(['GET', 'SEARCH', 'PUT'])*/,
                'api.httpql.definition' => route('/api/http-ql/definition', 'definition'),
                'api.httpql.export' => route('/api/http-ql/export', 'export'),
                'api.httpql.download' => route('/api/http-ql/download', 'download'),
            ]),
        ];
    }

}