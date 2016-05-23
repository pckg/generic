<?php namespace Pckg\Generic\Resolver;

use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Response;
use Pckg\Generic\Entity\Routes;

class Route implements RouteResolver
{

    /**
     * @var Routes
     */
    protected $routes;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(Routes $routes, Response $response)
    {
        $this->routes = $routes;
        $this->response = $response;
    }

    public function resolve($value)
    {
        return $this->routes->where('id', $value)
            ->oneOrFail(function () {
                $this->response->unauthorized('Route not found');
            });
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}