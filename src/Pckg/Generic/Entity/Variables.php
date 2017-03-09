<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Repository;
use Pckg\Generic\Record\Variable;

class Variables extends Entity
{

    protected $record = Variable::class;

    public function actions()
    {
        return $this->morphsMany(Actions::class)
                    ->over(ActionsMorphs::class)
                    ->leftForeignKey('variable_id');
    }

}