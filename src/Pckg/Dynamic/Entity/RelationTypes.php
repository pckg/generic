<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\RelationType;

class RelationTypes extends Entity
{

    use Translatable;

    protected $record = RelationType::class;

    protected $table = 'dynamic_relation_types';

    public function relations()
    {
        return $this->hasMany(Relations::class)
                    ->foreignKey('relation_type_id');
    }

}