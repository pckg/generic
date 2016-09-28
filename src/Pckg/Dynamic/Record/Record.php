<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Record\Snippet\RecordActions;
use Pckg\Maestro\Service\Contract\Record as MaestroRecord;

class Record extends DatabaseRecord implements MaestroRecord
{

    use RecordActions;

    protected $entity = Entity::class;

    public function getRelationForSelect(Table $table, Field $field)
    {
        /**
         * So, $table is users table, $field is user_group_id for which we need to get relation.
         * We need to create entity user_groups (which is found on relation)
         * and select all records.
         */
        $relation = $field->getSelectRelation($table);

        if (!$relation) {
            return $relation;
        }

        $showTable = $relation->showTable;
        $entity = $showTable->createEntity();

        $values = [];
        $entity->all()->each(
            function(Record $record) use ($relation, &$values) {
                try {
                    $eval = eval(' return ' . $relation->value . '; ');
                } catch (\Exception $e) {
                    dd(exception($e));
                }

                $values[$record->id] = $eval;
            }
        );

        return $values;
    }

}