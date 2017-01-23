<?php namespace Pckg\Dynamic\Record;

use Pckg\Collection;
use Pckg\Concept\Reflect;
use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Entity\Tables;

class Table extends DatabaseRecord
{

    protected $entity = Tables::class;

    public function getEntityActions()
    {
        $actions = $this->actions(
            function(HasMany $relation) {
                $relation->where('type', ['entity', 'entity-plugin', 'mixed']);
                $relation->joinPermission();
            }
        );
        $defaultActions = new Collection(
            [
                'add',
                'options',
                'export',
                'import',
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
                $relation->where('type', ['record', 'record-plugin', 'mixed']);
                $relation->joinPermission();
            }
        );

        return $actions->count()
            ? $actions
            : ['edit', 'delete'];
    }

    public function getListTitle()
    {
        return $this->title ?? $this->table;
    }

    public function getFormTitle($type = 'Add')
    {
        return $type . ' ' . lcfirst($this->title ?? ('<i>' . $this->table . '</i>'));
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

    public function getEditUrl(DatabaseRecord $record)
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

    public function getRepository()
    {
        $r = $this->repository;

        // @T00D00 - @T3MP
        if ($r == 'gnp') {
            $r = 'default';
        } elseif ($r == 'derive') {
            $r = 'dynamic';
        } elseif (!$r) {
            $r = 'dynamic';
        }

        return context()->get(Repository::class . '.' . $r);
    }

    /**
     * @return Entity
     */
    public function createEntity($alias = null)
    {
        $repository = $this->getRepository();
        $entityClass = $this->framework_entity
            ? $this->framework_entity
            : Entity::class;
        $entity = runInLocale(
            function() use ($entityClass, $repository, $alias) {
                return new $entityClass($repository, $alias);
            },
            session()->pckg_dynamic_lang_id
        );
        $entity->setTable($this->table);

        return $entity;
    }

    public function fetchFrameworkRecord(DatabaseRecord $record, DatabaseEntity $entity)
    {
        if (!$this->framework_entity) {
            return $record;
        }

        return $entity->where('id', $record->id)->oneOrFail();
    }

}