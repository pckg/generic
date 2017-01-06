<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Repository;
use Pckg\Generic\Record\Variable;

class Variables extends Entity
{

    protected $record = Variable::class;

    protected $repositoryName = Repository::class . '.dynamic';

    public function actions()
    {
        return $this->morphsMany(Actions::class)
                    ->over(ActionsMorphs::class)
                    ->leftForeignKey('variable_id');
    }

}