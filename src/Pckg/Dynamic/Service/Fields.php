<?php namespace Pckg\Dynamic\Service;

use Pckg\Collection;
use Pckg\CollectionInterface;
use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Table;
use Pckg\Framework\Request\Data\Get;

class Fields
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
        return $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id]['view']['fields'] ?? [];
    }

    public function getAppliedRelations()
    {
        return $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id]['view']['relations'] ?? [];
    }

    public function getAvailableFields()
    {
        return $this->makeFields($this->table->listableFields);
    }

    public function getAvailableRelations()
    {
        return $this->table->relations->each(
            function(Relation $relation) {
                $entity = $relation->showTable->createEntity();

                $options = $relation->onField && $relation->dynamic_relation_type_id == 1 ? $entity->all()->each(
                    function($record) use ($relation, $entity) {
                        try {
                            $eval = eval(' return ' . $relation->value . '; ');
                        } catch (\Exception $e) {
                            dd(exception($e));
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
                return [
                    'field'   => $field->field,
                    'label'   => $field->title ?? $field->field,
                    'applied' => in_array(
                        $field->field,
                        $this->getAppliedFields()
                    ),
                ];
            }
        )->keyBy('field');
    }

    public function applyOnEntity(Entity $entity)
    {
        return $this;
        $fields = $this->getAppliedFields();
        $relations = $this->getAppliedRelations();
    }

}