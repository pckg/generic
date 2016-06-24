<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Generic\Record\Setting;

class Settings extends Entity
{

    use Translatable;

    protected $record = Setting::class;

}