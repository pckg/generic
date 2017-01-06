<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\Tab;

class Tabs extends DatabaseEntity
{

    use Translatable;

    protected $table = 'dynamic_table_tabs';

    protected $record = Tab::class;

    protected $repositoryName = Repository::class . '.dynamic';

    public function table()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('dynamic_table_id');
    }

}