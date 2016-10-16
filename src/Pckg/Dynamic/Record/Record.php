<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Record\Snippet\RecordActions;
use Pckg\Maestro\Service\Contract\Record as MaestroRecord;

class Record extends DatabaseRecord implements MaestroRecord
{

    use RecordActions;

    protected $entity = Entity::class;

}