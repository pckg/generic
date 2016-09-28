<?php namespace Pckg\Dynamic\Resolver;

use Impero\Apache\Record\Site;
use Pckg\Concept\Reflect;
use Pckg\Database\Query;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Record as DatabaseRecord;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

class Record implements RouteResolver
{

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Tables
     */
    protected $tables;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Dynamic
     */
    protected $dynamic;

    public function __construct(
        Router $router,
        Tables $tables,
        Response $response,
        Dynamic $dynamic
    ) {
        $this->router = $router;
        $this->tables = $tables;
        $this->response = $response;
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        $table = $this->router->resolved('table');
        $this->tables->setTable($table->table);
        $this->tables->setRecordClass(DatabaseRecord::class);

        if ($table->repository) {
            $this->tables->setRepository(context()->get(Repository::class . '.' . $table->repository));
        }

        $this->dynamic->joinTranslationsIfTranslatable($this->tables);
        $this->dynamic->joinPermissionsIfPermissionable($this->tables);

        return $this->tables->where('id', $value)
                            ->oneOrFail(
                                function() {
                                    $this->response->unauthorized('Record not found');
                                }
                            );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
