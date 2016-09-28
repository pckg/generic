<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\Func;

class Functions extends Entity
{

    use Translatable;

    protected $table = 'dynamic_functions';

    protected $record = Func::class;

}