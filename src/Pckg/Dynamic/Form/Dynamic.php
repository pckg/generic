<?php namespace Pckg\Dynamic\Form;

use Pckg\Auth\Entity\UserGroups;
use Pckg\Collection;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\HasOne;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Record;
use Pckg\Dynamic\Record\Table;
use Pckg\Framework\Inter\Entity\Languages;
use Pckg\Htmlbuilder\Decorator\Method\Wrapper\Dynamic as DynamicDecorator;
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

    /**
     * @var Record
     */
    protected $foreignRecord;

    /**
     * @var string
     */
    protected $foreignFieldId;

    protected $editable = true;

    public function __construct()
    {
        parent::__construct();

        $this->addDecorator($this->decoratorFactory->create(DynamicDecorator::class));
    }

    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    public function isEditable()
    {
        return $this->editable;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    public function setRecord(DatabaseRecord $record)
    {
        $this->record = $record;

        return $this;
    }

    public function setForeignFieldId($foreign)
    {
        $this->foreignFieldId = $foreign;

        return $this;
    }

    public function setForeignRecord($foreign)
    {
        $this->foreignRecord = $foreign;

        return $this;
    }

    public function populatePasswords(DatabaseRecord $record)
    {
        $data = $this->getData();
        if (!isset($data['password'])) {
            return;
        }

        $password = $data['password'];
        $field = (new Fields())->where('dynamic_table_id', $this->table->id)
                               ->where('field', 'password')
                               ->one();
        if (!$field) {
            return;
        }

        $provider = $field->getSetting('pckg.dynamic.field.passwordProvider');

        $record->password = $provider
            ? auth($provider)->hashPassword($password)
            : $password;
    }

    public function initLanguageFields()
    {
        $languages = (new Languages())->all()
                                      ->keyBy('slug')
                                      ->map('title');

        if (count($languages) < 2) {
            return;
        }

        $this->addFieldset('translatable');
        /**
         * @T00D00 - field language_id could/will interfere with main table fields ...
         */
        $sessionLanguageId = session()->pckg_dynamic_lang_id;
        $languageId = $this->record ? ($this->record->language_id ?? $sessionLanguageId) : null;
        $this->addSelect('language_id')
             ->setValue($languageId)
             ->addOptions($languages)
             ->setLabel('Language');
        if ($this->isEditable()) {
            $this->addSubmit('switch_language')->setValue('Switch language');
        }

        return $this;
    }

    public function initPermissionFields()
    {
        return;
        /**
         * @T00D00 - this should be handled separately, like in different form or even different page/tab.
         */
        $allPermissions = $this->record->allPermissions->groupBy('user_group_id')->map(
            function($permissions) {
                return (new Collection($permissions))->keyBy('action');
            }
        )->toArray();

        $this->addFieldset('permissionable');

        $tablePermissions = [
            'read'   => 'Read',
            'write'  => 'Write',
            'delete' => 'Delete',
        ];

        if ($this->table->table == 'dynamic_fields' || $this->table->table == 'dynamic_relations') {
            // $tablePermissions['view'] = 'View';
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
                $child .= '<td><input type="checkbox" name="p17n[table][' . $group->id . '][' . $permissionKey .
                          ']" value="1" ' .
                          (isset($allPermissions[$group->id][$permissionKey]) ? 'checked = checked ' : '') . ' /></td>';
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
                $child .= '<td><input type="checkbox" name="p17n[action][' . $group->id . '][' . $action->id .
                          ']" value="1" ' .
                          (isset($allActionPermissions[$group->id][$action->id]) ? 'checked = checked ' : '') .
                          '/></td>';
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
                $fields->withFieldGroup();
            }
        );

        $prevGroup = null;
        foreach ($fields as $field) {
            if (($prevGroup && $prevGroup != $field->dynamic_field_group_id) ||
                (!$prevGroup && $field->dynamic_field_group_id)
            ) {
                $fieldset = $this->addFieldset()->setAttribute('data-field-group', $field->dynamic_field_group_id);
                $fieldset->addChild('<hr /><h4>' . $field->fieldGroup->title . '</h4>');
                $prevGroup = $field->dynamic_field_group_id;
            }

            $type = $field->fieldType->slug;
            $name = $field->field;

            if ($type == 'php') {
                /**
                 * PHP field is not editable.
                 * Should we display content?
                 */
                if (!$this->record->id) {
                    continue;
                }

                $element = $this->getFieldset()->addDiv()->addChild(
                    '<div class="form-group grouped php" data-field-id="' . $field->id . '"><label class="col-sm-3">' .
                    $field->title . '</label>
<div class="col-sm-9">' . $this->record->{$field->field} . '</div></div>'
                );
                continue;
            } elseif ($type != 'hidden' && !$field->hasPermissionTo('write') && config('pckg.dynamic.permissions')) {
                $element = $this->getFieldset()->addDiv()->addChild(
                    '<div class="form-group grouped readonly" data-field-id="' . $field->id . '"><label class="col-sm-3"></label>
<div class="col-sm-9">' . $this->record->{$field->field} . '</div></div>'
                );

                continue;
            } elseif (!$this->editable/* !$field->hasPermissionTo('edit')*/) {
                $this->createReadonlyElementByType($type, $name, $field);

                continue;
            } elseif ($field->id == $this->foreignFieldId) {
                $this->createElementByType('hidden', $name, $field);
                continue;
            }

            $element = $this->createElementByType($type, $name, $field);

            if (($label = $field->label)) {
                $translatable = $field->isTranslatable($this->record->getEntity())
                    ? '<span class="label label-info"><i class="fa fa-globe" aria-hidden="true"></i></span>'
                    : '';
                $element->setLabel($label . $translatable);
            }

            $element->setHelp($field->help);

            $element->setAttribute('data-field-id', $field->id);
        }

        if ($this->isEditable()) {
            $this->addSubmit('submit');
            $this->addSubmit('as_new')->setValue('As new')->setClass('btn-link');
        }

        return $this;
    }

    protected function createReadonlyElementByType($type, $name, Field $field)
    {
        $label = $field->label;
        $value = $this->record->{$field->field};

        if ($type == 'select' && $this->record) {
            $relation = $field->hasOneSelectRelation;
            if ($relation) {
                $relatedRecord = $field->getRecordForSelect($this->record, $this->foreignRecord, $value);
                if ($relatedRecord) {
                    $relationTitle = $field->eval($relation->value, $relatedRecord, $relation);
                    $url = url(
                        'dynamic.record.view',
                        [
                            'table'  => $relation->showTable,
                            'record' => $relatedRecord,
                        ]
                    );
                    $value = '<a href="' . $url . '">' . $relationTitle . '</a>';
                }
            }
        } elseif ($field->getSetting('pckg.dynamic.field.iframe')) {
            $tempValue = $value;
            $value = '<iframe id="iframe-field-' . $field->id . '" style="border: 0; width: 100%;"></iframe>';
            $value .= '<script type="text/x-template" id="iframe-field-div-' . $field->id .
                      '" style="display: none;">' . $tempValue . '</script>';
            $value .= '<script type="text/javascript">$(document).ready(function(){
    $("#iframe-field-' . $field->id . '")[0].contentDocument.write($("#iframe-field-div-' . $field->id . '").html());
});</script>';
        } elseif (in_array($type, ['file', 'pdf'])) {
            if ($this->record->{$field->field}) {
                $dir = $field->getAbsoluteDir(
                    $field->getSetting('pckg.dynamic.field.dir'),
                    $field->getSetting('pckg.dynamic.field.privateUpload')
                );
                $fullPath = $this->record->{$field->field}
                    ? media($this->record->{$field->field}, null, true, $dir)
                    : null;
                $value = '<a class="btn btn-success btn-md" title="Download ' . $type . '" href="' . $fullPath .
                         '"><i class="fa fa-download" aria-hidden="true"></i> Download ' .
                         $this->record->{$field->field} . '</a>';
            }
        } elseif ($type == 'picture') {
            if ($this->record->{$field->field}) {
                $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
                $fullPath = img($this->record->{$field->field}, null, true, $dir);
                $value = '<a href="' . $fullPath . '"><img style="max-width: 240px;" class="img-thumbnail" src="' .
                         $fullPath . '" /></a>';
            }
        }

        $element = $this->getFieldset()->addDiv()->addChild(
            '<div class="form-group grouped" data-field-id="' . $field->id . '"><label class="col-sm-3">' . $label . '
</label>
<div class="col-sm-9">' . $value . '</div></div>'
        );
    }

    protected function createElementByType($type, $name, Field $field)
    {
        $auto = [
            'id',
            'hidden',
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
            $dir = $field->getAbsoluteDir(
                $field->getSetting('pckg.dynamic.field.dir'),
                $field->getSetting('pckg.dynamic.field.privateUpload')
            );
            if ($this->record) {
                $fullPath = $this->record->{$field->field}
                    ? media($this->record->{$field->field}, null, true, $dir)
                    : null;
            }

            if ($field->getSetting('pckg.dynamic.field.uploadDisabled')) {
                $element = $this->addDiv();
                if ($this->record) {
                    if ($field->getSetting('pckg.dynamic.field.generateFileUrl')) {
                        $element->addChild(
                            '<a class="btn btn-info btn-md" title="Generate ' . $type . '" href="' .
                            $field->getGenerateFileUrlAttribute(
                                $this->record
                            ) . '"><i class="fa fa-refresh" aria-hidden="true"></i> Generate ' . $type . '</a>'
                        );
                    }
                    if ($this->record->{$field->field}) {
                        $element->addChild(
                            '&nbsp;&nbsp;<a class="btn btn-success btn-md" title="Download ' . $type . '" href="' .
                            $fullPath . '"><i class="fa fa-download" aria-hidden="true"></i> Download ' .
                            $this->record->{$field->field} . '</a>'
                        );
                    }
                }
            } else {
                $element = $this->addFile($name);
                $element->setPrefix(
                    '<i class="fa fa-file' . ($type == 'pdf' ? '-pdf' : '') . '-o" aria-hidden="true"></i>'
                );

                if ($fullPath) {
                    $element->setAttribute('data-image', $fullPath);
                }
                $element->setAttribute('data-type', $type);
            }

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
        } elseif ($type == 'point') {
            return $this->addPoint($name);
        } elseif ($type == 'geo') {
            $element = $this->addGeo($name);
            $element->setPrefix('<i class="fa fa-map-marker" aria-hidden="true"></i>');

            return $element;
        } elseif ($type == 'select') {

            if ($this->record) {
                $relation = $field->getRelationForSelect($this->record, $this->foreignRecord);
                $element = $this->addSelect($name);
                /**
                 * @T00D00 - add setting for select placeholder for speciffic field
                 */
                $element->addOption(null, ' -- select value -- ');
                if (count($relation) > 100) {
                    $element->addOption(
                        $this->record->{$field->field},
                        str_replace(
                            ['<br />', '<br/>', '<br>'],
                            ' - ',
                            $field->getItemForSelect(
                                $this->record,
                                null,
                                $this->record->{$field->field}
                            )
                        )
                    );
                } else {
                    foreach ($relation as $id => $value) {
                        $element->addOption($id, str_replace(['<br />', '<br/>', '<br>'], ' - ', $value));
                    }
                }

                $element->setAttribute(
                    'data-url',
                    url(
                        'dynamic.record.list',
                        [
                            'table' => $this->table,
                        ]
                    )
                );

                $element->setAttribute(
                    'data-refresh-url',
                    url(
                        'dynamic.records.field.selectList' . ($this->record->id ? '' : '.none'),
                        [
                            'table'  => $this->table,
                            'field'  => $field,
                            'record' => $this->record,
                        ]
                    )
                );

                $element->addClass('ajax');

                return $element;
            } else {
                return $this->{'addText'}($name);
            }
        } else {
            dd('Unknown dynamic form type: ' . $type);
        }
    }

}