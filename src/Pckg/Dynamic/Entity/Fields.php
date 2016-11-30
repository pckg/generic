<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Orderable;
use Pckg\Database\Entity\Extension\Permissionable;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\Field;
use Pckg\Generic\Entity\Settings;
use Pckg\Generic\Entity\SettingsMorphs;

class Fields extends DatabaseEntity
{

    use Translatable, Orderable, Permissionable;

    protected $record = Field::class;

    protected $table = 'dynamic_fields';

    /**
     * @return \Pckg\Database\Relation\BelongsTo
     */
    public function table()
    {
        return $this->belongsTo(Tables::class)
                    ->foreignKey('dynamic_table_id');
    }

    public function fieldType()
    {
        return $this->belongsTo(FieldTypes::class)
                    ->foreignKey('dynamic_field_type_id');
    }

    public function fieldGroup()
    {
        return $this->belongsTo(FieldGroups::class)
                    ->foreignKey('dynamic_field_group_id');
    }

    public function settings()
    {
        return $this->morphsMany(Settings::class)
                    ->over(SettingsMorphs::class)
                    ->rightForeignKey('setting_id');
    }

    /**
     * Show relation on listing.
     */
    public function hasOneSelectRelation()
    {
        return $this->hasOne(Relations::class)
                    ->foreignKey('on_field_id')
                    ->where('dynamic_relation_type_id', 1);
    }

}