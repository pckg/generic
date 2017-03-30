<?php namespace Pckg\Generic\Resolver;

use Pckg\Framework\Provider\RouteResolver;
use Pckg\Generic\Entity\Routes;

class Route implements RouteResolver
{

    public function resolve($value)
    {
        return (new Routes())->where('slug', router()->get('name'))
                             ->joinTranslation()
                             ->oneOrFail(
                                 function() use ($value) {
                                     response()->notFound('Route ' . $value . ' not found in generic routes');
                                 }
                             );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}