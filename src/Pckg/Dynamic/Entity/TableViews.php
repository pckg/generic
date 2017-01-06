<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\TableView;

class TableViews extends DatabaseEntity
{

    use Translatable;

    protected $table = 'dynamic_table_views';

    protected $record = TableView::class;

    protected $repositoryName = Repository::class . '.dynamic';

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