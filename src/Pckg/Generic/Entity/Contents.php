<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\Content;

/**
 * Class Contents
 *
 * @package Pckg\Generic\Entity
 */
class Contents extends Entity
{

    /**
     * @var
     */
    protected $record = Content::class;

    public function actions()
    {
        return $this->morphedBy(Actions::class);
    }
    public function contents()
    {
        return $this->hasMany(Contents::class)->foreignKey("parent_id");
    }

}