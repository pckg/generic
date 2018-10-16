<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Record\Relation;

class Relations
{

    public function getRelationAction(Relation $relation)
    {
        $with = get('with', []);

        $data = $relation->toArray();

        if (in_array('fields', $with)) {
            $data['fields'] = (new Fields())->where('dynamic_table_id', $relation->show_table_id)->all()->toArray();
        }

        if (in_array('relations', $with)) {
            $data['relations'] = (new \Pckg\Dynamic\Entity\Relations())->where('on_table_id', $relation->show_table_id)->all()->toArray();
        }

        return [
            'relation' => $data,
        ];
    }

}