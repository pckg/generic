<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\RelationType;

class RelationTypes extends DatabaseEntity
{

    use Translatable;

    protected $record = RelationType::class;

    protected $table = 'dynamic_relation_types';

    protected $repositoryName = Repository::class . '.dynamic';

    public function relations()
    {
        return $this->hasMany(Relations::class)
                    ->foreignKey('relation_type_id');
    }

}