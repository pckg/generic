<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Tables as TablesEntity;
use Pckg\Dynamic\Form\Dynamic as DynamicForm;
use Pckg\Dynamic\Record\Table as TableRecord;
use Pckg\Framework\Controller;
use Pckg\Maestro\Helper\Maestro;
use Pckg\Maestro\Service\Tabelize;

class Tables extends Controller
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