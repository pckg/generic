<?php

namespace Pckg\Dynamic\Migration;

use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\FieldType;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\RelationType;
use Pckg\Dynamic\Record\Table;

trait DynamicMigration
{
    public function upDynamicTables($data = [])
    {
        foreach ($data as $table => $properties) {
            $dynamicTable = $this->upDynamicTable($table);
            foreach ($properties as $key => $val) {
                if ($val == 'id') {
                    $this->upDynamicId($dynamicTable);
                } elseif ($val == 'slug') {
                    $this->upDynamicField($dynamicTable, 'slug', ['type' => 'slug']);
                } else if ($key == '_relations') {
                    foreach ($val as $relation) {
                        $this->upDynamicRelation($dynamicTable, $relation);
                    }
                } else {
                    $this->upDynamicField($dynamicTable, $key, $val);
                }
            }
        }
    }

    protected function upDynamicTable($table)
    {
        return Table::getOrCreate(['table' => $table]);
    }

    protected function upDynamicId(Table $table)
    {
        $fieldType = $this->upDynamicFieldType('id');

        $field = Field::getOrCreate(['dynamic_table_id' => $table->id, 'field' => 'id']);
        $field->dynamic_field_type_id = $fieldType->id;
        $field->save();
    }

    protected function upDynamicField(Table $table, $field, $props = [])
    {
        $field = Field::getOrCreate(['dynamic_table_id' => $table->id, 'field' => $field]);

        if (isset($props['type'])) {
            $field->dynamic_field_type_id = $this->upDynamicFieldType($props['type'])->id;
        }

        $field->save();

        return $field;
    }

    protected function upDynamicFieldType($fieldType)
    {
        return FieldType::getOrCreate(['slug' => $fieldType]);
    }

    protected function upDynamicRelationType($relationType)
    {
        return RelationType::getOrCreate(['slug' => $relationType]);
    }

    protected function upDynamicRelation(Table $table, $relation)
    {
        $onField = $this->upDynamicField($table, $relation['primary_field']);
        $relationType = $this->upDynamicRelationType($relation['type']);

        $relation = Relation::getOrCreate(
            [
                'show_table_id'            => $this->upDynamicTable($relation['table'])->id,
                'on_table_id'              => $table->id,
                'on_field_id'              => $onField->id,
                'dynamic_relation_type_id' => $relationType->id,
            ]
        );

        return $relation;
    }
}
