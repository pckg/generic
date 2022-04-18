<?php

namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\Relation;

class Relations extends DatabaseEntity
{
    protected $record = Relation::class;

    protected $table = 'dynamic_relations';

    protected $repositoryName = Repository::class . '.dynamic';

    public function type()
    {
        return $this->belongsTo(RelationTypes::class)
                    ->foreignKey('dynamic_relation_type_id');
    }

    public function onTable()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('on_table_id');
    }

    public function onField()
    {
        return $this->belongsTo(Fields::class)
                    ->foreignKey('on_field_id');
    }

    public function showTable()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('show_table_id');
    }

    public function foreignField()
    {
        return $this->belongsTo(Fields::class)
                    ->foreignKey('foreign_field_id');
    }

    public function overTable()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('over_table_id');
    }

    public function leftForeignKey()
    {
        return $this->belongsTo(Fields::class)
                    ->foreignKey('left_foreign_key_id');
    }
}
