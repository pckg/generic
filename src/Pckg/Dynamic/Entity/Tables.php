<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\Table;
use Pckg\Maestro\Service\Contract\Entity as MaestroEntity;

class Tables extends DatabaseEntity implements MaestroEntity
{

    protected $record = Table::class;

    protected $table = 'dynamic_tables';

    protected $repositoryName = Repository::class . '.dynamic';

    /**
     * @param callable|null $callback
     *
     * @return HasMany
     */
    public function fields(callable $callback = null)
    {
        return $this->hasMany(Fields::class, $callback)
                    ->foreignKey('dynamic_table_id')/*->fill('fields', 'table')*/
            ;
    }

    public function listableFields()
    {
        return $this->fields(function(HasMany $fields) {
            //$hasMany->joinPermissionTo('view');
            $fields->where('searchable');
        })->fill('searchableFields');
    }

    public function searchableFields()
    {
        return $this->fields(function(HasMany $fields) {
            //$hasMany->joinPermissionTo('view');
        })->fill('listableFields');
    }

    public function actions()
    {
        return $this->hasMany(TableActions::class)
                    ->foreignKey('dynamic_table_id')
                    ->fill('actions', 'table');
    }

    public function belongsToRelation()
    {
        return $this->relations()
                    ->where('dynamic_relation_type_id', 1)
                    ->fill('belongsToRelation');
    }

    public function hasManyRelation()
    {
        return $this->relations()
                    ->where('dynamic_relation_type_id', 2)
                    ->fill('hasManyRelation');
    }

    public function hasAndBelongsToRelation()
    {
        return $this->relations()
                    ->where('dynamic_relation_type_id', 4)
                    ->fill('hasAndBelongsToRelation');
    }

    public function morphsManyRelation()
    {
        return $this->relations()
                    ->where('dynamic_relation_type_id', 6)
                    ->fill('morphsManyRelation');
    }

    public function morphedByRelation()
    {
        return $this->relations()
                    ->where('dynamic_relation_type_id', 5)
                    ->fill('morphedByRelation');
    }

    public function relations()
    {
        return $this->hasMany(Relations::class)
                    ->foreignKey('on_table_id')
                    ->fill('relations');
    }

    public function tabs()
    {
        return $this->hasMany(Tabs::class)
                    ->foreignKey('dynamic_table_id');
    }

    public function functions()
    {
        return $this->hasMany(Functions::class)
                    ->foreignKey('dynamic_table_id');
    }

    public function views()
    {
        return $this->hasMany(TableViews::class)
                    ->foreignKey('dynamic_table_id')
                    ->fill('views');
    }

}