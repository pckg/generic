<?php namespace Pckg\Dynamic\Service;

use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Table;

class Sort
{

    protected $table;

    public function setTable(Table $table)
    {
        $this->table = $table;
    }

    public function getSaveSortUrl()
    {
        return url(
            'dynamic.record.sort.save',
            [
                'table' => $this->table,
            ]
        );
    }

    public function getAppliedSorts()
    {
        return $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id]['view']['sort'] ?? [];
    }

    public function getAvailableSorts()
    {
        return $this->table->listableFields->each(
            function(Field $field) {
                return [
                    'field'   => $field->field,
                    'label'   => $field->title ?? $field->field,
                    'type'    => $field->fieldType->slug,
                    'options' => [],
                ];
            },
            true
        )->keyBy('field');
    }

    public function applyOnEntity(Entity $entity)
    {
        if (get('field') && get('dir')) {
            $field = $this->table->listableFields->first(
                function(Field $field) {
                    return $field->id == get('field');
                }
            );

            $directionMapper = [
                'up'   => 'ASC',
                'down' => 'DESC',
            ];

            if ($field) {
                $entity->orderBy(
                    '`' . $entity->getTable() . '`.`' . $field->field . '` ' . ($directionMapper[get('dir')] ?? 'DESC')
                );

                return;
            }
        }
        $sorts = $this->getAppliedSorts();

        if (!$sorts) {
            $this->applyGuessedSorts($entity);

            return;
        }

        $directionMapper = [
            'ascending'  => 'ASC',
            'descending' => 'DESC',
        ];

        foreach ($sorts as $sort) {
            $entity->orderBy(
                $sort['field'] . ' ' . ($directionMapper[$sort['options']['direction'] ?? 'descending'] ?? 'DESC')
            );
        }
    }

    protected function applyGuessedSorts(Entity $entity)
    {
        if (!$entity->getQuery()->getOrderBy()) {
            $cache = $entity->getRepository()->getCache();
            if ($cache->tableHasField($entity->getTable(), 'order')) {
                $entity->orderBy('`' . $entity->getTable() . '`.`order` ASC');

            } else if ($cache->tableHasField($entity->getTable(), 'ord')) {
                $entity->orderBy('`' . $entity->getTable() . '`.`ord` ASC');

            } else if ($cache->tableHasField($entity->getTable(), 'position')) {
                $entity->orderBy('`' . $entity->getTable() . '`.`position` ASC');

            } else if ($cache->tableHasField($entity->getTable(), 'dt_published')) {
                $entity->orderBy(
                    'IF(`' . $entity->getTable(
                    ) . '`.`dt_published` BETWEEN \'0000-00-00 00:00:01\' AND NOW() , 1, 0) DESC, id ASC'
                );

            } else {
                $entity->orderBy('`' . $entity->getTable() . '`.`id` DESC');

            }
        }
    }

    public function getDirections()
    {
        return [
            'descending' => [
                'label' => 'Descending',
            ],
            'ascending'  => [
                'label' => 'Ascending',
            ],
        ];
    }

}