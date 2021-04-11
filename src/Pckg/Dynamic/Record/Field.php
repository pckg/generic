<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Fields;
use Throwable;

/**
 * Class Field
 * @package Pckg\Dynamic\Record
 * @property Relation $hasOneSelectRelation
 * @property FieldType $fieldType
 * @property string $settings
 * @property string $field
 * @property string $title
 */
class Field extends DatabaseRecord
{

    protected $entity = Fields::class;
    protected $toArray = [
        'fieldType',
        'isTogglable',
        'minTogglable',
        'maxTogglable',
        'isRaw',
        'title', // @T00D00 - this should be added to extension
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

    public function getEntityForSelect($record = null, $foreignRecord = null)
    {
        /**
         * So, $table is users table, $field is user_group_id for which we need to get user group title..
         * We need to create entity user_groups (which is found on relation)
         * and select all records.
         */
        $relation = $this->hasOneSelectRelation;
        if (!$relation) {
            return $relation;
        }

        $showTable = $relation->showTable;
        $entity = $showTable->createEntity();
        $this->automaticallyApplyRelation($entity, $relation->value);
/**
         * Now, we have $record->addition->title, editing related orders_users_additions on orders_users.
         * We have to select additions that are added to packets_additions for orders_user.packet_id
         *
         * @T00D00
         */
        $relation->applyFilterOnEntity($entity, $foreignRecord, $record);
        return $entity;
    }

    public static function automaticallyApplyRelation(Entity $entity, $eval)
    {
        /**
         * Append relation if we want to print for example
         *  - $record->order->num
         *  - $record->order->num . '<br />' . $record->user->email
         *
         * First, split string by spaces so we separate calls.
         */
        $explodedEvals = explode(' ', $eval);
        foreach ($explodedEvals as $partialToExplode) {
            $explodedEval = explode('->', $partialToExplode);
            if (count($explodedEval) == 3) {
        /**
                         * We're calling something like $record->user->email
                         */
                $entity->{'with' . ucfirst($explodedEval[1])}(function ($relation) {

                    if ($relation->getRightEntity()->isTranslatable()) {
                        if (!$relation->getRightEntity()->isTranslated()) {
                            $relation->getRightEntity()->joinTranslations();
                        }
                    }
                });
            }
        }
    }

    public function getItemForSelect($record, $foreignRecord, $value)
    {
        $relation = $this->hasOneSelectRelation;
        $relatedRecord = $this->getRecordForSelect(
            $record,
            $foreignRecord,
            $value,
            $relation->foreign_field_id ? $relation->foreignField->field : 'id'
        );
        if (!$relatedRecord) {
            return null;
        }

        $value = $this->eval($relation->value, $relatedRecord, $relation);
        return $value;
    }

    public function getRecordForSelect($record, $foreignRecord, $value, $by = 'id')
    {
        $entity = $this->getEntityForSelect($record, $foreignRecord);
        if (!$entity) {
            return null;
        }

        /**
         * We need to replace id with real relation slug.
         */
        $entity->where($by, $value);
        return $entity->one();
    }

    public function getRelationForSelect($record = null, $foreignRecord = null, Entity $entity = null)
    {
        if (!$entity) {
            $entity = $this->getEntityForSelect($record, $foreignRecord);
        }

        if (!$entity) {
            return [];
        }

        $entity->limit(250);
        if ($entity->isDeletable()) {
            $entity->nonDeleted();
        }

        return $this->fetchAndPrepareResultsForSelect($entity);
    }

    protected function fetchAndPrepareResultsForSelect(Entity $entity)
    {
        $relation = $this->hasOneSelectRelation;
        $foreignField = $relation->foreign_field_id
            ? $relation->foreignField->field
            : 'id';
        $values = [];
        $records = $entity->all();
        $records->each(function ($record) use ($relation, &$values, $foreignField) {

                $value = $this->eval($relation->value, $record, $relation);
            $groupValue = $relation->group_value
                    ? $this->eval($relation->group_value, $record, $relation)
                    : null;
            $values[$groupValue][$record->{$foreignField}] = $value;
        });
        if (count($values) == 1) {
            $values = end($values);
        }

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
            if (prod()) {
                return '#' . $record->id;
            }

            return '#' . $record->id . ' - ' . exception($e);
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
        $setting = $this->settings->first(function ($item) use ($key) {

                return $item->slug == $key;
        });
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

    public function getIsRawAttribute()
    {
        return $this->getSetting('pckg-generic-field-isRaw');
    }

    public function getPreviewFileUrlAttribute($record = null)
    {
        $setting = $this->getSetting('pckg.dynamic.field.previewFileUrl');
        if (!$setting) {
            return null;
        }

        return $this->eval($setting, $record);
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

        return path($private ? 'app_private' : 'app_uploads') . ($dir ? $dir . path('ds') : '');
    }

    public function getLabelAttribute()
    {
        return $this->title ?? $this->field;
    }

    public function isTranslatable($entity)
    {
        return $entity->getRepository()->getCache()->tableHasField($entity->getTable() . '_i18n', $this->field);
    }

    public function getTransformedValue($entity)
    {
        $field = $this;
        if ($this->fieldType && $this->fieldType->slug == 'php') {
            return function ($record) use ($field) {

                return $record->{'get' . ucfirst($field->field) . 'Attribute'}();
            };
        } elseif ($this->fieldType && $this->fieldType->slug == 'geo') {
            $entity->addSelect([
                    $this->field . '_x' => 'X(' . $this->field . ')',
                    $this->field . '_y' => 'Y(' . $this->field . ')',
                    $this->field        => 'CONCAT(X(' . $this->field . '), \';\', Y(' . $this->field . '))',
                ]);
            return function ($record) use ($field) {

                $value = $record->{$field->field};
                return $value
                    ? $record->{$field->field . '_x'} . ';' . $record->{$field->field . '_y'}
                    : null;
            };
        }
    }

    public function selectMultiField(Entity $tablesEntity)
    {
        if ($this->fieldType->slug == 'geo') {
            $tablesEntity->addSelect([
                    $this->field . '_x' => 'X(' . $this->field . ')',
                    $this->field . '_y' => 'Y(' . $this->field . ')',
                    $this->field        => 'CONCAT(X(' . $this->field . '), \';\', Y(' . $this->field . '))',
                ]);
        } else if ($this->fieldType->slug == 'mysql' && method_exists($tablesEntity, 'select' . ucfirst($this->field) . 'Field')) {
            $tablesEntity->{'select' . ucfirst($this->field) . 'Field'}();
        }
    }

    public function isImportable()
    {
        return !in_array($this->fieldType->slug, ['mysql', 'php']);
    }

    public function getRelationOptions($relation, Record $record = null)
    {
        $options = [];
        $rawValue = $record->{$this->field} ?? null;
        $foundValue = false;
        foreach ($relation as $id => $value) {
            if (is_array($value)) {
                $optgroup = [];
                foreach ($value as $k => $v) {
                    $optgroup[$k] = str_replace(['<br />', '<br/>', '<br>'], ' - ', $v);
                    $foundValue = $foundValue || $k == $rawValue;
                }
                $options[$id] = $optgroup;
            } else {
                $options[$id] = str_replace(['<br />', '<br/>', '<br>'], ' - ', $value);
                $foundValue = $foundValue || $id == $rawValue;
            }
        }

        if (!$foundValue && $rawValue) {
            $item = $this->getItemForSelect($record, null, $rawValue);
            if (!trim($item)) {
                $item = $rawValue;
            }

            $options[$rawValue] = str_replace(['<br />', '<br/>', '<br>'], ' - ', $item);
        }

        return $options;
    }

    public function getVueOptions($initialOptions, $record = null)
    {
        $options = new \stdClass();
        $slug = $this->fieldType->slug;
        if ($slug === 'select') {
            $options = [
                'options' => $initialOptions[$this->field] ?? [],
            ];
        } else if ($slug === 'picture') {

            /*$url = $this->relation && $this->foreignRecord
                ? url('dynamic.records.field.upload.newForeign', [
                    'table' => $this->table,
                    'field' => $field,
                    'relation' => $this->relation,
                    'record' => $this->foreignRecord,
                ])
                : ($this->record->id
                    ? url('dynamic.records.field.upload', [
                        'table' => $this->table,
                        'field' => $field,
                        'record' => $this->record,
                    ])
                    : url('dynamic.records.field.upload.new', [
                        'table' => $this->table,
                        'field' => $field,
                    ]));*/

            $options = [
                'url' => url('dynamic.records.field.upload', [
                    'table' => $this->table,
                    'field' => $this,
                    'record' => $record,
                ]),
            ];
        }

        return $options;
    }
}
