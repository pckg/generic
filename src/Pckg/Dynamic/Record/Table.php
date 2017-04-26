<?php namespace Pckg\Dynamic\Record;

use Pckg\Collection;
use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Filter;

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
        return $this->actions(
            function(HasMany $relation) {
                $relation->where('type', ['record', 'record-plugin', 'mixed']);
                //$relation->joinPermission();
            }
        );
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

    public function getListUrl() {
        return url('dynamic.record.list', ['table' => $this]);
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
    public function createEntity($alias = null, $extensions = true)
    {
        $repository = $this->getRepository();
        $entityClass = $this->framework_entity
            ? $this->framework_entity
            : Entity::class;
        $entity = runInLocale(
            function() use ($entityClass, $repository, $alias) {
                return new $entityClass($repository, $alias);
            },
            $_SESSION['pckg_dynamic_lang_id']
        );
        $entity->setTable($this->table);

        if ($extensions && $entity->isTranslatable()) {
            $entity->joinTranslations();
        }

        return $entity;
    }

    public function createRecord()
    {
        return $this->createEntity()->getRecord();
    }

    public function fetchFrameworkRecord(DatabaseRecord $record, DatabaseEntity $entity)
    {
        if (!$this->framework_entity) {
            return $record;
        }

        return $entity->where('id', $record->id)->oneOrFail();
    }

    public function getFields($listableFields, Filter $filterService)
    {
        $tableRecord = $this;

        return runInLocale(
            function() use ($tableRecord, $listableFields, $filterService) {
                return $listableFields->reduce(
                    function(Field $field) use ($tableRecord, $filterService) {
                        $fields = $filterService->getSession()['fields']['visible'] ?? [];

                        return (!$fields && $field->visible) || in_array($field->id, $fields);
                    }
                );
            },
            'en_GB'
        );
    }

}