<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Record\Extension\Permissionable;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Service\Dynamic;
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

    public function getRelationForSelect($record = null, $foreignRecord = null)
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
        resolve(Dynamic::class)->joinTranslationsIfTranslatable($entity);

        /**
         * Append relation if we want to print for example
         *  - $record->order->num
         *  - $record->order->num . '<br />' . $record->user->email
         */
        $toEval = $relation->value;
        $explodedEvals = explode(' ', $toEval);
        foreach ($explodedEvals as $partialToExplode) {
            $explodedEval = explode('->', $partialToExplode);
            if (count($explodedEval) == 3) {
                $entity->{'with' . ucfirst($explodedEval[1])}();
            }
        }

        /**
         * Now, we have $record->addition->title, editing related orders_users_additions on orders_users.
         * We have to select additions that are added to packets_additions for orders_user.packet_id
         */
        $relation->applyFilterOnEntity($entity, $foreignRecord);

        $values = [];
        $entity->all()->each(
            function($record) use ($relation, &$values) {
                $values[$record->id] = $this->eval($relation->value, $record, $relation);
            }
        );

        return $values;
    }

    /**
     * @param      $eval
     * @param      $record
     * @param      $originalRecord
     * @param null $relation
     *
     * @return mixed|string
     *
     * See if we can do this in more secure way! @T00D00
     */
    public function eval($eval, $record = null, $relation = null)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (Throwable $e) {
            throw $e;
        }
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

    public function getGenerateFileUrlAttribute($record = null)
    {
        $setting = $this->getSetting('pckg.dynamic.field.generateFileUrl');

        if (!$setting) {
            return null;
        }

        return $this->eval($setting, $record);
    }

    public function getAbsoluteDir($dir, $private = false)
    {
        if (strpos($dir, path('ds')) === 0) {
            return $dir;
        }

        return path($private ? 'app_private' : 'app_uploads') . $dir . path('ds');
    }

    public function getLabelAttribute()
    {
        return $this->title ?? $this->field;
    }

}