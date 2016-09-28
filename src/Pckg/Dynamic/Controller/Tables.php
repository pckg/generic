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

    public function getAddTableAction(DynamicForm $dynamicForm, TableRecord $tableRecord)
    {
        $dynamicForm->populateFromRecord($tableRecord);

        return $this->formalize($dynamicForm, $tableRecord, 'Add dynamic table');
    }

    public function postAddTableAction(DynamicForm $dynamicForm, TableRecord $tableRecord)
    {
        $dynamicForm->populateToRecord($tableRecord);

        $tableRecord->save();

        return $this->response()->respondWithSuccessRedirect($tableRecord->getEditUrl());
    }

    public function getEditTableAction(DynamicForm $dynamicForm, TableRecord $tableRecord)
    {
        $dynamicForm->populateFromRecord($tableRecord);

        return $this->formalize($dynamicForm, $tableRecord, 'Edit dynamic table');
    }

}