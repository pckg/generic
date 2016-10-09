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
        $sorts = $this->getAppliedSorts();

        $directionMapper = [
            'ascending'  => 'ASC',
            'descending' => 'DESC',
        ];

        foreach ($sorts as $sort) {
            $entity->orderBy($sort['field'] . ' ' . ($directionMapper[$sort['options']['direction'] ?? 'descending'] ?? 'DESC'));
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