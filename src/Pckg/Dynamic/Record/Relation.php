<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Entity;
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

        $filter = $this->filter;

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

    public function getOptions()
    {
        $entity = $this->showTable->createEntity();
        Field::automaticallyApplyRelation($entity, $this->value);
        $relation = $this;

        if ($relation->filter) {
            $filter = $relation->filter;
            if (strpos($filter, '"') === 0 && strpos(strrev($filter), '"') === 0) {
                $filter = substr($filter, 1, -1);
            }
            $entity->whereRaw($filter);
        }

        $data = $entity->limit(100)
                       ->all()
                       ->keyBy(function($record) use ($relation) {
                           return $record->{$relation->foreign_field_id ? $relation->foreignField->field : 'id'};
                       })
                       ->map(
                           function($record) use ($relation, $entity) {
                               try {
                                   $eval = eval(' return ' . $relation->value . '; ');
                               } catch (Throwable $e) {
                                   $eval = exception($e);
                               }

                               return $eval;
                           }
                       );

        //}

        return $data;
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
            ->after(
                function($record) use ($relation) {
                    $record->setRelation('select_relation_' . $relation->onField->field, $relation);
                }
            );
        $entity->with($belongsToRelation);
    }

    public function joinToEntity(Entity $entity, Field $field)
    {
        $entity->join('INNER JOIN ' . $this->showTable->table,
                      $this->showTable->table . '.id = ' . $this->onTable->table . '.' . $this->onField->field);
    }

}