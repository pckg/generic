<?php namespace Pckg\Dynamic\Dataset;

use Derive\Orders\Record\Order;
use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Table;

class Fields
{

    public function getListableFieldsForTable(Table $table)
    {
        return $table->listableFields(
            function(HasMany $relation) {
                $relation->withFieldType();
            }
        );
    }

    public function getFieldsTransformations(Collection $fields, Entity $entity)
    {
        $fieldTransformations = [];

        /**
         * Transform field type = php, geo
         * Add support for point fields.
         */
        $fields->each(
            function(Field $field) use (&$fieldTransformations, $entity) {
                $transformation = $field->getTransformedValue($entity);

                if ($transformation) {
                    $fieldTransformations[$field->field] = $transformation;
                }
            }
        );

        return $fieldTransformations;
    }

}