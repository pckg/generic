<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\Func;

class Functions extends DatabaseEntity
{

    use Translatable;

    protected $table = 'dynamic_functions';

    protected $record = Func::class;

    protected $repositoryName = Repository::class . '.dynamic';

}