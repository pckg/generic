<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Relation\HasMany;
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

    public function boot()
    {
        $this->joinTranslations();
    }

    public function actions()
    {
        return $this->morphedBy(Actions::class);
    }

    public function contents()
    {
        return $this->hasMany(Contents::class, function (HasMany $contents) {
            $contents->nonDeleted();
        })
                    ->foreignKey('parent_id');
    }
}
