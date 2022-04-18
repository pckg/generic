<?php

namespace Pckg\Maestro\Service\Tabelize;

use Pckg\Dynamic\Dataset\Relations;
use Pckg\Maestro\Service\Tabelize;

class Delete
{
    public function getListAction(Tabelize $tabelize)
    {
        /**
         * Get table for which we need to determine relations tree.
         */
        $table = $tabelize->getTable();

        /**
         * Get relations tree.
         */
        $distinctRelations = [];
        $relations = [];//(new Relations())->getHasManyRelationsTreeForTable($table, $distinctRelations);

        /**
         * Render view.
         */
        return view(
            'tabelize/listActions/delete',
            [
                'relations' => $relations,
            ]
        );
    }
}
