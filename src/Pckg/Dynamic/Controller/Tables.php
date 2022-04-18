<?php

namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Tables as TablesEntity;
use Pckg\Maestro\Helper\Maestro;
use Pckg\Maestro\Service\Tabelize;

class Tables
{
    use Maestro;

/**
     * List all installed tables
     *
     * @param TablesEntity $tables
     *
     * @return Tabelize
     */


    public function getIndexAction(TablesEntity $tables)
    {
        return $this->tabelize($tables, ['table'], 'Installed tables')
                    ->setRecordActions(['view']);
    }
}
