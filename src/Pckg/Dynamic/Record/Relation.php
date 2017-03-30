<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Entity;
use Pckg\Database\Query\Raw;
use Pckg\Database\Record;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Relations;
use Throwable;

class Relation extends DatabaseRecord
{

    protected $entity = Relations::class;

    public function applyFilterOnEntity($entity, $foreignRecord)
    {
        /**
         * Is this correct? || !$foreignRecord?
         * http://hi.derive.bob/dynamic/records/edit/23/6751
         */
        if (!$this->filter) {
            return;
        }

        if (!$foreignRecord && strpos($this->filter, '$foreignRecord->') !== false) {
            return;
        }

        $evalResult = $this->eval($this->filter, $foreignRecord);

        if (!$evalResult) {
            return;
        }

        $entity->where(Raw::raw($evalResult));
    }

    public function applyRecordFilterOnEntity(Record $record, Entity $entity)
    {
        if ($this->left_foreign_key_id) {
            $entity->where($this->leftForeignKey->field, $record->id);
        } elseif ($this->on_field_id) {
            $entity->where($this->onField->field, $record->id);
        }
    }

    public function eval($eval, $foreignRecord)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (Throwable $e) {
            if (prod()) {
                return null;
            }

            throw $e;
        }
    }

    public function getOptions()
    {
        $entity = $this->showTable->createEntity();
        Field::automaticallyApplyRelation($entity, $this->value);
        $relation = $this;

        return $this->onField && $this->dynamic_relation_type_id == 1
            ? $entity->limit(100)->all()->map(
                function($record) use ($relation, $entity) {
                    try {
                        $eval = eval(' return ' . $relation->value . '; ');
                    } catch (Throwable $e) {
                        $eval = exception($e);
                    }

                    return [
                        'key'   => $record->{$relation->foreign_field_id ? $relation->foreignField->field : 'id'},
                        'value' => $eval,
                    ];
                }
            )
            : [];
    }

}