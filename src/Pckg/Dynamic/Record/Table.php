<?php namespace Pckg\Dynamic\Record;

use Pckg\Collection;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Entity\Tables;

class Table extends Record
{

    protected $entity = Tables::class;

    public function getEntityActions()
    {
        $actions = $this->actions(
            function(HasMany $relation) {
                $relation->where('type', ['entity', 'entity-plugin']);
                $relation->joinPermission();
            }
        );
        $defaultActions = new Collection(
            [
                'add',
                'options',
                'export',
                'view',
            ]
        );

        $actions->copyTo($defaultActions);

        return $defaultActions;
    }

    public function getRecordActions()
    {
        $actions = $this->actions(
            function(HasMany $relation) {
                $relation->where('type', ['record', 'record-plugin']);
                $relation->joinPermission();
            }
        );

        return $actions->count()
            ? $actions
            : ['edit', 'delete'];
    }

    public function getListTitle()
    {
        return ($this->title ?? ('Table ' . $this->table));
    }

    public function getViewUrl()
    {
        return url(
            'dynamic.record.list',
            [
                'table' => $this,
            ]
        );
    }

    public function getEditUrl(Record $record)
    {
        return url(
            'dynamic.record.edit',
            [
                'table'  => $this,
                'record' => $record,
            ]
        );
    }

    public function getHasManyRelation()
    {
        return $this->relationExists('hasManyRelation')
            ? $this->getRelation('hasManyRelation')
            : $this->hasManyRelation(
                function(HasMany $relation) {
                    $relation->where('on_table_id', $this->id);
                }
            );
    }

    public function getBelongsToRelation()
    {
        return $this->relationExists('belongsToRelation')
            ? $this->getRelation('belongsToRelation')
            : $this->belongsToRelation(
                function(BelongsTo $relation) {
                    $relation->where('on_table_id', $this->id);
                }
            );
    }

    /**
     * @return Entity
     */
    public function createEntity()
    {
        $repository = $this->repository
            ? context()->get(Repository::class . '.' . $this->repository)
            : null;
        $entityClass = $this->framework_entity
            ? $this->framework_entity
            : Entity::class;
        $entity = new $entityClass($repository);
        $entity->setTable($this->table);

        return $entity;
    }

}