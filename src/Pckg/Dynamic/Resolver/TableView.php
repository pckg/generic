<?php namespace Pckg\Dynamic\Resolver;

use Impero\Apache\Record\Site;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Entity\TableViews;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Request\Data\Session;
use Pckg\Framework\Response;

class TableView implements RouteResolver
{

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Tables
     */
    protected $tableViews;

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

    public function __construct(TableViews $tableViews, Response $response, Dynamic $dynamic)
    {
        $this->tableViews = $tableViews;
        $this->response = $response;
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        //$this->dynamic->joinTranslationsIfTranslatable($this->tableViews);
        //$this->dynamic->joinPermissionsIfPermissionable($this->tableViews);

        return $this->tableViews->where('id', $value)
                                ->oneOrFail(
                                    function() {
                                        $this->response->unauthorized('Table view not found');
                                    }
                                );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
