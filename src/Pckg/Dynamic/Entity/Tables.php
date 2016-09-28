<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Paginatable;
use Pckg\Database\Entity\Extension\Permissionable;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Record\Table;
use Pckg\Maestro\Service\Contract\Entity as MaestroEntity;

class Tables extends Entity implements MaestroEntity
{

    use Paginatable, Translatable, Permissionable;

    protected $record = Table::class;

    protected $table = 'dynamic_tables';

    public function fields()
    {
        return $this->hasMany(Fields::class)
                    ->foreignKey('dynamic_table_id')
                    ->fill('fields', 'table');
    }

    public function listableFields()
    {
        return $this->hasMany(
            Fields::class,
            function(HasMany $hasMany) {
                //$hasMany->joinPermissionTo('view');
            }
        )
                    ->foreignKey('dynamic_table_id')
                    ->fill('listableFields', 'table');
    }

    public function actions()
    {
        return $this->hasMany(TableActions::class)
                    ->foreignKey('dynamic_table_id')
                    ->fill('actions', 'table');
    }

    public function getAddUrl()
    {
        return url('dynamic.table.add');
    }

    public function isTranslatable()
    {
        return $this->getRepository()->getCache()->hasTable($this->table . $this->translatableTableSuffix);
    }

    public function isPermissionable()
    {
        return $this->getRepository()->getCache()->hasTable($this->table . $this->permissionableTableSuffix);
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