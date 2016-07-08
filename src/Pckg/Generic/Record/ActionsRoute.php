<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\ActionsRoutes;

class ActionsRoute extends Record
{

    protected $entity = ActionsRoutes::class;

    public function getVariableName()
    {
        return $this->variable
            ? $this->variable->slug
            : null;
    }

}