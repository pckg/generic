<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Form\Group;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Group as GroupService;
use Pckg\Framework\Controller;

class GroupBy extends Controller
{

    /**
     * @var GroupService
     */
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function getGroupTableAction(Table $table, Group $groupForm)
    {
        $groupForm->setTable($table);
        $groupForm->initFields();

        $this->groupService->setTable($table);

        return view(
            'Pckg/Dynamic:group',
            [
                'table'         => $table,
                'groupForm'     => $groupForm,
                'request'       => $this->request(),
                'groups'        => $this->groupService->getAvailableGroups(),
                'appliedGroups' => $this->groupService->getAppliedGroups(),
                'typeMethods'   => $this->groupService->getTypeMethods(),
                'saveGroupUrl'  => $this->groupService->getSaveGroupUrl(),
            ]
        );
    }

    public function postGroupTableAction(Table $table, Group $groupForm)
    {
        $groupForm->setTable($table);
        $groupForm->initFields();

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

    public function postSaveAction(Table $table)
    {
        $groups = $this->post()->get('groups', []);

        $_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view']['group'] = $groups;

        return $this->response()->respondWithSuccessOrRedirect($table->getViewUrl());
    }

}