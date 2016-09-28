<?php namespace Pckg\Dynamic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Query\Raw;
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
                $relation->where(new Raw('type = \'entity\''));
                $relation->joinPermission();
            }
        );

        return $actions->count()
            ? $actions
            : [
                'add',
                'options',
                'export',
                'view',
            ];
    }

    public function getRecordActions()
    {
        $actions = $this->actions(
            function(HasMany $relation) {
                $relation->where(new Raw('type = \'record\''));
                $relation->joinPermission();
            }
        );

        return $actions->count()
            ? $actions
            : ['edit', 'delete'];
    }

    public function getListTitle()
    {
        return 'Table: ' . $this->table;
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
        if ($this->framework_entity) {
            $entity = Reflect::create($this->framework_entity);

        } else {
            $entity = new Entity();
            $entity->setTable($this->table);

            if ($this->repository) {
                $entity->setRepository(context()->get(Repository::class . '.' . $this->repository));
            }

        }

        return $entity;
    }

}