<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Entity;

class Record extends DatabaseRecord
{

    protected $entity = Entity::class;

}