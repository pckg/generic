<?php namespace Pckg\Dynamic\Resolver;

use Impero\Apache\Record\Site;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Request\Data\Session;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

class Table implements RouteResolver
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
     * @var Session
     */
    protected $session;

    /**
     * @var Dynamic
     */
    protected $dynamic;

    public function __construct(Tables $tables, Response $response, Dynamic $dynamic)
    {
        $this->tables = $tables;
        $this->response = $response;
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        $this->dynamic->joinTranslationsIfTranslatable($this->tables);
        $this->dynamic->joinPermissionsIfPermissionable($this->tables);

        return $this->tables->where('id', $value)
                            ->withFields(
                                function(HasMany $fields) {
                                    $fields->joinTranslations();
                                    $fields->withFieldType();
                                    $fields->withSettings();
                                }
                            )
                            ->withTabs(
                                function(HasMany $tabs) {
                                    $tabs->joinTranslations();
                                }
                            )
                            ->oneOrFail(
                                function() {
                                    $this->response->unauthorized('Table not found');
                                }
                            );
    }

    public function parametrize($record)
    {
        if (!$record) {
            return $record;
        }

        return $record->id;
    }

}
