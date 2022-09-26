<?php

namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\TableView;

class TableViews extends DatabaseEntity
{

    protected $table = 'dynamic_table_views';

    protected $record = TableView::class;

    public function boot()
    {
        $this->joinTranslations();

        return $this;
    }

    public function table()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('dynamic_table_id')
                    ->fill('table');
    }
}
