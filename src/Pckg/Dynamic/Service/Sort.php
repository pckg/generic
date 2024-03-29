<?php

namespace Pckg\Dynamic\Service;

use Pckg\Database\Entity;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Record\Field;

class Sort extends AbstractService
{
    public function getSaveSortUrl()
    {
        return url('dynamic.record.sort.save', [
                                                 'table' => $this->table,
                                             ]);
    }

    public function getAppliedSorts()
    {
        return $this->getSession('sort');
    }

    public function getAvailableSorts()
    {
        return $this->table->listableFields->map(function (Field $field) {
            return [
                'field'   => $field->field,
                'label'   => $field->title ?? $field->field,
                'type'    => $field->fieldType->slug,
                'options' => [],
            ];
        })->keyBy('field');
    }

    public function applyOnEntity(Entity $entity, $paginator = [])
    {
        if (!$this->table) {
            return;
        }

        $field = (new Fields())->withFieldType()
                               ->where('field', $paginator['sort'] ?? 'id')
                               ->where('dynamic_table_id', $this->table->id)
                               ->one();

        if (!$field) {
            return;
        }

        $directionMapper = [
            'up'   => 'ASC',
            'down' => 'DESC',
            'asc'   => 'ASC',
            'desc' => 'DESC',
        ];

        if ($field) {
            if (!in_array($field->fieldType->slug, ['mysql'])) {
                $table = $field->field == 'id'
                    ? $entity->getTable()
                    : $entity->getRepository()
                             ->getCache()
                             ->getExtendeeTableForField($entity->getTable(), $field->field);

                $entity->orderBy('`' . ($table ?? $entity->getTable()) . '`.`' . $field->field . '` ' .
                                 ($directionMapper[$paginator['dir'] ?? 'down'] ?? 'DESC'));
            } else {
                $entity->orderBy('`' . $field->field . '` ' . ($directionMapper[$paginator['dir']] ?? 'DESC'));
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
