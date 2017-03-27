<?php namespace Pckg\Generic\Resolver;

use Pckg\Framework\Provider\RouteResolver;
use Pckg\Generic\Entity\ActionsMorphs;

class ActionsMorph implements RouteResolver
{

    public function resolve($value)
    {
        return (new ActionsMorphs())->where('id', $value)->oneOrFail();
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}