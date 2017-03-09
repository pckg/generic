<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Generic\Record\Content;

/**
 * Class Contents
 *
 * @package Pckg\Generic\Entity
 */
class Contents extends Entity
{

    use Translatable;

    /**
     * @var
     */
    protected $record = Content::class;

    public function actions()
    {
        return $this->morphedBy(Actions::class);
    }

}