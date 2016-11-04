<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\TableViews;

class TableView extends DatabaseRecord
{

    protected $entity = TableViews::class;

    public function getLoadUrl()
    {
        return url(
            'dynamic.record.view.load',
            [
                'tableView' => $this,
                'table'     => $this->table,
            ]
        );
    }

    /**
     * Load settings from database into session:
     *  - filters
     *  - groups
     *  - orders
     */
    public function loadToSession()
    {
        $_SESSION['pckg']['dynamic']['view']['table_' . $this->dynamic_table_id]['view'] = json_decode(
            $this->settings,
            true
        );
    }

    /**
     * Create view based on session data:
     *  - filters
     *  - groups
     *  - orders
     */
    public function loadFromSession()
    {
        $this->settings = json_encode($_SESSION['pckg']['dynamic']['view']['table_' . $this->dynamic_table_id]['view'] ?? []);

        $this->save();
    }

}