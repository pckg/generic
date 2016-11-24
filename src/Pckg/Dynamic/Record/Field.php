<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Record\Extension\Permissionable;
use Pckg\Dynamic\Entity\Fields;
use Throwable;

class Field extends DatabaseRecord
{

    use Permissionable;

    protected $entity = Fields::class;

    protected $toArray = [
        'fieldType',
        'isTogglable',
        'minTogglable',
        'maxTogglable',
    ];

    /**
     * @param Table $onTable
     *
     * @return Relation
     */
    /*public function getSelectRelation(Table $onTable)
    {
        $self = $this;
        $relation = $this->relationExists('hasOneSelectRelation')
            ? $this->getRelation('hasOneSelectRelation')
            : $this->hasOneSelectRelation(
                function(HasOne $relation) use ($onTable, $self) {
                    $relation->where('on_table_id', $onTable->id)
                             ->where('on_field_id', $self->id);
                }
            );

        return $relation;
    }*/

    public function getRelationForSelect()
    {
        /**
         * So, $table is users table, $field is user_group_id for which we need to get relation.
         * We need to create entity user_groups (which is found on relation)
         * and select all records.
         */
        $relation = $this->hasOneSelectRelation;

        if (!$relation) {
            return $relation;
        }

        $showTable = $relation->showTable;
        $entity = $showTable->createEntity();

        $values = [];
        $entity->all()->each(
            function($record) use ($relation, &$values) {
                try {
                    $eval = eval(' return ' . $relation->value . '; ');
                    $values[$record->id] = $eval;
                } catch (Throwable $e) {
                    $values[$record->id] = exception($e);
                }
            }
        );

        return $values;
    }

    /**
     * @return bool
     *
     * Boolean type is always togglable.
     * Datetime is togglable if setting exists:
     *  - 0000-00-00 00:00:00 --> now
     */
    public function getIsTogglableAttribute()
    {
        return $this->fieldType->slug == 'boolean'
               || ($this->fieldType->slug == 'datetime' && $this->getSetting('pckg-generic-field-toggle'));
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings->first(
            function($item) use ($key) {
                return $item->slug == $key;
            }
        );

        if (!$setting) {
            return $default;
        }

        return $setting->pivot->value;
    }

    public function getJsonSetting($key, $default = null)
    {
        $setting = $this->getSetting($key, $default);

        if (!$setting) {
            return $default;
        }

        return \json_decode($setting);
    }

    public function getMinTogglableAttribute()
    {
        $setting = $this->getJsonSetting('pckg-generic-field-toggle');

        if (!$setting) {
            return null;
        }

        return isset($setting->min->eval) ? eval('return ' . $setting->min->eval . ';') : $setting->min;
    }

    public function getMaxTogglableAttribute()
    {
        $setting = $this->getJsonSetting('pckg-generic-field-toggle');

        if (!$setting) {
            return null;
        }

        return isset($setting->max->eval) ? eval('return ' . $setting->max->eval . ';') : $setting->max;
    }

    public function getAbsoluteDir($dir)
    {
        if (strpos($dir, path('ds')) === 0) {
            return $dir;
        }

        return path('app_uploads') . $dir . path('ds');
    }

    public function getLabelAttribute()
    {
        return $this->title ?? $this->field;
    }

}