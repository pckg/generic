<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Entity\TableViews;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;

class TableView implements RouteResolver
{

    /**
     * @var Dynamic
     */
    protected $dynamic;

    public function __construct(Dynamic $dynamic)
    {
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        return (new TableViews())->where('id', $value)
                                 ->oneOrFail(
                                     function() {
                                         response()->unauthorized('Table view not found');
                                     }
                                 );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
