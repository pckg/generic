<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Orderable;
use Pckg\Database\Entity\Extension\Permissionable;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\TableAction;

class TableActions extends DatabaseEntity
{

    use Translatable, Permissionable, Orderable;

    protected $table = 'dynamic_table_actions';

    protected $record = TableAction::class;

    protected $repositoryName = Repository::class . '.dynamic';

}