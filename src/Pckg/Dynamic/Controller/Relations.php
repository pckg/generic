<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Record\Relation;

class Relations
{

    public function getRelationAction(Relation $relation)
    {
        $with = get('with', []);

        if (in_array('fields', $with)) {
            $fields = (new Fields())->where('dynamic_table_id', $relation->show_table_id)->all();
            $relation->set('fields', $fields);
            $relation->addToArray('fields');
        }

        if (in_array('relations', $with)) {
            $relations = (new \Pckg\Dynamic\Entity\Relations())->where('on_table_id', $relation->show_table_id)->all();
            $relation->set('relations', $relations);
            $relation->addToArray('relations');
        }

        return [
            'relation' => $relation,
        ];
    }

}