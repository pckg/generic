<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Entity\TableViews;
use Pckg\Framework\Provider\Helper\EntityResolver;
use Pckg\Framework\Provider\RouteResolver;

class TableView implements RouteResolver
{

    use EntityResolver;

    protected $entity = TableViews::class;

}
