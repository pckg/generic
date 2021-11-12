<?php

namespace Pckg\Generic\Resolver;

use Pckg\Framework\Provider\RouteResolver;
use CommsCenter\Pagebuilder\Entity\Contents;

class Content implements RouteResolver
{

    public function resolve($value)
    {
        return (new Contents())->where('id', $value)->oneOrFail();
    }

    public function parametrize($record)
    {
        return $record->id;
    }
}
