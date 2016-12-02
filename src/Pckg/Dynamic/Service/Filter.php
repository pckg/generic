<?php namespace Pckg\Dynamic\Service;

use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\SphinxQL;
use Pckg\Collection;
use Pckg\CollectionInterface;
use Pckg\Database\Entity;
use Pckg\Database\Query\Parenthesis;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Table;
use Pckg\Framework\Request\Data\Get;
use Throwable;

class Filter
{

    protected $table;

    protected $get;

    public function __construct(Get $get)
    {
        $this->get = $get;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;
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
        return $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id]['view']['filter'] ?? [];
    }

    public function getAppliedRelationFilters()
    {
        return $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id]['view']['relationFilter'] ?? [];
    }

    public function getAvailableFilters()
    {
        return $this->makeFields($this->table->listableFields);
    }

    public function getAvailableRelationFilters()
    {
        return $this->table->relations->eachNew(
            function(Relation $relation) {
                $entity = $relation->showTable->createEntity();

                $options = $relation->onField && $relation->dynamic_relation_type_id == 1 ? $entity->all()->eachNew(
                    function($record) use ($relation, $entity) {
                        try {
                            $eval = eval(' return ' . $relation->value . '; ');
                        } catch (Throwable $e) {
                            $eval = exception($e);
                        }

                        return [
                            'key'   => $record->id,
                            'value' => $eval,
                        ];
                    },
                    true
                ) : [];

                return [
                    'id'      => $relation->id,
                    'field'   => $relation->id,
                    'table'   => $relation->showTable->table,
                    'fields'  => $this->makeFields($relation->showTable->fields),
                    'type'    => $relation->dynamic_relation_type_id,
                    'options' => [
                        'options' => $options,
                    ],
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
                    if (false && $relation) {
                        $options = $relation->showTable()
                                            ->createEntity()
                                            ->limit(100)
                                            ->all()
                                            ->keyBy($relation->onField->field)
                                            ->map(
                                                function($record) {
                                                    return $record->id;
                                                }
                                            )->toArray();
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
        $filters = $this->getAppliedFilters();

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
        ];

        foreach ($filters as $filter) {
            if (!is_array($filter['value']) && in_array($filter['options']['method'], ['in', 'notIn'])) {
                $filter['value'] = explode(',', $filter['value']);
            }

            $entity->where($filter['field'], $filter['value'], $signMapper[$filter['options']['method']]);
        }

        $signMapper = [
            'equals' => '=',
            'in'     => 'IN',
            'notIn'  => 'NOT IN',
            'not'    => '!=',
        ];

        $relationFilters = $this->getAppliedRelationFilters();
        foreach ($relationFilters as $relationFilter) {
            $relation = (new Relations())->where('id', $relationFilter['id'])->one();

            if ($relation->dynamic_relation_type_id == 1) {
                $entity->where(
                    $relation->onField->field,
                    $relationFilter['value'],
                    $signMapper[$relationFilter['options']['method']]
                );
            } else if ($relation->dynamic_relation_type_id == 2) {
                $entity->join(
                    'INNER JOIN ' . $relation->showTable->table,
                    $relation->onTable->table . '.id = ' . $relation->showTable->table . '.' . $relation->onField->field,
                    $relation->showTable->table . '.' . $relationFilter['field'] . ' ' . $signMapper[$relationFilter['options']['method']] . ' ' . $entity->getRepository(
                    )->getConnection()->quote($relationFilter['value'])
                );
            }

        }
    }

    public function filterByGet($entity)
    {
        if ($search = $this->get->get('search')) {
            $query = clone $entity->getQuery();

            /**
             * This should be applied on all related entities, in separate query.
             * We cannot use this query because not all relations are joined.
             * We cannot use separate query because relations are somehow nod accessible.
             * We need to make join through 2 levels: relations of entity and relations of relations.
             */
            foreach ($entity->getWith() as $with) {
                $with->mergeToQuery($query);
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
            $tables = array_unique($tables);

            $query->select($query->getTable() . '.id');

            $where = new Parenthesis();
            $where->setGlue('OR');
            foreach ($tables as $alias => $table) {
                /**
                 * @T00D00
                 * Here we need to reduce list of filterable fields!
                 */
                foreach ($entity->getRepository()->getCache()->getTableFields($table) as $field) {
                    $where->push($alias . '.' . $field . ' LIKE ?');
                    $query->bind('%' . $search . '%', 'where');
                }
            }
            $query->where($where);

            $newEntity = $this->table->createEntity();
            $newEntity->setQuery($query);
            $newEntity->limit(null);

            $entity->where('id', $newEntity->all()->map('id'));
        }
    }

    public function getTypeMethods()
    {
        return [
            'equals'          => [
                'label' => '=',
            ],
            'greater'         => [
                'label' => '>',
            ],
            'greaterOrEquals' => [
                'label' => '>=',
            ],
            'lower'           => [
                'label' => '<',
            ],
            'lowerOrEquals'   => [
                'label' => '<=',
            ],
            'not'             => [
                'label' => 'NOT',
            ],
            'in'              => [
                'label' => 'IN',
            ],
            'notIn'           => [
                'label' => 'NOT IN',
            ],
            'like'            => [
                'label' => 'LIKE',
            ],
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
