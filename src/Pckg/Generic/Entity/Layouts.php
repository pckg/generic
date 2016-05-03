<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
USE Pckg\Generic\Record\Layout;

/**
 * Class Layouts
 * @package Pckg\Generic\Entity
 */
class Layouts extends Entity
{

    use Translatable;

    /**
     * @var
     */
    protected $record = Layout::class;

    public function actions()
    {
        return $this->morphsMany(Actions::class)
            ->over(ActionsMorphs::class)
            ->on('action_id')// id of morphs
            ->poly('poly_id')// id of related object
            ->morph('morph_id'); // this class
    }

}