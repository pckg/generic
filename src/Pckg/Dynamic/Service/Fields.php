<?php namespace Pckg\Dynamic\Service;

use Pckg\Collection;
use Pckg\CollectionInterface;
use Pckg\Database\Entity;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Framework\Request\Data\Get;

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
        //return cache('availableFields#' . $this->table->id, function() {
        return $this->makeFields($this->table->listableFields);
        //}, 'app', 60);
    }

    public function getAvailableRelations()
    {
        $sessionRelations = $this->getSession()['relations'] ?? [];

        //return cache('availableRelations', function() use ($sessionRelations) {
        return $this->table->relations->map(
            function(Relation $relation) use ($sessionRelations) {
                $options = $relation->getOptions();

                $filtered = (new Collection($sessionRelations['filters'] ?? []))->filter('relation', $relation->id)
                                                                                ->first();

                return [
                    'id'            => $relation->id,
                    'title'         => $relation->title ?? $relation->showTable->table,
                    'table'         => $relation->showTable->table,
                    'fields'        => $this->makeFields($relation->showTable->fields),
                    'type'          => $relation->dynamic_relation_type_id,
                    'filterOptions' => $options,
                    'visible'       => in_array($relation->id, $sessionRelations['visible'] ?? []),
                    'filterMethod'  => $filtered['method'] ?? null,
                    'filterValue'   => $filtered['value'] ?? null,
                    'filterField'   => $filtered['field'] ?? null,
                ];
            }
        );
        //});
    }

    protected function makeFields(CollectionInterface $fields, $deep = false)
    {
        $sessionFields = $this->getSession()['fields'] ?? [];
        $relations = (new Relations())->where('on_field_id', $fields->map('id'))
                                      ->where('on_table_id', $fields->first()->dynamic_table_id)
                                      ->withOnField()
                                      ->withForeignField()
                                      ->withShowTable(function(BelongsTo $showTable) {
                                          $showTable->joinTranslations();
                                          $showTable->withFields();
                                      })
                                      ->all()
                                      ->keyBy('on_field_id');

        return $fields->map(
            function(Field $field) use ($sessionFields, $deep, $relations) {
                $filtered = (new Collection($sessionFields['filters'] ?? []))->filter('field', $field->id)->first();
                $sorted = (new Collection($sessionFields['sorts'] ?? []))->filter('field', $field->id)->first();
                $options = [];
                $fields = [];

                $relation = $relations[$field->id] ?? null;
                if ($relation) {
                    $options = $relation->getOptions();
                }

                if ($deep && $relation) {
                    $fields = $this->makeFields($relation->showTable->fields);
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
                    'fields'       => $fields,
                ];
            }
        )->keyBy('field');
    }

    public function applyOnEntity(Entity $entity)
    {
        return $this;
    }

}
