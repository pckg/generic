<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Entity\Relations;
use Pckg\Framework\Provider\RouteResolver;

class Relation implements RouteResolver
{

    public function resolve($value)
    {
        return (new Relations())->where('id', $value)->oneOrFail();
    }

    public function parametrize($record)
    {
        return $record->id ?? $record;
    }

}
