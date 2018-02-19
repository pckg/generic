<?php namespace Pckg\Dynamic\Service;

use Pckg\Database\Entity;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Record\Field;

class Sort extends AbstractService
{

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
        return $this->getSession('sort');
    }

    public function getAvailableSorts()
    {
        return $this->table->listableFields->map(
            function(Field $field) {
                return [
                    'field'   => $field->field,
                    'label'   => $field->title ?? $field->field,
                    'type'    => $field->fieldType->slug,
                    'options' => [],
                ];
            }
        )->keyBy('field');
    }

    public function applyOnEntity(Entity $entity)
    {
        if (get('field') && get('dir')) {
            $field = (new Fields())->withFieldType()->where('id', get('field'))->oneOrFail();

            $directionMapper = [
                'up'   => 'ASC',
                'down' => 'DESC',
            ];

            if ($field) {
                if (!in_array($field->fieldType->slug, ['mysql'])) {
                    $table = $field->field == 'id'
                        ? $entity->getTable()
                        : $entity->getRepository()
                                 ->getCache()
                                 ->getExtendeeTableForField($entity->getTable(), $field->field);

                    $entity->orderBy(
                        '`' . ($table ?? $entity->getTable()) . '`.`' . $field->field . '` ' .
                        ($directionMapper[get('dir')] ?? 'DESC')
                    );
                } else {
                    $entity->orderBy(
                        '`' . $field->field . '` ' .
                        ($directionMapper[get('dir')] ?? 'DESC')
                    );
                }
            }
        }

        $session = $this->getSession();
        $sorts = $session['fields']['sorts'] ?? [];

        if (!$sorts) {
            $this->applyGuessedSorts($entity);

            return;
        }

        $directionMapper = [
            'ascending'  => 'ASC',
            'descending' => 'DESC',
        ];

        foreach ($sorts as $sort) {
            $field = Field::getOrFail(['id' => $sort['field']]);

            $table = $field->field == 'id'
                ? $entity->getTable()
                : $entity->getRepository()
                         ->getCache()
                         ->getExtendeeTableForField($entity->getTable(), $field->field);
            $entity->orderBy(
                ($table ?? $entity->getTable()) . '.' . $field->field . ' ' .
                ($directionMapper[$sort['direction'] ?? 'descending'] ?? 'DESC')
            );
        }
    }

    protected function applyGuessedSorts(Entity $entity)
    {
        if (!$entity->getQuery()->getOrderBy()) {
            $cache = $entity->getRepository()->getCache();
            if ($this->table && $this->table->order) {
                $entity->orderBy($this->table->order);
            } else if ($cache->tableHasField($entity->getTable(), 'order')) {
                $entity->orderBy('`' . $entity->getTable() . '`.`order` ASC');
            } else if ($cache->tableHasField($entity->getTable(), 'ord')) {
                $entity->orderBy('`' . $entity->getTable() . '`.`ord` ASC');
            } else if ($cache->tableHasField($entity->getTable(), 'position')) {
                $entity->orderBy('`' . $entity->getTable() . '`.`position` ASC');
            } else if ($cache->tableHasField($entity->getTable(), 'dt_published')) {
                $entity->orderBy(
                    'IF(`' . $entity->getTable() .
                    '`.`dt_published` BETWEEN \'0000-00-00 00:00:01\' AND NOW() , 1, 0) DESC, `' . $entity->getTable() .
                    '`.id ASC'
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