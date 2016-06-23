<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
USE Pckg\Generic\Record\Layout;

/**
 * Class Layouts
 *
 * @package Pckg\Generic\Entity
 */
class Layouts extends Entity
{

    use Translatable;

    /**
     * @var
     */
    protected $record = Layout::class;

    public function actions() {
        return $this->morphsMany(Actions::class)
                    ->leftForeignKey('action_id')
                    ->over(ActionsMorphs::class)
                    ->fill('actionsMorphs');
    }

}