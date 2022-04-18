<?php

namespace Pckg\Dynamic\Record;

use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Entity\TableActions;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Filter;
use Pckg\Framework\View\Twig;
use Pckg\Maestro\Service\Tabelize;

/**
 * Class Table
 * @package Pckg\Dynamic\Record
 * @property Table $table
 * @property string $title
 * @property string $repository
 * @property string $framework_entity
 * @property Collection $listableFields
 * @method actions(callable $callback = null)
 * @method hasManyRelation(callable $callback = null)
 * @method belongsToRelation(callable $callback = null)
 */
class Table extends Record
{
    protected $entity = Tables::class;
    protected $toArray = ['privileges', 'titleSingular', 'indexUrl'];

    public function getPrivilegesAttribute()
    {
        return $this->allPermissions
            ->filter('user_group_id', auth()->getGroupId())
            ->keyBy('action')
            ->map(true)
            ->all();
    }

    public function getTitleSingularAttribute()
    {
        $string = $this->title;
        // save some time in the case that singular and plural are the same
        /*if ( in_array( strtolower( $string ), self::$uncountable ) )
            return $string;*/

        // check for irregular plural forms
        /*foreach ( self::$irregular as $result => $pattern )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $string ) )
                return preg_replace( $pattern, $result, $string);
        }*/

        // check for matches using regular expressions
        $singular = array(
            '/(quiz)zes$/i'             => "$1",
            '/(matr)ices$/i'            => "$1ix",
            '/(vert|ind)ices$/i'        => "$1ex",
            '/^(ox)en$/i'               => "$1",
            '/(alias)es$/i'             => "$1",
            '/(octop|vir)i$/i'          => "$1us",
            '/(cris|ax|test)es$/i'      => "$1is",
            '/(shoe)s$/i'               => "$1",
            '/(o)es$/i'                 => "$1",
            '/(bus)es$/i'               => "$1",
            '/([m|l])ice$/i'            => "$1ouse",
            '/(x|ch|ss|sh)es$/i'        => "$1",
            '/(m)ovies$/i'              => "$1ovie",
            '/(s)eries$/i'              => "$1eries",
            '/([^aeiouy]|qu)ies$/i'     => "$1y",
            '/([lr])ves$/i'             => "$1f",
            '/(tive)s$/i'               => "$1",
            '/(hive)s$/i'               => "$1",
            '/(li|wi|kni)ves$/i'        => "$1fe",
            '/(shea|loa|lea|thie)ves$/i' => "$1f",
            '/(^analy)ses$/i'           => "$1sis",
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
            '/([ti])a$/i'               => "$1um",
            '/(n)ews$/i'                => "$1ews",
            '/(h|bl)ouses$/i'           => "$1ouse",
            '/(corpse)s$/i'             => "$1",
            '/(us)es$/i'                => "$1",
            '/s$/i'                     => ""
        );

        foreach ($singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    public function getEntityActions()
    {
        return $this->actions(function (HasMany $relation) {

                $relation->where('type', ['entity', 'entity-plugin', 'mixed']);
                $relation->joinPermission();
        });
        return $actions;
    }

    public function getRecordActions()
    {
        return $this->actions(function (HasMany $relation) {

                $relation->where('type', ['record', 'record-plugin', 'mixed']);
                $relation->joinPermission();
        });
    }

    public function getListTitle()
    {
        return $this->title ?? $this->table;
    }

    public function getFormTitle($type = 'Add')
    {
        return $type . ' ' . lcfirst($this->title ?? ('<i>' . $this->table . '</i>'));
    }

    public function getViewUrl(Record $record = null)
    {
        return $record
            ? url('dynamic.record.view', [
                    'table' => $this,
                    'record' => $record,
                ])
            : url('dynamic.record.list', [
                    'table' => $this,
                ]);
    }

    public function getEditUrl(Record $record)
    {
        return url('dynamic.record.edit', [
                'table'  => $this,
                'record' => $record,
            ]);
    }

    public function getListUrl()
    {
        return url('dynamic.record.list', ['table' => $this]);
    }

    public function getHasManyRelation()
    {
        return $this->relationExists('hasManyRelation')
            ? $this->getRelation('hasManyRelation')
            : $this->hasManyRelation(function (HasMany $relation) {

                    $relation->where('on_table_id', $this->id);
            });
    }

    public function getBelongsToRelation()
    {
        return $this->relationExists('belongsToRelation')
            ? $this->getRelation('belongsToRelation')
            : $this->belongsToRelation(function (BelongsTo $relation) {

                    $relation->where('on_table_id', $this->id);
            });
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
        $entity = runInLocale(function () use ($entityClass, $repository, $alias, $extensions) {

                $entity = new $entityClass($repository, $alias);
                $entity->setTable($this->table);
            if ($extensions && $entity->isTranslatable() && !$entity->isTranslated()) {
                $entity->joinTranslations();
            }

                return $entity;
        }, $_SESSION['pckg_dynamic_lang_id']);
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
        return $listableFields->reduce(function (Field $field) use ($fields) {

                return in_array($field->field, $fields);
        });
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
            /*if (config('app') != config('app_parent')) {
                $dir = path('apps') . config('app_parent') . path('ds') . 'src' . path('ds') . $partial;
                Twig::addDir($dir);
            }*/
            /**
             * This is needed for table actions.
             */
            Twig::addDir($dir . 'tabelize' . path('ds') . 'recordActions' . path('ds'));
            Twig::addDir($dir . 'tabelize' . path('ds') . 'entityActions' . path('ds'));
            $dynamicService->selectScope($entity);
        }

        return $entity;
    }

    public function checkPermissionsFor($action = 'write')
    {
        if (!$this->hasPermissionTo($action)) {
            response()->unauthorized('Missing permissions to write');
        }

        if (!$this->listableFields->count()) {
            response()->unauthorized('Missing view field permissions.');
        }

        (new TableActions())->joinPermissionTo('execute')
            ->where('dynamic_table_id', $this->id)
            ->where('slug', $action === 'read' ? 'view' : 'edit')
            ->oneOrFail(function () {
                response()->unauthorized();
            });
    }

    public function getBelongsToRelations()
    {
        return
            $relations = (new Relations())->withShowTable()
                ->withOnField()
                ->where('on_table_id', $this->id)
                ->where('dynamic_relation_type_id', 1)
                ->all();
    }

    public function getTabelize(Entity $tableEntity)
    {
        $listableFields = $this->listableFields;
        $fieldTransformations = resolve(Dynamic::class)->getFieldsTransformations($tableEntity, $listableFields);

        return (new Tabelize())
            ->setTable($this)
            ->setEntity($tableEntity)
            ->setEntityActions($this->getEntityActions())
            ->setRecordActions($this->getRecordActions())
            ->setViews($this->actions()->keyBy('slug'))
            ->setFields($listableFields)
            ->setFieldTransformations($fieldTransformations);
    }
}
