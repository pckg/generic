<?php

namespace Pckg\Dynamic\Controller;

use Pckg\Concept\Reflect;
use Pckg\Database\Entity;
use Pckg\Database\Helper\Convention;
use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Tab;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Resolver\TableQl;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Generic\Controller\Generic;
use Pckg\Maestro\Service\Tabelize;

class HttpQl
{

    /**
     * @return Table
     */
    protected function fetchTable(Dynamic $dynamicService)
    {
        /**
         * Fetch main table set in request.
         */
        $path = get('path', null);
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }

        return (new TableQl($dynamicService))->resolve($path);
    }

    /**
     * Get all records.
     *
     * @param Table $table
     * @return mixed|null
     * @throws \Throwable
     */
    public function getTableAction(Table $table)
    {
        $tabelize = resolve(Tabelize::class);
        $tabelize->setEnriched(false);

        return Reflect::method($this, 'searchIndexAction', ['table' => $table, 'tabelize' => $tabelize]);
    }

    public function getRecordAction(Table $table, Record $record)
    {
        $tabelize = resolve(Tabelize::class);
        $tabelize->setEnriched(false);

        return [
            'record' => Reflect::method(Records::class, 'getViewAction', ['table' => $table, 'record' => $record, 'tabelize' => $tabelize])['mappedRecord']
        ];
    }

    public function patchRecordAction(Table $table, Record $record)
    {
        $tabelize = resolve(Tabelize::class);
        $tabelize->setEnriched(false);

        return [
            'record' => Reflect::method(Records::class, 'patchEditAction', ['table' => $table, 'record' => $record, 'tabelize' => $tabelize])['mappedRecord']
        ];
    }

    // full update
    public function postRecordAction(Table $table, Record $record)
    {
        $tabelize = resolve(Tabelize::class);
        $tabelize->setEnriched(false);

        return [
            'record' => Reflect::method(Records::class, 'postEditAction', ['table' => $table, 'record' => $record, 'tabelize' => $tabelize])['mappedRecord']
        ];
    }

    public function deleteRecordAction(Table $table, Record $record)
    {
        $tabelize = resolve(Tabelize::class);
        $tabelize->setEnriched(false);

        return Reflect::method(Records::class, 'deleteDeleteAction', ['table' => $table, 'record' => $record, 'tabelize' => $tabelize])['mappedRecord'];
    }

    /**
     * Insert multiple records.
     */
    public function postTableAction(Table $table)
    {
        return Reflect::method(Records::class, 'postAddAction', ['table' => $table]);
    }

    /**
     * Patch multiple records.
     */
    public function patchTableAction()
    {

    }

    /**
     * Delete multiple records.
     */
    public function deleteTableAction()
    {

    }

    /**
     * This works the same as postAddAction from Record.
     * Except, it accepts data in other format.
     */
    public function putIndexAction(Dynamic $dynamicService)
    {
        $table = $this->fetchTable($dynamicService);
        $form = resolve(\Pckg\Dynamic\Form\Dynamic::class);
        return resolve(Records::class)->postAddAction($form, $table);
    }

    /**
     * This works only for file uploads.
     */
    public function postUploadAction(Dynamic $dynamic)
    {
        if (!files()->all()) {
            throw new \Exception('Incomplete request');
        }

        /**
         * We are uploading a file to the field.
         */
        $table = $this->fetchTable($dynamic);
        $field = Field::getOrFail(['field' => get('field'), 'dynamic_table_id' => $table->id]);
        $record = $table->createEntity()->where('id', get('record'))->oneOrFail();

        return resolve(Records::class)->postUploadAction($table, $record, $field);
    }

    /**
     * Return list of all records, paginated.
     *
     * @param Dynamic $dynamicService
     * @param Tabelize $tabelize
     * @param Table|null $table
     * @param bool $noLimit
     * @return array
     */
    public function searchIndexAction(
        Dynamic $dynamicService,
        Tabelize $tabelize,
        Table $table = null,
        $noLimit = false
    )
    {
        if (!$table) {
            $table = $this->fetchTable($dynamicService);
        }

        /**
         * Read Orm data from body or headers.
         */
        $ormFields = json_decode(post('X-Pckg-Orm-Fields', ''), true);
        $ormFilters = json_decode(post('X-Pckg-Orm-Filters', ''), true);
        $ormPaginator = json_decode(post('X-Pckg-Orm-Paginator', ''), true);
        $ormSearch = json_decode(post('X-Pckg-Orm-Search', ''), true);
        $ormMeta = json_decode(post('X-Pckg-Orm-Meta', ''), true);

        foreach (['Fields' => [], 'Filters' => [], 'Paginator' => [], 'Search' => null, 'Meta' => []] as $key => $def) {
            if (!${'orm' . $key}) {
                ${'orm' . $key} = json_decode(request()->getHeader('X-Pckg-Orm-' . $key, ''), true);
            }
            if (!${'orm' . $key}) {
                ${'orm' . $key} = get(strtolower($key), []);
            }
            if (!${'orm' . $key}) {
                ${'orm' . $key} = $def;
            }
        }

        /**
         * Set defaults.
         */
        if (!$ormFields) {
            $ormFields = $table->fields->map('field')->all();
        }

        /**
         * When relation is set we want to display only values for related product.
         *
         * @T00D00 - solve this on JS level, apply proper filter.
         */
        $entity = null;
        /*if (false && $relation) {
            $entity = $relation->over_table_id ? $relation->overTable->createEntity()
                : $relation->showTable->createEntity();

            $relation->applyRecordFilterOnEntity($record, $entity);
        } else {*/
        $entity = $table->createEntity(null, false);
        //}

        $relations = (new \Pckg\Dynamic\Entity\Relations())->withShowTable()
            ->withOnField()
            ->withForeignField()
            ->where('on_table_id', $table->id)
            ->where('dynamic_relation_type_id', 1)
            ->all();

        foreach ($relations as $r) {
            $r->loadOnEntity($entity, $dynamicService);
        }

        /**
         * Apply filter, sort, group, limit and fields sub-services.
         */
        $dynamicService->setTable($table);

        $dynamicService->getFilterService()->applyOnEntity($entity, $ormFilters);
        $dynamicService->getPaginateService()->applyOnEntity($entity, $ormPaginator);
        $dynamicService->getSortService()->applyOnEntity($entity, $ormPaginator);
        $dynamicService->getFilterService()->filterByGet($entity, $relations, $ormSearch);

        /**
         * Apply relation
         */
        if ($ormMeta && isset($ormMeta['relation'])) {
            $relation = Relation::gets($ormMeta['relation']);
            $relation->applyRawFilterOnEntity($ormMeta['record'], $entity);
            $relation->applyFilterOnEntity($entity);
        }

        /**
         * Join extensions (translations, permissions and deletable).
         */
        $dynamicService->selectScope($entity);

        /**
         * Get all relations for fields with type (select).
         *
         * @T00D00 - load only required data.
         */
        $listableFields = $table->listableFields;
        $listedFields = $table->getFields($listableFields, $dynamicService->getFilterService(), $ormFields);

        /**
         * Transform custom fields (php, geo).
         */
        $fieldTransformations = $dynamicService->getFieldsTransformations($entity, $listableFields);

        /**
         * Optimize selected fields (php, mysql).
         */
        $dynamicService->optimizeSelectedFields($entity, $listedFields);

        /**
         * Group by primary key when joins are made so we display only 1 record per ID.
         */
        if (!$entity->getQuery()->getGroupBy()) {
            $entity->groupBy('`' . $entity->getTable() . '`.`id`');
        }

        /**
         * No limit for export
         */
        if ($noLimit) {
            $entity->limit(null);
        }

        /**
         * Prepare tabelize.
         */
        $tabelize->setTable($table);

        /**
         * Set proper fields.
         */
        $fields = [];
        $fieldTransformations = [];
        $this->getOrmFieldsAndTransformations($entity, $table, $ormFields, $fields, $fieldTransformations);
        $tabelize->setFields($fields);
        $tabelize->setFieldTransformations($fieldTransformations);

        /**
         * Fetch page records and total.
         */
        $one = get('getter') === 'one';
        $records = $entity->count()->{$one ? 'one' : 'all'}();
        $total = $one ? 1 : $records->total();

        /**
         * Transform records for frontend.
         */
        $records = $records ? $tabelize->transformCollection($one ? collect([$records]) : $records) : null;

        /**
         * Return all data.
         */
        return [
            ($one ? 'record' : 'records') => $one ? ($records[0] ?? null) : $records,
            'groups' => [],
            'paginator' => [
                'total' => $total,
                'url' => router()->getUri() . (get('search') ? '?search=' . get('search') : ''),
            ],
        ];
    }

    public function getOrmFieldsAndTransformations(
        Entity $entity,
        Table $table,
        $ormFields,
        &$fields,
        &$fieldTransformations
    )
    {
        foreach ($ormFields as $field) {
            if (strpos($field, '.') === false) {
                $fieldRecord = Field::gets(['field' => $field, 'dynamic_table_id' => $table->id]);
                if (!$fieldRecord) {
                    continue;
                }

                $fields[] = $fieldRecord;
                continue;
            }

            /**
             * Table is orders
             * Field is ordersUsers.packet.offer.title
             * Add select: SELECT (...) AS 'ordersUsers.packet.offer.title'
             */
            $alias = str_replace(' ', '', Convention::toCamel(str_replace('.', ' ', $field)));
            $entity->addSelect([
                $field => $alias . '.title',
            ]);

            $subquery = $this->getOrmFieldSubquery($table, $field);
            $keyMatch = $this->getOrmFieldKeyMatch($table, $field);

            if (!$subquery || !$keyMatch) {
                continue;
            }

            $entity->join('LEFT JOIN (' . $subquery . ') AS ' . $alias . ' ON ' . $alias . '.' . $keyMatch);

            $fieldTransformations[$field] = function ($record) use ($field) {
                /**
                 * This should be joined?
                 */
                return $record->{$field};
            };
        }
    }

    protected function getOrmFieldKeyMatch(Table $table, $field)
    {
        $alias = explode('.', $field)[0];

        $relation = (new \Pckg\Dynamic\Entity\Relations())->where('on_table_id', $table->id)
            ->where('alias', $alias)->one();

        return $relation->onField->field . ' = ' . $table->table . '.id';
    }

    /**
     * @param $field
     * @param $alias
     *              orderUsers.packet.offer.title
     *              Sub-select: SELECT offers.title FROM orders_users INNER JOIN packets INNER JOIN offers GROUP BY
     *              orders_users.order_id
     */
    protected function getOrmFieldSubquery(Table $table, $field)
    {
        $split = explode('.', $field);
        $onTable = $table;
        $showTable = null;

        $from = null;
        $groupBy = null;
        $select = [];
        $join = [];
        $repositoryCache = $table->getRepository()->getCache();
        foreach ($split as $i => $relationAlias) {
            if ($i < (count($split) - 1)) {
                $relation = (new \Pckg\Dynamic\Entity\Relations())->where('on_table_id', $onTable->id)
                    ->where('alias', $relationAlias)
                    ->one();

                if (!$relation) {
                    return;
                }

                $showTable = $relation->showTable;
                $prevOnTable = $onTable;
                $onTable = $showTable; // set for future
                if (!$from) {
                    $from = $showTable->table;
                    $groupBy = $showTable->table . '.' . $relation->onField->field;
                    $select[] = $groupBy;
                    continue;
                }
            } else {
                if ($repositoryCache->tableHasField($showTable->table . '_i18n', $relationAlias)) {
                    $join[] = $showTable->table . '_i18n ON ' . $showTable->table . '_i18n.id = ' . $showTable->table .
                        '.id AND ' . $showTable->table . '_i18n.language_id = \'en\'';
                    $select[] = $showTable->table . '_i18n.' . $relationAlias;
                    continue;
                }
                $select[] = $showTable->table . '.' . $relationAlias;
                continue;
            }

            if ($relation->dynamic_relation_type_id === 1) {
                $join[] = $showTable->table . ' ON ' . $showTable->table . '.id = ' . $prevOnTable->table . '.' .
                    $relation->onField->field;
                continue;
            } elseif ($relation->dynamic_relation_type_id === 2) {
                $join[] = $showTable->table . ' ON ' . $showTable->table . '.' . $relation->onField->field . ' = ' .
                    $prevOnTable->table . '.id';
                continue;
            }

            return;
        }

        if (!$from) {
            return;
        }

        $select = 'SELECT ' . implode(', ', $select) . ' FROM ' . $from . ' INNER JOIN ' .
            implode(' INNER JOIN ', $join) . ' GROUP BY ' . $groupBy;

        return $select;
    }

    public function searchExportAction(Dynamic $dynamic, Tabelize $tabelize)
    {
        $table = $this->fetchTable($dynamic);
        $tabelize->setDataOnly();
        $data = $this->searchIndexAction($dynamic, $tabelize, $table, true);

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 60);
        set_time_limit(60);

        $exportService = new \Pckg\Dynamic\Service\Export();
        $strategy = $exportService->useStrategy(get('format', 'csv'));

        $strategy->setData(collect($data['records']));

        $strategy->setFileName(($table->name ?? $table->table) . '-' . date('Ymd-his') . '-' .
            substr(sha1(microtime()), 0, 8) . '.' . $strategy->getExtension());

        $file = $strategy->save();

        return [
            'file' => '/api/http-ql/download?file=' . substr($file, strrpos($file, '/') + 1),
        ];
    }

    public function getDownloadAction()
    {
        $file = get('file', null);

        response()->download(path('tmp') . $file, $file);
    }

    public function getDefinitionAction()
    {
        return [
            'entities' => (new \Pckg\Dynamic\Entity\Tables())->where('repository', 'default')
                ->joinPermissionTo('write')
                ->withFields(function (HasMany $fields) {
                    $fields->orderBy('order');
                    $fields->withFieldType();
                })
                ->orderBy('`order`')
                ->all()
                ->map(function (Table $table) {
                    return [
                        'table' => $table->table,
                        'title' => $table->title,
                        'fields' => $table->fields->map(function (Field $field) {
                            return [
                                'field' => $field->field,
                                'title' => $field->title,
                                'type' => $field->fieldType->slug,
                                'required' => !!$field->required,
                            ];
                        })->keyBy('field')->all()
                    ];
                })->keyBy('table')->all(),
        ];
    }
}
