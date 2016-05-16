<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Orderable;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Generic\Record\MenuItem;

class MenuItems extends Entity
{

    use Translatable, Orderable;

    protected $record = MenuItem::class;

}