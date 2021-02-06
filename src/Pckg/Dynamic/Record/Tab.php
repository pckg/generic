<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Tabs;

/**
 * Class Tab
 * @package Pckg\Dynamic\Record
 * @property string $title
 * @property Table $table
 */
class Tab extends DatabaseRecord
{

    protected $entity = Tabs::class;
    public function getNameAttribute()
    {
        if ($this->title) {
            return $this->title;
        }

        $t = $this;
        return $this->table->relations(function (HasMany $relations) use ($t) {

                $relations->where('dynamic_table_tab_id', $t->id);
            $relations->withShowTable();
        })->map(function (Relation $relation) {

                return $relation->showTable->title ?? $relation->showTable->table;
        })->implode(', ');
    }
}
