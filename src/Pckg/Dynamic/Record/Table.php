<?php namespace Pckg\Dynamic\Record;

use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Filter;

class Table extends Record
{

    protected $entity = Tables::class;

    public function getEntityActions()
    {
        return $this->actions(
            function(HasMany $relation) {
                $relation->where('type', ['entity', 'entity-plugin', 'mixed']);
                $relation->joinPermission();
            }
        );

        return $actions;
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

    public function getListUrl()
    {
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

    /**
     * @return Repository
     */
    public function getRepository()
    {
        $r = $this->repository;

        if (!$r) {
            $r = 'default';
        }
        if ($r == 'gnp') {
            $r = 'default';
        }

        return context()->get(Repository::class . '.' . $r);
    }

    public function getIsTranslatableAttribute()
    {
        return $this->getRepository()->getCache()->hasTable($this->table . '_i18n');
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
            function() use ($entityClass, $repository, $alias, $extensions) {
                $entity = new $entityClass($repository, $alias);

                $entity->setTable($this->table);

                if ($extensions && $entity->isTranslatable() && !$entity->isTranslated()) {
                    $entity->joinTranslations();
                }

                return $entity;
            },
            $_SESSION['pckg_dynamic_lang_id']
        );

        return $entity;
    }

    public function createRecord()
    {
        return $this->createEntity()->getRecord();
    }

    public function fetchFrameworkRecord(Record $record, Entity $entity)
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