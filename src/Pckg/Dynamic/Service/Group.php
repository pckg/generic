<?php namespace Pckg\Dynamic\Service;

use Pckg\CollectionInterface;
use Pckg\Database\Collection;
use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Table;

class Group
{

    protected $table;

    public function setTable(Table $table)
    {
        $this->table = $table;
    }

    public function getSaveGroupUrl()
    {
        return url(
            'dynamic.record.group.save',
            [
                'table' => $this->table,
            ]
        );
    }

    public function getAppliedGroups()
    {
        return $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id]['view']['group'] ?? [];
    }

    public function getAvailableGroups()
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
        $groups = $this->getAppliedGroups();

        foreach ($groups as $group) {
            if (($group['type'] ?? null) == 'db') {
                $entity->groupBy($group['field']);
            }
        }
    }

    public function applyOnCollection(CollectionInterface $collection)
    {
        $newCollection = null;
        foreach ($this->getAppliedGroups() as $group) {
            if ($group['type'] == 'db') {
                continue;
            }

            if (!$newCollection) {
                $newCollection = $collection->groupBy(
                    function($item) use ($group) {
                        return $item->{$group['field']};
                    }
                );
            } else {
                $newCollection = $newCollection->each(
                    function($groupItems) use ($group) {
                        return (new Collection($groupItems))->groupBy(
                            function($item) use ($group) {
                                return $item->{$group['field']};
                            }
                        );
                    },
                    true
                );
            }
        }

        return $newCollection ?? $collection;
    }

    public function getTypeMethods()
    {
        return [];
    }

}