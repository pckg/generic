<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\Action;

/**
 * Class Actions
 *
 * @package Pckg\Generic\Entity
 */
class Actions extends Entity
{

    /**
     * @var
     */
    protected $record = Action::class;

    public function layouts()
    {
        return $this->morphedBy(Layouts::class)
                    ->over(ActionsMorphs::class)// middle entity
                    ->on('action_id')// id of morphs
                    ->poly('poly_id')// id of this object
                    ->morph('morph_id'); // related class
    }

    /**
     * @return mixed
     */
    public function contents()
    {
        return $this->morphsMany(Contents::class)
                    ->over(ActionsMorphs::class)
                    ->poly('poly_id')
                    ->morph('morph_id');
    }

}


