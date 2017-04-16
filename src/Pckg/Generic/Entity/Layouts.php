<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\Layout;

/**
 * Class Layouts
 *
 * @package Pckg\Generic\Entity
 */
class Layouts extends Entity
{

    /**
     * @var
     */
    protected $record = Layout::class;

    public function boot()
    {
        $this->joinTranslations();
    }

    public function actions()
    {
        return $this->morphedBy(Actions::class)
                    ->over(ActionsMorphs::class)
                    ->rightForeignKey('action_id');
    }

}