<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\FieldTypes;

class FieldType extends DatabaseRecord
{
    protected $entity = FieldTypes::class;
}
