<?php namespace Pckg\Dynamic\Form;

use Pckg\Auth\Entity\UserGroups;
use Pckg\Collection;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Record;
use Pckg\Dynamic\Record\Table;
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
        $this->addFieldset('translatable');
        $this->addSelect('language_id')
             ->setValue($this->record ? $this->record->language_id : null)
             ->addOptions(
                 [
                     'sl' => 'Slovenski',
                     'en' => 'English',
                 ]
             )
             ->setLabel('Language');
        $this->addSubmit('switch_language')->setValue('Switch language');
        $this->addSubmit('copy_to_language')->setValue('Copy to language');

        return $this;
    }

    public function initPermissionFields()
    {
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

        $child = '<h3>Permissions</h3><table class="table table-striped table-condensed">';
        $child .= '<thead><tr><th></th>';
        foreach ($authGroups as $group) {
            $child .= '<th>' . $group->slug . '&nbsp;<input type="checkbox" class="toggle-vertically"/></th>';
        }
        $child .= '</tr></thead><tbody>';
        $child .= '<tr><td><b>Table permissions</b></td></tr>';
        foreach ($tablePermissions as $permissionKey => $permissionTitle) {
            $child .= '<tr>';
            $child .= '<td>' . $permissionTitle . '&nbsp;<input type="checkbox" class="toggle-horizontally"/></td>';
            foreach ($authGroups as $group) {
                $child .= '<td><input type="checkbox" name="p17n[table][' . $group->id . '][' . $permissionKey . ']" value="1" ' . (isset($allPermissions[$group->id][$permissionKey]) ? 'checked = checked ' : '') . ' /></td>';
            }
            $child .= '</tr>';
        }

        $table = (new Tables())->where('id', $this->table->id)->withActions()->one();
        $child .= '<tr><td><b>Actions permissions</b></td></tr>';
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
        $fields = $this->table->listableFields;

        foreach ($fields as $field) {
            $type = $field->fieldType->slug;
            $name = $field->field;
            $label = $field->title ?: $name;

            if ($type == 'php') {
                continue;
            } elseif ($type != 'hidden' && !$field->hasPermissionTo('write') && config('pckg.dynamic.permissions')) {
                $element = $this->addDiv()->addChild(
                    '<div class="form-group grouped"><label class="col-sm-3">' . $label . '<div class="help"><button type="button" class="btn btn-info btn-xs btn-rounded" data-toggle="popover" data-trigger="focus" title="" data-content="<p>This is help text.</p>" data-placement="top" data-container="body" data-original-title="Help"><i class="fa fa-question" aria-hidden="true"></i></button></div>
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
            'date',
            'time',
            'datetime',
        ];
        if (in_array($type, $auto)) {
            return $this->{'add' . ucfirst($type)}($name);

        } elseif ($type == 'slug') {
            $type = 'text';

            return $this->{'add' . ucfirst($type)}($name);

        } elseif ($type == 'boolean') {
            return $this->{'addCheckbox'}($name);

        } elseif ($type == 'select') {
            if ($this->record && $relation = $this->record->getRelationForSelect($this->table, $field)) {
                $element = $this->addSelect($name);
                $element->addOption(null);
                foreach ($relation as $id => $value) {
                    $element->addOption($id, $value);
                }

                return $element;
            } else {
                return $this->{'addText'}($name);
            }

        } elseif (in_array($type, ['order', 'hash', 'datetime', 'decimal', 'picture'])) {
            $type = 'text';

            return $this->{'add' . ucfirst($type)}($name);

        } else {
            dd('Unknown dynamic form type: ' . $type);

        }
    }

}