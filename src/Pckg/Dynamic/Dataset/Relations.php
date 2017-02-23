<?php namespace Pckg\Dynamic\Dataset;

use Pckg\Dynamic\Entity\Relations as RelationsEntity;
use Pckg\Dynamic\Entity\RelationTypes;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Table;

class Relations
{

    public function getHasManyRelationsTreeForTable(Table $table, &$distinctRelations)
    {
        $relations = (new RelationsEntity())->where('on_table_id', $table->id)
                                            ->where('dynamic_relation_type_id', RelationTypes::TYPE_HAS_MANY)
                                            ->where('id', $distinctRelations, 'NOT IN')
                                            ->joinTranslations()
                                            ->withShowTable()
                                            ->all();

        $distinctRelations = array_unique($distinctRelations + $relations->map('id')->all());

        $relations->each(
            function(Relation $relation) use (&$distinctRelations) {
                $relation->hasManyRelations = $this->getHasManyRelationsTreeForTable(
                    $relation->showTable,
                    $distinctRelations
                );
            }
        );

        return $relations;
    }

}