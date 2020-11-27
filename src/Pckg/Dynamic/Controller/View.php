<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\TableViews;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableView;
use Pckg\Framework\Controller;

class View extends Controller
{

    public function getShareViewAction(Table $table)
    {
        /**
         * @T00D00 - fix json encode
         */
        return url(
                   'dynamic.record.view.share',
                   [
                       'table' => $table,
                   ],
                   true
               ) . '?data=' . base64_encode(
                   json_encode($_SESSION['pckg']['dynamic']['view']['table_' . $table->id]['view'] ?? [])
               );
    }

    public function getSaveViewAction(Table $table, TableView $tableView = null)
    {
        /*vueManager()->addView('Pckg/Dynamic:view/_save', [
            'savedViews'         => (new TableViews)->where('dynamic_table_id', $table->id)->all(),
            'saveCurrentViewUrl' => url(
                $tableView
                    ? 'dynamic.record.view.savePlusView'
                    : 'dynamic.record.view.save',
                [
                    'table'     => $table,
                    'tableView' => $tableView,
                ]
            ),
        ]);*/

        return view('Pckg/Dynamic:view/save', ['view' => $tableView]);
    }

    public function postSaveViewAction(Table $table)
    {
        if ($id = $this->post()->get('id')) {
            $view = (new TableViews())->where('id', $id)->oneOrFail();
            $view->loadFromSession(post('sessionView', null));
        } else {
            $view = new TableView(
                [
                    'dynamic_table_id' => $table->id,
                    'title'            => $this->post()->get('name'),
                ]
            );
            $view->loadFromSession(post('sessionView', null));
        }

        $view->save();

        return $this->response()->respondWithAjaxSuccessAndRedirectBack();
    }

    public function getResetViewAction(Table $table)
    {
        $_SESSION['pckg']['dynamic']['view']['table_' . $table->id . '_']['view'] = [];

        return $this->response()->redirect(
            url(
                'dynamic.record.list',
                [
                    'table' => $table,
                ]
            )
        );
    }

    public function getLoadViewAction(TableView $view)
    {
        $view->loadToSession();

        return $this->response()->redirect(
            $this->server('HTTP_REFERER') ? -1 : url(
                'dynamic.record.list',
                [
                    'table' => $view->table,
                ]
            )
        );
    }

}