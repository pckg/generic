<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Entity;
use Pckg\Database\Query;
use Pckg\Database\Query\Raw;
use Pckg\Database\Record;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Service\Dynamic;
use Throwable;

class Relation extends DatabaseRecord
{

    protected $entity = Relations::class;

    public function applyFilterOnEntity(Entity $entity, $foreignRecord = null, $record = null)
    {
        /**
         * Is this correct? || !$foreignRecord?
         * http://hi.derive.bob/dynamic/records/edit/23/6751
         */
        if (!$this->filter) {
            return;
        }

        if (!$foreignRecord && !$record && strpos($this->filter, '$') !== false) {
            return;
        }

        $filter = $this->filter;

        if (strpos($filter, '$') !== false) {
            $filter = $this->evalRecords($filter, $foreignRecord, $record);
        }

        if (strpos($filter, '"') === 0 && strpos(strrev($filter), '"') === 0) {
            $filter = substr($filter, 1, -1);
        }

        $entity->where(Raw::raw($filter));
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

    public function evalRecords($eval, $foreignRecord, $record)
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

    protected function evalRelationValue($relation, $record)
    {
        $eval = null;
        try {
            return eval(' return ' . $relation->value . '; ');
        } catch (Throwable $e) {
            if (prod()) {
                return null;
            }

            $eval = exception($e);
        }

        return $eval;
    }

    public function evalRecordAndRelation($eval, $record = null, $relation = null)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (Throwable $e) {
            if (prod()) {
                return null;
            }

            return exception($e);
        }
    }

    public function getOptions()
    {
        $entity = $this->showTable->createEntity();
        Field::automaticallyApplyRelation($entity, $this->value);
        if ($entity->isDeletable()) {
            $entity->nonDeleted();
        }
        $relation = $this;
        $relation->applyFilterOnEntity($entity);
        $foreignField = $relation->foreign_field_id
            ? $relation->foreignField->field
            : 'id';

        $values = [];
        $records = $entity->limit(500)->all();
        $records->keyBy(function($record) use ($relation) {
            return $record->{$relation->foreign_field_id ? $relation->foreignField->field : 'id'};
        })->each(
            function($record) use ($relation, $entity, $foreignField, &$values) {
                $relationValue = $this->evalRelationValue($relation, $record);

                $groupValue = $relation->group_value
                    ? $this->evalRecordAndRelation($relation->group_value, $record, $relation)
                    : null;

                $values[$groupValue][$record->{$foreignField}] = $relationValue;
            }
        );

        if (count($values) == 1) {
            $values = end($values);
        }

        return $values;
    }

    public function loadOnEntity(Entity $entity, Dynamic $dynamicService)
    {
        /**
         * Right table entity is created here.
         */
        $alias = $this->alias ?? $this->showTable->table;
        $relationEntity = $this->showTable->createEntity($alias);
        $dynamicService->joinTranslationsIfTranslatable($relationEntity);

        /**
         * We need to add relations to select.
         * $tableRecord is for example users.
         * So entity is entity with table users.
         * We will fetch all users and related user_group_id and language_id
         * as user.relation_user_group_id and user.relation_language_id.
         */
        $relation = $this;
        $belongsToRelation = (new BelongsTo($entity, $relationEntity, $alias))
            ->foreignKey($this->onField->field)
            ->fill('relation_' . $this->onField->field)
            ->primaryKey($this->foreignField ? $this->foreignField->field : 'id')
            ->after(
                function($record) use ($relation) {
                    $record->setRelation('select_relation_' . $relation->onField->field, $relation);
                }
            );
        $entity->with($belongsToRelation);
    }

    public function joinToQuery(Query $query, $alias = null, $subalias = null)
    {

        if (!$alias) {
            $alias = $this->showTable->table;
        }

        if (!$subalias) {
            $subalias = $this->onTable->table;
        }

        $query->join('LEFT JOIN ' . $this->showTable->table . ' AS ' . $alias,
                     $alias . '.id = ' . $subalias . '.' . $this->onField->field);
    }

    public function joinToEntity(Entity $entity, $alias = null, $subalias = null)
    {
        if (!$alias) {
            $alias = $this->showTable->table;
        }

        if (!$subalias) {
            $subalias = $this->onTable->table;
        }

        $entity->join('INNER JOIN ' . $this->showTable->table . ' AS ' . $alias,
                      $alias . '.id = ' . $subalias . '.' . $this->onField->field);
    }

}