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

    public function boot()
    {
        $this->setTranslatableLang('en')->joinTranslations();
    }

    public function layouts()
    {
        return $this->morphedBy(Layouts::class)
                    ->over(ActionsMorphs::class)// middle entity
                    ->leftForeignKey('action_id'); // related class
    }

    /**
     * @return mixed
     */
    public function contents()
    {
        return $this->morphsMany(Contents::class)
                    ->over(ActionsMorphs::class)
                    ->leftForeignKey('content_id');
    }

}


