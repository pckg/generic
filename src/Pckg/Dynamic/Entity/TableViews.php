<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\TableView;

class TableViews extends DatabaseEntity
{

    use Translatable;

    protected $table = 'dynamic_table_views';

    protected $record = TableView::class;

    public function boot()
    {
        $this->joinTranslations();
    }

    public function table()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('dynamic_table_id')
                    ->fill('table');
    }

}