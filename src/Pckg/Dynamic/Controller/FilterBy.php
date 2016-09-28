<?php namespace Pckg\Dynamic\Controller;

use Pckg\Collection;
use Pckg\Dynamic\Form\Filter;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Filter as FilterService;
use Pckg\Framework\Controller;

class FilterBy extends Controller
{

    /**
     * @var FilterService
     */
    protected $filterService;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function getFilterTableAction(Table $table, Filter $filterForm)
    {
        $filterForm->setTable($table);
        $filterForm->initFields();

        $this->filterService->setTable($table);

        return view(
            'Pckg/Dynamic:filter',
            [
                'table'                  => $table,
                'filterForm'             => $filterForm,
                'request'                => $this->request(),
                'filters'                => $this->filterService->getAvailableFilters(),
                'relationFilters'        => $this->filterService->getAvailableRelationFilters(),
                'appliedFilters'         => new Collection($this->filterService->getAppliedFilters()),
                'appliedRelationFilters' => new Collection($this->filterService->getAppliedRelationFilters()),
                'typeMethods'            => $this->filterService->getTypeMethods(),
                'relationMethods'        => $this->filterService->getRelationMethods(),
                'saveFilterUrl'          => $this->filterService->getSaveFilterUrl(),
            ]
        );
    }

    public function postFilterTableAction(Table $table, Filter $filterForm)
    {
        $filterForm->setTable($table);
        $filterForm->initFields();

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

    public function postSaveAction(Table $table)
    {
        $filters = $this->post()->get('filters', []);
        $relationFilters = $this->post()->get('relationFilters', []);

        $_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view']['filter'] = $filters;
        $_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view']['relationFilter'] = $relationFilters;

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

}