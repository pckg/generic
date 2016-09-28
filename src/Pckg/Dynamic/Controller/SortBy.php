<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Form\Sort;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Sort as SortService;
use Pckg\Framework\Controller;

class SortBy extends Controller
{

    /**
     * @var Sort
     */
    protected $sortService;

    public function __construct(SortService $sortService)
    {
        $this->sortService = $sortService;
    }

    public function getSortTableAction(Table $table, Sort $sortForm)
    {
        $sortForm->setTable($table);
        $sortForm->initFields();

        $this->sortService->setTable($table);

        return view(
            'Pckg/Dynamic:sort',
            [
                'table'        => $table,
                'sortForm'     => $sortForm,
                'request'      => $this->request(),
                'sorts'        => $this->sortService->getAvailableSorts(),
                'appliedSorts' => $this->sortService->getAppliedSorts(),
                'directions'   => $this->sortService->getDirections(),
                'saveSortUrl'  => $this->sortService->getSaveSortUrl(),
            ]
        );
    }

    public function postSortTableAction(Table $table, Sort $sortForm)
    {
        $sortForm->setTable($table);
        $sortForm->initFields();

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

    public function postSaveAction(Table $table)
    {
        $sorts = $this->post()->get('sorts', []);

        $_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view']['sort'] = $sorts;

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

}