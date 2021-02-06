<?php

namespace Pckg\Dynamic\Service;

use Pckg\CollectionInterface;
use Pckg\Database\Collection;
use Pckg\Database\Entity;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Record\Field;

class Group extends AbstractService
{

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
        return $this->getSession('fields')['groups'] ?? [];
    }

    public function getAvailableGroups()
    {
        return $this->table->listableFields->each(
            function (Field $field) {
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
            if (isset($group['field'])) {
                $field = (new Fields())->withTable()->where('id', $group['field'])->one();
                $entity->addGroupBy($field->table->table . '.' . $field->field);
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
                    function ($item) use ($group) {
                        return $item->{$group['field']};
                    }
                );
            } else {
                $newCollection = $newCollection->each(
                    function ($groupItems) use ($group) {
                        return (new Collection($groupItems))->groupBy(
                            function ($item) use ($group) {
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
