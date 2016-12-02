<?php namespace Pckg\Generic\Resolver;

use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Response;
use Pckg\Generic\Entity\Routes;

class Route implements RouteResolver
{

    public function resolve($value)
    {
        return (new Routes())//->where($value ? 'id' : 'slug', $value ?: 'home')
        ->joinTranslation()
        ->where('routes_i18n.route', $value)// @T00D00 ... what about dynamic routes? /news/[id] ?
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