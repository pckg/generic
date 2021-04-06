<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Controller\Records;
use Pckg\Dynamic\Form\Dynamic;
use Pckg\Framework\Provider\ResolvesMultiple;
use Pckg\Framework\Provider\RouteResolver;

class FunctionizesTabelizes implements RouteResolver, ResolvesMultiple
{

    /**
     * @var \Pckg\Dynamic\Record\Table
     */
    protected $table;

    /**
     * @var \Pckg\Database\Record
     */
    protected $record;

    /**
     * @var \Pckg\Dynamic\Record\Tab
     */
    protected $tab;

    public function __construct(\Pckg\Dynamic\Record\Table $table, \Pckg\Database\Record $record, \Pckg\Dynamic\Record\Tab $tab)
    {
        $this->table = $table;
        $this->record = $record;
        $this->tab = $tab;
    }

    public function resolve($value)
    {
        // mock the controller?
        return resolve(Records::class)->getTabAction($this->record, $this->table, $this->tab);
    }

    public function parametrize($record)
    {
        // TODO: Implement parametrize() method.
    }

}
