<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Tabs;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Response;

class Tab implements RouteResolver
{

    public function resolve($value)
    {
        return (new Tabs())->where('id', $value)
                           ->oneOrFail(
                               function() {
                                   response()->unauthorized('Tab not found');
                               }
                           );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
