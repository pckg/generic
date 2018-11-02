<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Entity\Relations;
use Pckg\Framework\Provider\Helper\EntityResolver;
use Pckg\Framework\Provider\RouteResolver;

class Relation implements RouteResolver
{

    use EntityResolver;

    protected $entity = Relations::class;

}
