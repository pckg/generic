<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Response;

class Field implements RouteResolver
{

    public function resolve($value)
    {
        return (new Fields())->where('id', $value)
                             ->oneOrFail(
                                 function() {
                                     response()->unauthorized('Field not found');
                                 }
                             );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
