<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\Relation;

class Relations extends DatabaseEntity
{

    use Translatable;

    protected $record = Relation::class;

    protected $table = 'dynamic_relations';

    public function type()
    {
        return $this->belongsTo(RelationTypes::class)
                    ->foreignKey('relation_type_id')
                    ->fill('type');
    }

    public function onTable()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('on_table_id')
                    ->fill('onTable');
    }

    public function onField()
    {
        return $this->belongsTo(Fields::class)
                    ->foreignKey('on_field_id')
                    ->fill('onField');
    }

    public function showTable()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('show_table_id')
                    ->fill('showTable');
    }

}