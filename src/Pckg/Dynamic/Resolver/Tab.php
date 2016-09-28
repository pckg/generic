<?php namespace Pckg\Dynamic\Resolver;

use Impero\Apache\Record\Site;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Tabs;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Request\Data\Session;
use Pckg\Framework\Response;

class Tab implements RouteResolver
{

    /**
     * @var Tabs
     */
    protected $tabs;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Dynamic
     */
    protected $dynamic;

    public function __construct(Tabs $tabs, Response $response, Dynamic $dynamic)
    {
        $this->tabs = $tabs;
        $this->response = $response;
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        return $this->tabs->where('id', $value)
                          ->oneOrFail(
                              function() {
                                  $this->response->unauthorized('Tab not found');
                              }
                          );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
