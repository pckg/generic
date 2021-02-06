<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\FieldGroups;
use Pckg\Dynamic\Entity\FieldTypes;

class FieldGroup extends DatabaseRecord
{

    protected $entity = FieldGroups::class;
}
