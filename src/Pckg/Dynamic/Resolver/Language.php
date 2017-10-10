<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Locale\Entity\Languages;
use Pckg\Framework\Provider\RouteResolver;

class Language implements RouteResolver
{

    public function resolve($value)
    {
        return (new Languages())->where('slug', $value)->oneOrFail();
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
