<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\Layouts;

class Layout extends Record
{

    use SettingsHelper;

    protected $entity = Layouts::class;

}