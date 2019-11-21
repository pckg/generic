<?php namespace Pckg\Dynamic\Record;

use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Filter;
use Pckg\Framework\View\Twig;
use Pckg\Maestro\Service\Tabelize;

class Table extends Record
{

    protected $entity = Tables::class;

    protected $toArray = ['privileges'];

    public function getPrivilegesAttribute()
    {
        $tables = (new Tables(null, null, false));
        $tables->usePermissionableTable();

        return $tables->where('id', $this->id)
                      ->where('user_group_id', auth()->getGroupId())
                      ->all()
                      ->keyBy('action')
                      ->map(function() {
                          return true;
                      })
                      ->all();
    }

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
                $relation->joinPermission();
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

    public function getFields($listableFields, Filter $filterService, $fields = [])
    {
        $tableRecord = $this;

        return $listableFields->reduce(
            function(Field $field) use ($tableRecord, $filterService, $fields) {
                return in_array($field->field, $fields);
            }
        );
    }

    /*public function getStringVues($entity, $dynamicService)
    {
        $tabelize = new Tabelize();
        $tabelize->setEntityActions($this->getEntityActions())
                 ->setRecordActions($this->getRecordActions())
                 ->setViews($this->actions()->keyBy('slug'));

        $entity = $this->loadTwigDirsForEntity($entity, $dynamicService);

        return $tabelize->__toStringParsedViews();
    }*/

    public function loadTwigDirsForEntity($entity, $dynamicService)
    {
        if (!$entity) {
            $entity = $this->createEntity(null, false);

            $partial = implode(path('ds'), array_slice(explode('\\', get_class($entity)), 0, -2)) . path('ds') .
                'View' . path('ds');
            $dir = path('app_src') . $partial;
            Twig::addDir($dir);
            if (config('app') != config('app_parent')) {
                $dir = path('apps') . config('app_parent') . path('ds') . 'src' . path('ds') . $partial;
                Twig::addDir($dir);
            }
            /**
             * This is needed for table actions.
             */
            Twig::addDir($dir . 'tabelize' . path('ds') . 'recordActions' . path('ds'));
            Twig::addDir($dir . 'tabelize' . path('ds') . 'entityActions' . path('ds'));

            $dynamicService->selectScope($entity);
        }

        return $entity;
    }

}