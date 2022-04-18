<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\RelationTypes;

class RelationType extends DatabaseRecord
{
    protected $entity = RelationTypes::class;
}
