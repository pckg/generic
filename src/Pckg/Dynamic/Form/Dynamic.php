<?php namespace Pckg\Dynamic\Form;

use Pckg\Auth\Entity\UserGroups;
use Pckg\Collection;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\HasOne;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Record;
use Pckg\Dynamic\Record\Table;
use Pckg\Framework\Inter\Entity\Languages;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;

class Dynamic extends Bootstrap
{

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Record
     */
    protected $record;

    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    public function setRecord(Record $record)
    {
        $this->record = $record;

        return $this;
    }

    public function initLanguageFields()
    {
        $languages = (new Languages())->joinTranslation()
                                      ->all()
                                      ->keyBy('slug')
                                      ->map('title');

        if (count($languages) < 2) {
            return;
        }

        $this->addFieldset('translatable');
        /**
         * @T00D00 - field language_id could/will interfere with main table fields ...
         */
        $this->addSelect('language_id')
             ->setValue($this->record ? $this->record->language_id : null)
             ->addOptions($languages)
             ->setLabel('Language');
        $this->addSubmit('switch_language')->setValue('Switch language');
        $this->addSubmit('copy_to_language')->setValue('Copy to language');

        return $this;
    }

    public function initPermissionFields()
    {
        /**
         * @T00D00 - this should be handled separately, like in different form or even different page/tab.
         */
        $allPermissions = $this->record->allPermissions->groupBy('user_group_id')->each(
            function($permissions) {
                return (new Collection($permissions))->groupBy('action');
            },
            true
        )->toArray();

        $this->addFieldset('permissionable');

        $tablePermissions = [
            'read'  => 'Read',
            'write' => 'Write',
        ];

        if ($this->table->table == 'dynamic_fields' || $this->table->table == 'dynamic_relations') {
            $tablePermissions['view'] = 'View';
        }

        $authGroups = (new UserGroups())->all();

        $child = '<hr /><h4>Permissions</h4><table class="table table-striped table-condensed">';
        $child .= '<thead><tr><th></th>';
        foreach ($authGroups as $group) {
            $child .= '<th>' . $group->slug . '&nbsp;<input type="checkbox" class="toggle-vertically"/></th>';
        }
        $child .= '</tr></thead><tbody>';
        $child .= '<tr><td colspan="' . ($authGroups->count() + 1) . '"><b>Table permissions</b></td></tr>';
        foreach ($tablePermissions as $permissionKey => $permissionTitle) {
            $child .= '<tr>';
            $child .= '<td>' . $permissionTitle . '&nbsp;<input type="checkbox" class="toggle-horizontally"/></td>';
            foreach ($authGroups as $group) {
                $child .= '<td><input type="checkbox" name="p17n[table][' . $group->id . '][' . $permissionKey . ']" value="1" ' . (isset($allPermissions[$group->id][$permissionKey]) ? 'checked = checked ' : '') . ' /></td>';
            }
            $child .= '</tr>';
        }

        $table = (new Tables())->where('id', $this->table->id)->withActions()->one();
        $child .= '<tr><td colspan="' . ($authGroups->count() + 1) . '"><b>Actions permissions</b></td></tr>';
        foreach ($table->actions as $action) {
            $child .= '<tr>';
            $child .= '<td>' . $action->slug . '&nbsp;<input type="checkbox" class="toggle-horizontally"/></td>';
            $allActionPermissions = $action->allPermissions->groupBy('user_group_id')->each(
                function($permissions) {
                    return (new Collection($permissions))->groupBy('id');
                }
            )->toArray();
            foreach ($authGroups as $group) {
                $child .= '<td><input type="checkbox" name="p17n[action][' . $group->id . '][' . $action->id . ']" value="1" ' . (isset($allActionPermissions[$group->id][$action->id]) ? 'checked = checked ' : '') . '/></td>';
            }
            $child .= '</tr>';
        }
        $child .= '</tbody></table>';

        $this->addDiv()->addChild($child);
    }

    public function initFields()
    {
        $this->addFieldset();

        $fields = $this->table->listableFields(
            function(HasMany $fields) {
                $fields->getRightEntity()->orderBy('dynamic_field_group_id ASC, `order` ASC');
                $fields->withFieldType();
                $fields->withPermissions();
                $fields->withHasOneSelectRelation(
                    function(HasOne $relation) {
                        $relation->withOnTable();
                        $relation->withShowTable();
                    }
                );
            }
        );

        $prevGroup = null;
        foreach ($fields as $field) {
            if (
                ($prevGroup && $prevGroup != $field->dynamic_field_group_id) ||
                (!$prevGroup && $field->dynamic_field_group_id)
            ) {
                $fieldset = $this->addFieldset()->setAttribute('data-field-group', $field->dynamic_field_group_id);
                $fieldset->addChild('<hr /><h4>Field group #' . $field->dynamic_field_group_id . '</h4>');
                $prevGroup = $field->dynamic_field_group_id;
            }

            $type = $field->fieldType->slug;
            $name = $field->field;
            $label = $field->title ?: $name;

            if ($type == 'php') {
                /**
                 * PHP field is not editable.
                 * Should we display content?
                 */
                continue;
            } elseif ($type != 'hidden' && !$field->hasPermissionTo('write') && config('pckg.dynamic.permissions')) {
                $element = $this->addDiv()->addChild(
                    '<div class="form-group grouped" data-field-id="' . $field->id . '"><label class="col-sm-3">' . $label . '<div class="help"><button type="button" class="btn btn-info btn-xs btn-rounded" data-toggle="popover" data-trigger="focus" title="" data-content="<p>This is help text.</p>" data-placement="top" data-container="body" data-original-title="Help"><i class="fa fa-question" aria-hidden="true"></i></button></div>
</label>
<div class="col-sm-9">' . $this->record->{$field->field} . '</div></div>'
                );

                continue;
            }

            $element = $this->createElementByType($type, $name, $field);

            if ($label) {
                $element->setLabel($label);
            }

            $element->setHelp($field->help);

            $element->setAttribute('data-field-id', $field->id);
        }

        $this->addSubmit('submit');
        $this->addSubmit('as_new')->setValue('As new');

        return $this;
    }

    protected function createElementByType($type, $name, Field $field)
    {
        $auto = [
            'id',
            'email',
            'password',
            'text',
            'textarea',
            'editor',
            'integer',
            'decimal',
        ];
        if (in_array($type, $auto)) {
            return $this->{'add' . ucfirst($type)}($name);

        } elseif (in_array($type, ['file', 'pdf'])) {
            $element = $this->addFile($name);
            $element->setPrefix('<i class="fa fa-file' . ($type == 'pdf' ? '-pdf' : '') . '-o" aria-hidden="true"></i>');

            $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
            $element->setAttribute(
                'data-image',
                media($this->record->{$field->field}, null, true, $dir)
            );
            $element->setAttribute('data-type', $type);

            return $element;

        } elseif ($type == 'picture') {
            $element = $this->addPicture($name);
            $element->setPrefix('<i class="fa fa-picture-o" aria-hidden="true"></i>');
            $element->setAttribute(
                'data-url',
                url(
                    'dynamic.records.field.upload',
                    [
                        'table'  => $this->table,
                        'field'  => $field,
                        'record' => $this->record,
                    ]
                )
            );
            $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
            $element->setAttribute(
                'data-image',
                img($this->record->{$field->field}, null, true, $dir)
            );
            $element->setAttribute('data-type', 'picture');

            return $element;

        } elseif ($type == 'datetime') {
            $element = $this->addDatetime($name);
            $element->setPrefix('<i class="fa fa-calendar" aria-hidden="true"></i>');

            return $element;

        } elseif ($type == 'date') {
            $element = $this->addDate($name);
            $element->setPrefix('<i class="fa fa-calendar" aria-hidden="true"></i>');

            return $element;

        } elseif ($type == 'time') {
            $element = $this->addTime($name);
            $element->setPrefix('<i class="fa fa-clock-o" aria-hidden="true"></i>');

            return $element;

        } elseif (in_array($type, ['slug', 'order', 'hash'])) {
            return $this->addText($name);

        } elseif (in_array($type, ['json'])) {
            return $this->addTextarea($name);

        } elseif ($type == 'boolean') {
            return $this->addCheckbox($name);

        } elseif ($type == 'select') {
            if ($this->record && $relation = $field->getRelationForSelect()) {
                $element = $this->addSelect($name);
                /**
                 * @T00D00 - add setting for select placeholder for speciffic field
                 */
                $element->addOption(null, ' -- select value -- ');
                foreach ($relation as $id => $value) {
                    $element->addOption($id, $value);
                }

                return $element;
            } else {
                return $this->{'addText'}($name);
            }

        } else {
            dd('Unknown dynamic form type: ' . $type);

        }
    }

}