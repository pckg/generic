<?php

namespace Pckg\Generic\Migration;

use Pckg\Auth\Migration\CreateAuthTables;
use Pckg\Migration\Migration;

class CreateGenericTablesRequirements extends Migration
{
    /**
     * Those packets need to be installed before this migration.
     */
    public function dependencies()
    {
        return [
            // translatable, permissionable
            CreateLanguagesTable::class,
            CreateMenuTables::class,
            CreateListTables::class,
            CreateDataAttributesTables::class,
        ];
    }

    public function partials()
    {
        return [
            (new CreateSettingsTable())->setRepository($this->repository),
            (new CreateAuthTables())->setRepository($this->repository),
        ];
    }
}
