<?php

namespace Pckg\Dynamic\Provider;

use Pckg\Dynamic\Resolver\Record;
use Pckg\Dynamic\Resolver\Table;
use Pckg\Dynamic\Resolver\TableQl;
use Pckg\Framework\Provider;

class HttpQl extends Provider
{

    public function routes()
    {
        return [
            routeGroup([
                'controller' => \Pckg\Dynamic\Controller\HttpQl::class,
                'tags' => [
                    'group:api',
                ],
            ], [
                'api.httpql' => route('/api/http-ql', 'index'),
                'api.httpql.definition' => route('/api/http-ql/definition', 'definition'),
                'api.httpql.export' => route('/api/http-ql/export', 'export'),
                'api.httpql.download' => route('/api/http-ql/download', 'download'),
                'api.httpql.upload' => route('/api/http-ql/upload', 'upload'),
                'api.httpql.table' => route('/api/http-ql/[table]', 'table')->resolvers([
                    'table' => TableQl::class,
                ]),
                'api.httpql.record' => route('/api/http-ql/[table]/[record]', 'record')->resolvers([
                    'table' => TableQl::class,
                    'record' => Record::class,
                ]),
            ]),
        ];
    }
}
