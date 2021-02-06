<?php

namespace Pckg\Dynamic\Service;

use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\SphinxQL;
use Pckg\CollectionInterface;
use Pckg\Database\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Query;
use Pckg\Database\Query\Parenthesis;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Framework\Request\Data\Get;

class Filter extends AbstractService
{

    protected $get;

    public function __construct(Get $get)
    {
        $this->get = $get;
    }

    public function getSaveFilterUrl()
    {
        return url('dynamic.record.filter.save', [
            'table' => $this->table,
        ]);
    }

    public function getAppliedFilters()
    {
        return $this->getSession('filter');
    }

    public function getAppliedRelationFilters()
    {
        return $this->getSession('relationFilter');
    }

    public function getAvailableFilters()
    {
        return $this->makeFields($this->table->listableFields(function (HasMany $fields) {
            $fields->where('dynamic_field_type_id', 19, '!=');
        }));
    }

    public function getAvailableRelationFilters()
    {
        return $this->table->relations->map(function (Relation $relation) {
            /**
             * @T00D00 - load via ajax if possible?
             *         - optimize related selects like ($relation->value = '$record->order->user->city->title' ;-))
             */
            $options = $relation->getOptions();

            return [
                'id'            => $relation->id,
                'field'         => $relation->id,
                'table'         => $relation->showTable->table,
                'fields'        => $this->makeFields($relation->showTable->fields),
                'type'          => $relation->dynamic_relation_type_id,
                'options'       => [
                    'options' => $options,
                ],
                'filterOptions' => $options,
            ];
        });
    }

    protected function makeFields(CollectionInterface $collection)
    {
        return $collection->each(function (Field $field) {
            $type = $field->fieldType->slug;

            $options = [];
            if ($type == 'select') {
                $relation = $field->hasOneSelectRelation;
                if ($relation) {
                    $options = $relation->getOptions();
                }
            }

            return [
                'field'   => $field->field,
                'label'   => $field->title ?? $field->field,
                'type'    => $type,
                'options' => $options,
            ];
        })->keyBy('field');
    }

    public function applyFilterOnRelation(Entity $entity, $filter)
    {
        $parts = explode('.', $filter['k']);
        $relation = (new Relations())->where('alias', $parts[0])->where('on_table_id', $this->table->id)->one();

        if (!$relation) {
            if (dev()) {
                ddd('no relation', $filter);
            }

            return;
        }
        $signMapper = $this->getTypeMethods();

        /**
         * We're on table orders:
         *  - belongs to user: Users
         *  - has many ordered packets: OrdersUsers
         */
        $subEntity = $relation->showTable->createEntity(null, false);

        if ($relation->dynamic_relation_type_id == 1) { // belongs to
            $subField = (new Fields())->where('field', $parts[1])
                                      ->where('dynamic_table_id', $relation->show_table_id)
                                      ->one();
            if (!$subField) {
                if (dev()) {
                    ddd('no subfield', $parts);
                }

                return;
            }

            $subEntity->select($subEntity->getTable() . '.id')->where($subField->field, $filter['v'], $filter['c']);
            $entity->where($relation->onField->field, $subEntity);
        } elseif ($relation->dynamic_relation_type_id == 2) { // has many
            $subEntity->select($relation->onField->field);

            if (count($parts) <= 1) {
                if (dev()) {
                    ddd('more parts are required?', $parts, $filter);
                }

                return;
            }

            if (count($parts) > 2) {
                if (count($parts) > 3) {
                    if (dev()) {
                        ddd('more than 3 levels', $filter, $parts);
                    }

                    return;
                }
                $relation2 = (new Relations())->where('alias', $parts[1])
                                              ->where('on_table_id', $relation->show_table_id)
                                              ->one();
                if ($relation2->dynamic_relation_type_id == 1) { // packet belongs to offer
                    $subField2 = (new Fields())->where('field', $parts[2])
                                               ->where('dynamic_table_id', $relation2->show_table_id)
                                               ->one();
                    if (!$subField2) {
                        if (dev()) {
                            ddd('no subfield 2 2', $parts);
                        }

                        return;
                    }
                    $subEntity2 = $relation2->showTable->createEntity(null, false);

                    $subEntity2->select($subEntity2->getTable() . '.id')
                               ->where($subField2->field, $filter['v'], $filter['c']);
                    $subEntity->where($relation2->onField->field, $subEntity2);
                    $subEntity->select($relation->onField->field);

                    $entity->where('id', $subEntity);
                } else {
                    if (dev()) {
                        ddd('has any?');
                    }

                    return;
                }
            } else {
                $subField = (new Fields())->where('field', $parts[1])
                                          ->where('dynamic_table_id', $relation->show_table_id)
                                          ->one();
                if (!$subField) {
                    if (dev()) {
                        ddd('no subfield 4', $parts);
                    }

                    return;
                }

                $subEntity->select($relation->onField->field)->where($subField->field, $filter['v'], $filter['c']);
                $entity->where('id', $subEntity);

                return;
            }
        } else {
            if (dev()) {
                ddd('unknown relation type', $relation->data());
            }

            return;
        }

        if (count($parts) > 2) {
            if (count($parts) > 3) {
                ddd('new level ...', $filter);

                return;
            }

            $relation2 = (new Relations())->where('alias', $parts[1])
                                          ->where('on_table_id', $relation->show_table_id)
                                          ->one();
            if ($relation2->dynamic_relation_type_id == 1) { // belongs to
                $subField2 = (new Fields())->where('field', $parts[2])
                                           ->where('dynamic_table_id', $relation2->show_table_id)
                                           ->one();
                if (!$subField2) {
                    if (dev()) {
                        ddd('no subfield2', $parts);
                    }

                    return;
                }

                $subEntity2 = $relation2->showTable->createEntity();

                $subEntity2->select($subEntity2->getTable() . '.id')
                           ->where($subField2->field, $filter['v'], $signMapper[$filter['c']]);
                $subEntity->where($relation->onField->field, $subEntity2);
            } else {
                if (dev()) {
                    ddd('not belongs to 2', $parts, $filter);
                }

                return;
            }

            return;
        }
    }

    public function applyFilterOnEntity(Field $field, Entity $entity, $filter)
    {
        $signMapper = $this->getTypeMethods();

        if (!$field) {
            return;
        }

        if ($field->fieldType->slug == 'boolean') {
            $entity->where($field->field, $filter['v'] ? 1 : null, $signMapper[$filter['c']]);

            return;
        }

        if (in_array($field->fieldType->slug, ['mysql'])) {
            if (method_exists($entity, 'select' . ucfirst($field->field) . 'Field')) {
                $entity->having('`' . $field->field . '`', $filter['v'], $signMapper[$filter['c']]);
            }

            return;
        }

        if (in_array($field->fieldType->slug, ['php'])) {
            if (method_exists($entity, 'select' . ucfirst($field->field) . 'Field')) {
                $entity->where('`' . $field->field . '`', $filter['v'], $signMapper[$filter['c']]);
            }

            return;
        }

        if (in_array($filter['c'], ['!=', 'notIn'])) {
            $entity->where($field->field . ' IS NULL OR ' . $field->field, $filter['v'], $filter['c']);

            return;
        }

        $entity->where($field->field, $filter['v'], $filter['c']);
    }

    public function applyOnEntity(Entity $entity, $filters = [])
    {
        $signMapper = $this->getTypeMethods();

        /**
         * Field filters.
         */
        foreach ($filters as $i => $filter) {
            if (!is_string($filter['k'])) {
                continue;
            }

            if (!in_array($filter['c'], $signMapper)) {
                continue;
            }

            if (strpos($filter['k'], '.')) {
                $this->applyFilterOnRelation($entity, $filter);
                continue;
            }

            $field = (new Fields())->where('field', $filter['k'])->where('dynamic_table_id', $this->table->id)->one();

            if (!$field) {
                continue;
            }

            $this->applyFilterOnEntity($field, $entity, $filter);
        }

        /**
         * Relation filters
         */
        $joined = false;
        foreach ($filters as $filter) {
            if (is_string($filter['k'])) {
                continue;
            }

            continue;

            $relation = (new Relations())->withOnField()
                                         ->withShowTable()
                                         ->withOnTable()
                                         ->where('id', $relationFilter['relation'])
                                         ->one();

            if ($relation->dynamic_relation_type_id == 1 || !isset($relationFilter['field'])) {
                $field = null;
                $alias = null;

                if ($relationFilter['subfield']) {
                    /*$alias = 'relation_' . $relation->id . '_' . $relation->showTable->table;
                    $relation->joinToEntity($entity, $alias);
                    $joined = true;*/

                    /**
                     * When listing orders users we filter by orders_users.units.unit_group_id
                     */
                    $field = Field::getOrFail(['id' => $relationFilter['subfield']]);

                    $subrelation = (new Relations())->withOnField()
                                                    ->withShowTable()
                                                    ->withOnTable()
                                                    ->where('show_table_id', $field->dynamic_table_id)
                                                    ->where('on_table_id', $relation->show_table_id)
                                                    ->one();

                    $subalias = 'relation_' . $subrelation->id . '_' . $subrelation->showTable->table;
                    /*$subrelation->joinToEntity($entity, $subalias, $alias);

                    $entity->where($subalias . '.' . $field->field, $relationFilter['value'],
                                   $signMapper[$relationFilter['method']]);*/
                } elseif (isset($relationFilter['field'])) {
                    $alias = 'relation_' . $relation->id . '_' . $relation->showTable->table;
                    $relation->joinToEntity($entity, $alias);
                    $joined = true;

                    /**
                     * When listing orders users we filter by orders.status_id
                     */
                    $field = Field::getOrFail(['id' => $relationFilter['field']]);

                    $entity->where(
                        $alias . '.' . $field->field,
                        $relationFilter['value'],
                        $signMapper[$relationFilter['method']]
                    );

                    /*$field = Field::getOrFail(['id' => $relationFilter['field']]);

                    $entity->where($relation->onTable->table . '.' . $relation->onField->field,
                                   $relation->showTable->createEntity()
                                                       ->select([
                                                                    $relation->showTable->table . '.id',
                                                                ])
                                                       ->where($field->field, $relationFilter['value'],
                                                               $signMapper[$relationFilter['method']]));*/
                }

                if (!$relationFilter['field'] && !$relationFilter['subfield']) {
                    $entity->where(
                        $relation->onField->field,
                        $relationFilter['value'],
                        $signMapper[$relationFilter['method']]
                    );
                }
            } elseif ($relation->dynamic_relation_type_id == 2) {
                $field = Field::getOrFail(['id' => $relationFilter['field']]);

                $joined = true;
                $entity->join(
                    'INNER JOIN ' . $relation->showTable->table,
                    $relation->onTable->table . '.id = ' . $relation->showTable->table . '.' .
                    $relation->onField->field
                );

                $entity->where(
                    $relation->showTable->table . '.' . $field->field,
                    $relationFilter['value'],
                    $signMapper[$relationFilter['method']]
                );
            }

            if ($joined && !$entity->getQuery()->getGroupBy()) {
                $entity->distinct();
                //$entity->addGroupBy($entity->getTable() . '.id');
            }
        }
    }

    public function filterByGet(Entity $entity, Collection $relations = null, $search = null)
    {
        if (!$search) {
            $search = get('search');
        }
        if (!$search) {
            return;
        }

        /**
         * We will build new part of sql.
         */
        $where = new Parenthesis();
        $where->setGlue('OR');

        /**
         * Filter relations in separate query.
         * Add foreign field to optimize things.
         */
        foreach ($relations ?? [] as $relation) {
            /**
             * Perform search on relations.
             * When searching on orders_bills.orders_user_id
             *  - search on orders_users.* -> orders_users.id => orders_bills.orders_user_id
             */
            $relationEntity = $relation->showTable->createEntity();
            $this->filterByGet($relationEntity, null, $search);
            $data = $relationEntity->addSelect([$relationEntity->getTable() . '.id'])->all()->map('id')->all();
            if ($data) {
                $where->push($relation->onTable->table . '.' . $relation->onField->field . ' IN (' .
                             str_repeat('?,', count($data) - 1) . '?)', $data);
            }
        }

        if ($relations && $selected = get('selected')) {
            $exploded = explode(',', $selected);
            $where->push(
                $entity->getTable() . '.id IN (' . substr(str_repeat('?,', count($exploded)), 0, -1) . ')',
                $exploded
            );
        }

        /**
         * Get all tables that are currently linked to query.
         */
        $tables = $this->getTablesFromEntity($entity);

        /**
         * Perform LIKE query on all fields listed in tables
         *
         * @T00D00 - filter them by filterable fields only
         */
        $this->fullSearchTables($entity, $tables, $where, $search);
    }

    public function getTypeMethods()
    {
        $data = [
            'is'              => '=',
            'greater'         => '>',
            'greaterOrEquals' => '>=',
            'lower'           => '<',
            'lowerOrEquals'   => '<=',
            'not'             => '!=',
            'like'            => 'LIKE',
            'notLike'         => 'NOT LIKE',
            'isNull'          => 'IS NULL',
            'notNull'         => 'IS NOT NULL',
            'in'              => 'IN',
            'notIn'           => 'NOT IN',
        ];

        return $data;
    }

    public function getRelationMethods()
    {
        return [
            'is'  => [
                'label' => '=',
            ],
            'not' => [
                'label' => 'NOT',
            ],
        ];
    }

    /**
     * @param Entity $entity
     * @param Query  $query
     * @param array  $tables
     * @param        $search
     */
    private function fullSearchTables(Entity $entity, array $tables, Parenthesis $where, $search)
    {
        foreach ($tables as $alias => $table) {
            $tableRecord = (new Tables())->where('table', str_replace('_i18n', '', $table))->one();
            if (!$tableRecord) {
                continue;
            }
            $searchableFields = $tableRecord->searchableFields->keyBy('field');
            $match = [];
            foreach ($entity->getRepository()->getCache()->getTableFields($table) as $field) {
                $searchableField = $searchableFields[$field];
                if (!$searchableField || ($field == 'id' && strpos($table, '_i18n'))) {
                    continue;
                }
                if ($searchableField->fieldType->slug == 'datetime') {
                    /**
                     * Binary option will compare fields byte by byte.
                     * Should be faster than convering all dates to speciffic format.
                     */
                    // $s = 'DATE_FORMAT(' . $alias . '.' . $field . ', \'%Y-%m-%d %H:%i:%s\') LIKE ?';
                    $s = $alias . '.' . $field . ' LIKE BINARY ?';
                    $where->push($s, '%' . $search . '%');
                } elseif ($table == 'mails_sents' && in_array($searchableField->field, ['content'])) {
                    $match[] = $alias . '.' . $field;
                } else {
                    $s = $alias . '.' . $field . ' LIKE ?';
                    $where->push($s, '%' . $search . '%');
                }
            }
            if ($match) {
                $s = 'MATCH(' . implode(',', $match) . ') AGAINST(?)';
                $where->push($s, $search);
            }
        }
        if ($where->hasChildren()) {
            $entity->where($where);
        }
    }

    /**
     * @param $query
     * @param $tables
     *
     * @return mixed
     */
    private function getTablesFromEntity(Entity $entity)
    {
        $tables = [$entity->getTable() => $entity->getTable()];

        /**
         * Make sure that translations are joined and matched.
         */
        if ($entity->isTranslatable()) {
            if (!$entity->isTranslated()) {
                $entity->joinTranslations()->addSelect([$entity->getTable() . '.*']);
            }
            $tables[$entity->getTable() . $entity->getTranslatableTableSuffix()] = $entity->getTable();
        }

        foreach ($entity->getQuery()->getJoin() as $join) {
            $first = strpos($join, '`');
            $table = substr($join, $first + 1, strpos($join, '`', $first + 1) - $first - 1);
            if (!strpos($join, '` AS `')) {
                $tables[$table] = $table;
            } else {
                $start = strpos($join, '` AS `') + 6;
                $tables[substr($join, $start, strpos($join, '`', strpos($join, '` AS `') + 6) - $start)] = $table;
            }
        }

        return $tables;
    }
}
