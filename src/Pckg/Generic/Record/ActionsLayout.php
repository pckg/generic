<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\ActionsLayouts;

class ActionsLayout extends Record
{

    protected $entity = ActionsLayouts::class;

    public function getVariableName()
    {
        return $this->variable
            ? $this->variable->slug
            : null;
    }

}