<?php

namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Record;

/**
 * Represents one dynamic table.
 *
 * Class Entity
 *
 * @package Pckg\Dynamic\Entity
 */
class Entity extends DatabaseEntity
{
    protected $record = Record::class;
    public function hasManyRelation()
    {
        return $this->belongsTo(Relations::class)
                    ->foreignKey('on_table_id');
    }

    /**
     * Useful for building user_group.language_id = language.id relation
     *
     * @return $this
     */
    public function belongsToRelation()
    {
        return $this->belongsTo(Relations::class)
                    ->foreignKey('on_table_id');
    }
}
