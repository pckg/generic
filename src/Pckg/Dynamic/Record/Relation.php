<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Relations;

class Relation extends Record
{

    protected $entity = Relations::class;

}