<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record;
use Pckg\Database\Record\Extension\Permissionable;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Fields;

class Field extends Record
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
    public function getSelectRelation(Table $onTable)
    {
        $self = $this;
        $relation = $this->relationExists('hasOneSelectRelation')
            ? $this->getRelation('hasOneSelectRelation')
            : $this->hasOneSelectRelation(
                function(HasMany $relation) use ($onTable, $self) {
                    $relation->where('on_table_id', $onTable->id)
                             ->where('on_field_id', $self->id);
                }
            );

        return $relation;
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

        return $setting->poly->value;
    }

    public function getMinTogglableAttribute()
    {
        $setting = \json_decode($this->getSetting('pckg-generic-field-toggle'));

        if (!$setting) {
            return null;
        }

        return $setting->min;
    }

    public function getMaxTogglableAttribute()
    {
        $setting = \json_decode($this->getSetting('pckg-generic-field-toggle'));

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

        return path('uploads') . $dir . path('ds');
    }

}