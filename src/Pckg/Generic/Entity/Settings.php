<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\Setting;

class Settings extends Entity
{
    protected $record = Setting::class;
}
