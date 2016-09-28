<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\Tab;

class Tabs extends Entity
{

    use Translatable;

    protected $table = 'dynamic_table_tabs';

    protected $record = Tab::class;

    public function table()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('dynamic_table_id');
    }

}