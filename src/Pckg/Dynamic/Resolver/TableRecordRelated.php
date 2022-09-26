<?php

namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Controller\Records;
use Pckg\Dynamic\Form\Dynamic;
use Pckg\Framework\Provider\ResolvesMultiple;
use Pckg\Framework\Provider\RouteResolver;

class TableRecordRelated implements RouteResolver, ResolvesMultiple
{

    /**
     * @var \Pckg\Dynamic\Record\Table
     */
    protected $table;

    /**
     * @var \Pckg\Database\Record
     */
    protected $record;

    public function __construct(\Pckg\Dynamic\Record\Table $table, \Pckg\Database\Record $record)
    {
        $this->table = $table;
        $this->record = $record;
    }

    public function resolve($value)
    {
        $dynamicService = resolve(\Pckg\Dynamic\Service\Dynamic::class);
        $dynamicForm = resolve(Dynamic::class);
        $controller = resolve(Records::class);

        return $controller->getViewAction($dynamicForm, $this->record, $this->table, $dynamicService);
    }

    public function parametrize($record)
    {
        // TODO: Implement parametrize() method.
    }
}
