<?php namespace Pckg\Dynamic\Service;

use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableView;

abstract class AbstractService
{

    protected $table;

    protected $view;

    public function setTable(Table $table)
    {
        $this->table = $table;
    }

    public function setView(TableView $view)
    {
        $this->view = $view;
    }

    public function getSession($key = null)
    {
        $session = $_SESSION['pckg']['dynamic']['view']['table_' . $this->table->id . '_' . ($this->view->id ?? '')] ?? [];

        if (!$key) {
            return $session;
        }

        return $session[$key] ?? [];
    }

}