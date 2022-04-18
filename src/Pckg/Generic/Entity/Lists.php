<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\ListRecord;

class Lists extends Entity
{
    protected $record = ListRecord::class;
}
