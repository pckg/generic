<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Functions;

class Func extends DatabaseRecord
{

    protected $entity = Functions::class;
}
