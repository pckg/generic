<?php namespace Pckg\Dynamic\Service;

use Pckg\Collection;
use Pckg\CollectionInterface;
use Pckg\Database\Entity;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Framework\Request\Data\Get;
use Throwable;

class Fields extends AbstractService
{

    protected $get;

    public function __construct(Get $get)
    {
        $this->get = $get;
    }

    public function getSaveFieldsUrl()
    {
        return url(
            'dynamic.record.fields.save',
            [
                'table' => $this->table,
            ]
        );
    }

    public function getAppliedFields()
    {
        return $this->getSession('fields');
    }

    public function getAppliedRelations()
    {
        return $this->getSession('relations');
    }

    public function getAvailableFields()
    {
        return $this->makeFields($this->table->listableFields);
    }

    public function getAvailableRelations()
    {
        $field = $this;
        $sessionRelations = $this->getSession()['relations'] ?? [];

        return $this->table->relations->map(
            function(Relation $relation) use ($field, $sessionRelations) {
                $options = $relation->getOptions();

                $filtered = (new Collection($sessionRelations['filters'] ?? []))->filter('relation', $relation->id)->first();

                return [
                    'id'            => $relation->id,
                    'title'         => $relation->title,
                    'table'         => $relation->showTable->table,
                    'fields'        => $this->makeFields(
                        $relation->showTable->fields
                    ),
                    'type'          => $relation->dynamic_relation_type_id,
                    'options'       => [
                        'options' => $options,
                    ],
                    'filterOptions' => $options,
                    'visible'       => in_array($relation->id, $sessionRelations['visible'] ?? []),
                    'filterMethod'  => $filtered ? $filtered['method'] : null,
                    'filterValue'   => $filtered ? $filtered['value'] : null,
                    'filterField'   => $filtered ? $filtered['field'] : null,
                ];
            }
        );
    }

    protected function makeFields(CollectionInterface $collection)
    {
        $sessionFields = $this->getSession()['fields'] ?? [];

        return $collection->map(
            function(Field $field) use ($sessionFields) {
                $filtered = (new Collection($sessionFields['filters'] ?? []))->filter('field', $field->id)->first();
                $sorted = (new Collection($sessionFields['sorts'] ?? []))->filter('field', $field->id)->first();
                $options = [];

                $relation = (new Relations())->where('on_field_id', $field->id)->one();
                if ($relation) {
                    $options = $relation->getOptions();
                }

                return [
                    'id'           => $field->id,
                    'field'        => $field->field,
                    'type'         => $field->fieldType->slug,
                    'title'        => $field->title ?? $field->field,
                    'visible'      => in_array($field->id, $sessionFields['visible'] ?? []),
                    'filterMethod' => $filtered['method'] ?? null,
                    'filterValue'  => $filtered['value'] ?? null,
                    'sort'         => $sorted['direction'] ?? null,
                    'options'      => $options,
                ];
            }
        )->keyBy('field');
    }

    public function applyOnEntity(Entity $entity)
    {
        return $this;
    }

}
