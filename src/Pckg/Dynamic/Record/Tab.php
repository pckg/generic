<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Tabs;

class Tab extends Record
{

    protected $entity = Tabs::class;

    public static $dynamicRecord;

    public static $dynamicTable;

    public function getUrl()
    {
        return url(
            'dynamic.record.tab',
            [
                'tab'    => $this,
                'table'  => static::$dynamicTable,
                'record' => static::$dynamicRecord,
            ]
        );
    }

    public function getNameAttribute()
    {
        if ($this->title) {
            return $this->title;
        }

        $t = $this;
        return $this->table->relations(
            function(HasMany $relations) use ($t) {
                $relations->where('dynamic_table_tab_id', $t->id);
                $relations->withShowTable();
            }
        )->map(
            function(Relation $relation) {
                return $relation->showTable->title ?? $relation->showTable->table;
            }
        )->implode(', ');
    }

}