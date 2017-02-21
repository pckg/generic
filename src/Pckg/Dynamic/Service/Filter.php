<?php namespace Pckg\Dynamic\Service;

use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\SphinxQL;
use Pckg\Collection;
use Pckg\CollectionInterface;
use Pckg\Database\Entity;
use Pckg\Database\Query\Parenthesis;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Framework\Request\Data\Get;
use Throwable;

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
                $entity = $relation->showTable->createEntity();

                /**
                 * @T00D00 - load via ajax if possible?
                 *         - optimize related selects like ($relation->value = '$record->order->user->city->title' ;-))
                 */
                $options = $relation->onField && $relation->dynamic_relation_type_id == 1
                    ? $entity->limit(100)
                             ->all()
                             ->map(
                                 function($record
                                 ) use (
                                     $relation,
                                     $entity
                                 ) {
                                     try {
                                         $eval = eval(' return ' . $relation->value . '; ');
                                     } catch (Throwable $e) {
                                         $eval = dev()
                                             ? exception(
                                                 $e
                                             )
                                             : $record->id;
                                     }

                                     return [
                                         'key'   => $record->id,
                                         'value' => $eval,
                                     ];
                                 },
                                 true
                             ) : [];

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
        ];

        foreach ($session['fields']['filters'] ?? [] as $filter) {
            if (!is_array($filter['value']) && in_array($filter['method'], ['in', 'notIn'])) {
                $filter['value'] = explode(',', $filter['value']);
            }

            $entity->where($filter['field'], $filter['value'], $signMapper[$filter['method']]);
        }

        /**
         * @T00D00 - join translations
         */
        foreach ($session['relations']['filters'] ?? [] as $relationFilter) {
            $relation = (new Relations())->where('id', $relationFilter['relation'])->one();

            if ($relation->dynamic_relation_type_id == 1 || !isset($relationFilter['field'])) {
                $entity->where(
                    $relation->onField->field,
                    $relationFilter['value'],
                    $signMapper[$relationFilter['method']]
                );

                if (isset($relationFilter['subfield'])) {
                    $field = Field::getOrFail(['id' => $relationFilter['subfield']]);

                    $f = $relation->showTable->table . '.' . $field->field . ' ' . $signMapper[$relationFilter['method']] . ' ' .
                         $entity->getRepository()->getConnection()->quote($relationFilter['value']);

                    $entity->join(
                        'INNER JOIN ' . $relation->showTable->table,
                        $relation->onTable->table . '.' . $relation->onField->field . ' = ' . $relation->showTable->table . '.id',
                        $f
                    );
                }
            } else if ($relation->dynamic_relation_type_id == 2) {
                $field = Field::getOrFail(['id' => $relationFilter['field']]);

                $f = $relation->showTable->table . '.' . $field->field . ' ' . $signMapper[$relationFilter['method']] . ' ' .
                     $entity->getRepository()->getConnection()->quote($relationFilter['value']);

                $entity->join(
                    'INNER JOIN ' . $relation->showTable->table,
                    $relation->onTable->table . '.id = ' . $relation->showTable->table . '.' . $relation->onField->field,
                    $f
                );
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
            $newEntity->count(false);

            $entity->where('id', $newEntity, 'IN');
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
