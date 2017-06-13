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

    public function getTableViewUrlAttribute()
    {
        return url(
            'dynamic.record.listView',
            [
                'table'     => $this->table,
                'tableView' => $this,
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
        $_SESSION['pckg']['dynamic']['view']['table_' . $this->dynamic_table_id . '_' .
                                             $this->id]['view'] = json_decode(
            $this->settings,
            true
        );
    }

    public function loadToSessionIfNotLoaded()
    {
        if (!isset($_SESSION['pckg']['dynamic']['view']['table_' . $this->dynamic_table_id . '_' .
                                                        $this->id]['view'])
        ) {
            d('loading');
            $this->loadToSession();
            dd('loaded');
        }
    }

    /**
     * Create view based on session data:
     *  - filters
     *  - groups
     *  - orders
     */
    public function loadFromSession()
    {
        $this->settings = json_encode(
            $_SESSION['pckg']['dynamic']['view']['table_' . $this->dynamic_table_id . '_' . $this->id]['view'] ?? []
        );

        $this->save();
    }

}