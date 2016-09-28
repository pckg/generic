<?php namespace Pckg\Dynamic\Controller;

use Pckg\Collection;
use Pckg\Dynamic\Form\Fields as FieldsForm;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Fields as FieldsService;
use Pckg\Framework\Controller;

class Fields extends Controller
{

    /**
     * @var FieldsService
     */
    protected $fieldsService;

    public function __construct(FieldsService $fieldsService)
    {
        $this->fieldsService = $fieldsService;
    }

    public function getFieldsAction(Table $table, FieldsForm $fieldsForm)
    {
        $fieldsForm->setTable($table);
        $fieldsForm->initFields();

        $this->fieldsService->setTable($table);

        return view(
            'Pckg/Dynamic:fields',
            [
                'table'         => $table,
                'fieldsForm'    => $fieldsForm,
                'fields'        => $this->fieldsService->getAvailableFields(),
                //'relations'        => $this->fieldsService->getAvailableRelations(),
                'appliedFields' => new Collection($this->fieldsService->getAppliedFields()),
                //'appliedRelations' => new Collection($this->fieldsService->getAppliedRelations()),
                'saveFieldsUrl' => $this->fieldsService->getSaveFieldsUrl(),
            ]
        );
    }

    public function postFieldsAction(Table $table, FieldsForm $fieldsForm)
    {
        $fieldsForm->setTable($table);
        $fieldsForm->initFields();

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

    public function postSaveAction(Table $table)
    {
        $filters = $this->post()->get('fields', []);
        //$relationFilters = $this->post()->get('relations', []);

        $_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view']['fields'] = $filters;

        //$_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view']['relations'] = $relationFilters;

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

}