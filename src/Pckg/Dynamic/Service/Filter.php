<?php namespace Pckg\Dynamic\Service;

use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\SphinxQL;
use Pckg\CollectionInterface;
use Pckg\Database\Entity;
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

        $signMapper = [
            'equals'          => '=',
            'greater'         => '>',
            'greaterOrEquals' => '>=',
            'lower'           => '<',
            'lowerOrEquals'   => '<=',
            'not'             => '!=',
            'in'              => 'IN',
            'notIn'           => 'NOT IN',
            'like'            => 'LIKE',
            'isNull'          => 'IS NULL',
            'notNull'         => 'IS NOT NULL',
        ];

        foreach ($session['fields']['filters'] ?? [] as $filter) {
            $field = (new Fields())->where('id', $filter['field'])->oneOrFail();

            if ($field->fieldType->slug == 'boolean') {
                $entity->where($field->field, $filter['value'] ? 1 : null, $signMapper[$filter['method']]);
                continue;
            }

            if ($field->fieldType->slug == 'php') {
                if (method_exists($entity, 'select' . ucfirst($field->field) . 'Field')) {
                    //$subquery = $entity->{'select' . ucfirst($field->field) . 'Field'}();
                    //$subquery->addSelect(['order_id']);
                    //$entity->join($subquery, 'isLateWithPayment.id = orders.id', $field->field);
                    //$entity->where($field->field, $filter['value'], $signMapper[$filter['method']]);
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

    public function filterByGet($entity)
    {
        if ($search = get('search')) {
            $query = clone $entity->getQuery();

            /**
             * This should be applied on all related entities, in separate query.
             * We cannot use this query because not all relations are joined.
             * We cannot use separate query because relations are somehow nod accessible.
             * We need to make join through 2 levels: relations of entity and relations of relations.
             */
            foreach ($entity->getWith() as $with) {
                $with->mergeToQuery($query);
                $with->getRightEntity()->getQuery()->mergeToQuery($query);
            }

            /**
             * Transform all joins to LEFT joins so main data is always displayed.
             */
            $query->makeJoinsLeft();

            /**
             * @T00D00 - comment this
             */
            foreach ($query->getJoin() as $join) {
                $table = substr($join, 11, strpos($join, '`', 11) - 11);
                if (!strpos($join, '` AS `')) {
                    $tables[$table] = $table;
                } else {
                    $start = strpos($join, '` AS `') + 6;
                    $tables[substr($join, $start, strpos($join, '`', strpos($join, '` AS `') + 7) - $start)] = $table;
                }
            }
            $tables[$entity->getTable()] = $entity->getTable();

            $query->select($query->getTable() . '.id');

            $where = new Parenthesis();
            $where->setGlue('OR');
            foreach ($tables as $alias => $table) {
                $searchableFields = (new Tables())->where('table', str_replace('_i18n', '', $table))
                                                  ->one()->searchableFields->keyBy('field');
                foreach ($entity->getRepository()->getCache()->getTableFields($table) as $field) {
                    if (!$searchableFields->hasKey($field)) {
                        continue;
                    }
                    $where->push($alias . '.' . $field . ' LIKE ?');
                    $query->bind('%' . $search . '%', 'where');
                }
            }
            $query->where($where);

            $newEntity = $this->table->createEntity();
            $newEntity->setQuery($query);
            $newEntity->limit(null);
            $newEntity->count(false);

            $entity->where('id', $newEntity, 'IN');
        }
    }

    public function getTypeMethods()
    {
        return [
            'equals'          => '=',
            'greater'         => '>',
            'greaterOrEquals' => '>=',
            'lower'           => '<',
            'lowerOrEquals'   => '<=',
            'not'             => 'NOT',
            'in'              => 'IN',
            'notIn'           => 'NOT IN',
            'like'            => 'LIKE',
            'isNull'          => 'IS NULL',
            'notNull'         => 'IS NOT NULL',
        ];
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
            'in'     => [
                'label' => 'IN',
            ],
            'notIn'  => [
                'label' => 'NOT IN',
            ],
        ];
    }

}
