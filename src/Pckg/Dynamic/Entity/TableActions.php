<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Orderable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\TableAction;

class TableActions extends DatabaseEntity
{

    use Orderable;

    protected $table = 'dynamic_table_actions';

    protected $record = TableAction::class;

    protected $repositoryName = Repository::class . '.dynamic';

}