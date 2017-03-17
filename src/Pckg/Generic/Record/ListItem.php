<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\ListItems;

class ListItem extends Record
{

    protected $entity = ListItems::class;

    public function getTitleAttribute()
    {
        return $this->value;
    }

}