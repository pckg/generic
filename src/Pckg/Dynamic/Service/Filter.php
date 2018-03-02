<?php namespace Pckg\Dynamic\Service;

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
        return url(
            'dynamic.record.filter.save',
            [
                'table' => $this->table,
            ]
        );
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
        return $this->makeFields(
            $this->table->listableFields(
                function(HasMany $fields) {
                    $fields->where('dynamic_field_type_id', 19, '!=');
                }
            )
        );
    }

    public function getAvailableRelationFilters()
    {
        return $this->table->relations->map(
            function(Relation $relation) {
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
            }
        );
    }

    protected function makeFields(CollectionInterface $collection)
    {
        return $collection->each(
            function(Field $field) {
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
            }
        )->keyBy('field');
    }

    public function applyOnEntity(Entity $entity)
    {
        $session = $this->getSession();

        $signMapper = $this->getTypeMethods(true);

        foreach ($session['fields']['filters'] ?? [] as $filter) {
            $field = (new Fields())->where('id', $filter['field'])->oneOrFail();

            if ($field->fieldType->slug == 'boolean') {
                $entity->where($field->field, $filter['value'] ? 1 : null, $signMapper[$filter['method']]);
                continue;
            }

            if (in_array($field->fieldType->slug, ['mysql'])) {
                if (method_exists($entity, 'select' . ucfirst($field->field) . 'Field')) {
                    $entity->having('`' . $field->field . '`', $filter['value'], $signMapper[$filter['method']]);
                }
                continue;
            }

            if (in_array($field->fieldType->slug, ['php'])) {
                if (method_exists($entity, 'select' . ucfirst($field->field) . 'Field')) {
                    $entity->where('`' . $field->field . '`', $filter['value'], $signMapper[$filter['method']]);
                }
                continue;
            }

            $entity->where(
                $field->field,
                $filter['value'],
                $signMapper[$filter['method']]
            );
        }

        /**
         * @T00D00 - join translations
         */
        $joined = false;
        foreach ($session['relations']['filters'] ?? [] as $relationFilter) {
            $relation = (new Relations())->withOnField()
                                         ->withShowTable()
                                         ->withOnTable()
                                         ->where('id', $relationFilter['relation'])
                                         ->one();

            if ($relation->dynamic_relation_type_id == 1 || !isset($relationFilter['field'])) {
                $field = null;
                $alias = null;

                /**
                 * When listing orders users we filter by orders.status_id
                 */
                if (isset($relationFilter['field'])) {
                    $field = Field::getOrFail(['id' => $relationFilter['field']]);
                    $alias = 'relation_' . $relation->id . '_' . $relation->showTable->table;

                    $joined = true;
                    $relation->joinToEntity($entity, $alias);

                    if (!$relationFilter['subfield']) {
                        $entity->where($alias . '.' . $field->field, $relationFilter['value'],
                                       $signMapper[$relationFilter['method']]);
                    }
                }

                /**
                 * When listing orders users we filter by orders_users.units.unit_group_id
                 */
                if ($relationFilter['subfield']) {
                    $field = Field::getOrFail(['id' => $relationFilter['subfield']]);
                    $subrelation = (new Relations())->withOnField()
                                                    ->withShowTable()
                                                    ->withOnTable()
                                                    ->where('show_table_id', $field->dynamic_table_id)
                                                    ->where('on_table_id', $relation->show_table_id)
                                                    ->one();

                    $subalias = 'relation_' . $subrelation->id . '_' . $subrelation->showTable->table;
                    $joined = true;
                    $subrelation->joinToEntity($entity, $subalias, $alias);

                    $entity->where($subalias . '.' . $field->field, $relationFilter['value'],
                                   $signMapper[$relationFilter['method']]);
                }

                if (!$relationFilter['field'] && !$relationFilter['subfield']) {
                    $entity->where(
                        $relation->onField->field,
                        $relationFilter['value'],
                        $signMapper[$relationFilter['method']]
                    );
                }
            } else if ($relation->dynamic_relation_type_id == 2) {
                $field = Field::getOrFail(['id' => $relationFilter['field']]);

                $joined = true;
                $entity->join(
                    'INNER JOIN ' . $relation->showTable->table,
                    $relation->onTable->table . '.id = ' . $relation->showTable->table . '.' .
                    $relation->onField->field
                );

                $entity->where($relation->showTable->table . '.' . $field->field, $relationFilter['value'],
                               $signMapper[$relationFilter['method']]);
            }

            if ($joined && !$entity->getQuery()->getGroupBy()) {
                $entity->distinct();
                //$entity->addGroupBy($entity->getTable() . '.id');
            }
        }
    }

    public function filterByGet(Entity $entity, Collection $relations = null)
    {
        if ($search = get('search')) {
            /**
             * We will build new part of sql.
             */
            $where = new Parenthesis();
            $where->setGlue('OR');

            if ($relations) {
                /**
                 * Filter relations in separate query.
                 * Add foreign field to optimize things.
                 */
                foreach ($relations as $relation) {
                    /**
                     * Perform search on relations.
                     * When searching on orders_bills.orders_user_id
                     *  - search on orders_users.* -> orders_users.id => orders_bills.orders_user_id
                     */
                    $relationEntity = $relation->showTable->createEntity();
                    $this->filterByGet($relationEntity, null);
                    $data = $relationEntity->addSelect([$relationEntity->getTable() . '.id'])->all()->map('id')->all();
                    if ($data) {
                        $where->push($relation->onTable->table . '.' . $relation->onField->field . ' IN (' .
                                     str_repeat('?,', count($data) - 1) . '?)',
                                     $data);
                    }
                }
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
    }

    public function getTypeMethods($backwardsCompatible = false)
    {
        $data = [
            'equals'          => '=',
            'greater'         => '>',
            'greaterOrEquals' => '>=',
            'lower'           => '<',
            'lowerOrEquals'   => '<=',
            'not'             => '!=',
            'like'            => 'LIKE',
            'isNull'          => 'IS NULL',
            'notNull'         => 'IS NOT NULL',
        ];

        if ($backwardsCompatible) {
            $data['in'] = 'IN';
        }

        return $data;
    }

    public function getRelationMethods()
    {
        return [
            'equals' => [
                'label' => '=',
            ],
            'not'    => [
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
            $tableRecord = (new Tables())->where('table', str_replace('_i18n', '', $table))
                                         ->one();
            if (!$tableRecord) {
                continue;
            }
            $searchableFields = $tableRecord->searchableFields->keyBy('field');
            foreach ($entity->getRepository()->getCache()->getTableFields($table) as $field) {
                if (!$searchableFields->hasKey($field) || ($field == 'id' && strpos($table, '_i18n'))) {
                    continue;
                }
                $where->push($alias . '.' . $field . ' LIKE ?', '%' . $search . '%');
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
                $tables[substr($join, $start,
                               strpos($join, '`', strpos($join, '` AS `') + 6) - $start)] = $table;
            }
        }

        return $tables;
    }

}
