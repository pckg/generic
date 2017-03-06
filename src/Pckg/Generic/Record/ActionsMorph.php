<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Database\Record\Extension\Permissionable;
use Pckg\Generic\Entity\ActionsMorphs;

class ActionsMorph extends Record
{

    use Permissionable;

    protected $entity = ActionsMorphs::class;

    protected $toArray = ['variable'];

}