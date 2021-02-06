<?php

namespace Pckg\Dynamic\Form;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Pckg\Auth\Entity\UserGroups;
use Pckg\Auth\Service\Auth;
use Pckg\Collection;
use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\HasOne;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Table;
use Pckg\Htmlbuilder\Builder\Pckg;
use Pckg\Htmlbuilder\Decorator\Method\VueJS;
use Pckg\Htmlbuilder\Decorator\Method\Wrapper\Dynamic as DynamicDecorator;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Locale\Record\Language;

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
     * @var Relation
     */
    protected $relation;
/**
     * @var string
     */
    protected $foreignFieldId;
    protected $editable = true;
    protected $lastFieldset;
    public function __construct()
    {
        parent::__construct();
        $this->addDecorator($this->decoratorFactory->create(DynamicDecorator::class));
        $this->addDecorator($this->decoratorFactory->create(VueJS::class));
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

    public function setRecord(Record $record = null)
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

    public function setRelation($relation)
    {
        $this->relation = $relation;
        return $this;
    }

    public function populatePasswords(Record $record)
    {
        $data = $this->getData();
        $fields = (new Fields())->where('dynamic_table_id', $this->table->id)
                                ->where('field', array_keys($data))
                                ->where('dynamic_field_type_id', 7)// password
                                ->all();
        $fields->each(function (Field $field) use ($record, $data) {

            $password = $data[$field->field];
            if (!$password) {
                return;
            }

            if ($provider = $field->getSetting('pckg.dynamic.field.passwordProvider')) {
                $record->{$field->field} = auth($provider)->hashPassword($password);
            } elseif ($encrypt = $field->getSetting('pckg.dynamic.field.encrypt')) {
                $record->{$field->field} = Crypto::encrypt(
                    $password,
                    Key::loadFromAsciiSafeString(config('security.key'))
                );
            } else {
                $record->{$field->field} = $password;
            }
        });
    }

    public function initLanguageFields()
    {
        $languages = localeManager()->getFrontendLanguages();
        $sessionLanguageId = $_SESSION['pckg_dynamic_lang_id'] ?? ($languages[0]->slug ?? 'en');
        $languageId = $this->record ? ($this->record->language_id ?? $sessionLanguageId) : $sessionLanguageId;
        $this->addHidden('language_id')->setValue($languageId);
        return $this;
        if (count($languages) < 2) {
            return;
        }

        $fieldset = $this->addFieldset('translatable');
/**
         * @T00D00 - field language_id could/will interfere with main table fields ...
         */
        $this->addSelect('language_id')->setValue($languageId)->addOptions($languages->keyBy('slug')->map(function (Language $language) {

            return $language->title;
        }))->setLabel('Language');
        if ($this->isEditable()) {
            $fieldset->addButton('switch_language')->setValue('Switch language')->a('@click.prevent', 'switchLanguage');
        }

        return $this;
    }

    public function initPermissionFields()
    {
        return;
/**
         * @T00D00 - this should be handled separately, like in different form or even different page/tab.
         */
        $allPermissions = $this->record->allPermissions->groupBy('user_group_id')->map(function ($permissions) {

            return (new Collection($permissions))->keyBy('action');
        })->toArray();
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
                    ']" value="1" ' . (isset($allPermissions[$group->id][$permissionKey]) ? 'checked = checked ' : '') .
                    ' /></td>';
            }
            $child .= '</tr>';
        }

        $table = (new Tables())->where('id', $this->table->id)->withActions()->one();
        $child .= '<tr><td colspan="' . ($authGroups->count() + 1) . '"><b>Actions permissions</b></td></tr>';
        foreach ($table->actions as $action) {
            $child .= '<tr>';
            $child .= '<td>' . $action->slug . '&nbsp;<input type="checkbox" class="toggle-horizontally"/></td>';
            $allActionPermissions = $action->allPermissions->groupBy('user_group_id')->each(function ($permissions) {

                return (new Collection($permissions))->groupBy('id');
            })->toArray();
            foreach ($authGroups as $group) {
                $child .= '<td><input type="checkbox" name="p17n[action][' . $group->id . '][' . $action->id .
                                ']" value="1" ' .
                                (isset($allActionPermissions[$group->id][$action->id]) ? 'checked = checked ' : '') . '/></td>';
            }
            $child .= '</tr>';
        }
        $child .= '</tbody></table>';
        $this->addDiv()->addChild($child);
    }

    public function initFields()
    {
        $this->setDecoratorClasses([
                                       'label'           => '',
                                       'field'           => '',
                                       'fullField'       => '',
                                       'offset'          => '',
                                       'offsetField'     => '',
                                       'offsetFullField' => '',
                                   ]);
        $fields = collect();
        for ($i = 0; $i < 2; $i++) {
            $fields = $this->table->listableFields(function (HasMany $fields) use ($i) {

                $fields->getRightEntity()->orderBy('dynamic_field_group_id ASC, `order` ASC');
                $fields->withPermissions();
                $fields->withHasOneSelectRelation(function (HasOne $relation) {

                    $relation->withOnTable();
                    $relation->withShowTable();
                });
                $fields->withFieldGroup();
                if ($i || ($this->record && $this->record->id) || context()->exists(Auth::class . ':api')) {
                    return;
                }

                $fields->where('required');
            });
            if ($fields->count()) {
                break;
            }
        }

        $prevGroup = null;
        $fieldPositions = $fields->groupBy(function (Field $field) {

            return $field->dynamic_field_group_id ? ($field->fieldGroup->position ?? 'left') : 'left';
        });

        foreach ($fieldPositions as $position => $fields) {
// $positionFieldset = $this->addFieldset('position-' . $position);
            foreach ($fields as $field) {
                if (
                    ($prevGroup && $prevGroup != $field->dynamic_field_group_id) ||
                    (!$prevGroup && $field->dynamic_field_group_id) || (!$prevGroup && !$this->lastFieldset)
                ) {
                    $this->lastFieldset = $fieldset = $this->addFieldset('position-' . $position)
                                                           ->setAttribute(
                                                               'data-field-group',
                                                               $field->dynamic_field_group_id
                                                           );
                    $fieldset->addChild('<h4 class="__component-title">' . ($field->fieldGroup->title ?? 'General') .
                                        '</h4>');
                    $prevGroup = $field->dynamic_field_group_id;
                }

                $type = $field->fieldType->slug;
                $name = $field->field;
                $helpHtml = $field->help ? '<div class="help">' . $field->help . '</div>' : '';
                if ($type == 'php') {
                /**
                                     * PHP field is not editable.
                                     * Should we display content?
                                     */
                    if (!$this->record || !$this->record->id) {
                        continue;
                    }

                    $element = $fieldset->addDiv()->addChild('<div class="form-group grouped php" data-field-id="' .
                                                             $field->id . '"><label>' . $field->title . '</label>
<div>' . $this->record->{$field->field} . '</div>' . $helpHtml . '</div>');
                    continue;
                } elseif (
                    $type != 'hidden' && !$field->hasPermissionTo('write') &&
                    config('pckg.dynamic.permissions')
                ) {
                    $element = $fieldset->addDiv()
                                        ->addChild('<div class="form-group grouped readonly" data-field-id="' .
                                                   $field->id . '"><label></label>
<div>' . $this->record->{$field->field} . '</div>' . $helpHtml . '</div>');
                    continue;
                } elseif (!$this->editable/* !$field->hasPermissionTo('edit')*/) {
                    $this->createReadonlyElementByType($type, $name, $field);
                    continue;
                } elseif ($field->id == $this->foreignFieldId) {
                    $this->createElementByType('hidden', $name, $field);
                    continue;
                } elseif ($type == 'mysql') {
                    continue;
                }

                $element = $this->createElementByType($type, $name, $field);
/**
                 * We need to replace some elements, such as checkbox, editor, ...
                 */
                $element->setBuilder(new Pckg($element));
                if (($label = $field->label)) {
                    if ($field->required) {
                        $label = '* ' . $label;
                    }
                    $entity = $this->record ? $this->record->getEntity() : $this->table->getEntity();
                    $translatable = $field->isTranslatable($entity) &&
                    localeManager()->getFrontendLanguages()->count() > 1
                        ? '&nbsp;<pckg-tooltip icon="globe" content="Field is translatable"></pckg-tooltip>' : '';
                    $element->setLabel($label . $translatable);
                }

                $element->setHelp($field->help);
                $element->setAttribute('data-field-id', $field->id);
                if ($field->required) {
                    $element->required();
                }
            }
        }

        if ($this->isEditable()) {
            $this->addSubmit('submit')->setValue('Save');
// $this->addSubmit('as_new')->setValue('Save as')->setClass('btn-link');
        }

        return $this;
    }

    protected function createReadonlyElementByType($type, $name, Field $field)
    {
        $label = $field->label;
        $value = $this->record->data($field->field);
        if ($type == 'select' && $this->record) {
            $relation = $field->hasOneSelectRelation;
            if ($relation) {
                $relatedRecord = $field->getRecordForSelect($this->record, $this->foreignRecord, $value);
                if ($relatedRecord) {
                    $relationTitle = $field->eval($relation->value, $relatedRecord, $relation);
                    $url = url('dynamic.record.view', [
                        'table'  => $relation->showTable,
                        'record' => $relatedRecord,
                    ]);
                            $value = '<a href="' . $url . '">' . $relationTitle . '</a>';
                }
            }
        } elseif ($field->getSetting('pckg.dynamic.field.iframe')) {
            $tempValue = $value;
            $htmlValue = '<script type="text/x-template" id="iframe-field-div-' . $field->id . '">' . $tempValue .
                '</script>';
            $htmlValue .= '<script type="text/javascript">$(document).ready(function(){
    var ifrm = document.getElementById(\'iframe-field-' . $field->id . '\');
    ifrm = ifrm.contentWindow || ifrm.contentDocument.document || ifrm.contentDocument;
ifrm.document.open();
ifrm.document.write($("#iframe-field-div-' . $field->id . '").html());
ifrm.document.close();
    //$("#iframe-field-' . $field->id . '")[0].contentDocument.write($("#iframe-field-div-' . $field->id . '").html());
});</script>';
            vueManager()->addStringView($htmlValue);
            $value = '<iframe id="iframe-field-' . $field->id .
                '" style="border: 0; width: 100%; min-height: 360px;"></iframe>';
        } elseif (in_array($type, ['file', 'pdf'])) {
            if ($this->record->{$field->field}) {
                $dir = $field->getAbsoluteDir(
                    $field->getSetting('pckg.dynamic.field.dir'),
                    $field->getSetting('pckg.dynamic.field.privateUpload')
                );
                $fullPath = $this->record->{$field->field} ? media($this->record->{$field->field}, null, true, $dir)
                    : null;
                $value = '<a class="btn btn-success btn-md" title="Download ' . $type . '" href="' . $fullPath .
                    '"><i class="fal fa-fw fa-download" aria-hidden="true"></i> Download ' . $this->record->{$field->field} .
                    '</a>';
            }
        } elseif ($type == 'picture') {
            if ($this->record->{$field->field}) {
                $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
                $fullPath = img($this->record->{$field->field}, null, true, $dir);
                $value = '<a href="' . cdn($fullPath) . '"><img style="max-width: 240px;" class="img-thumbnail" src="' .
                    cdn($fullPath) . '" /></a>';
            }
        } elseif ($type == 'boolean') {
            $value = $value ? '<i class="fal fa-fw fa-check"></i>' : '<i class="fal fa-fw fa-times"></i>';
        }

        $helpHtml = $field->help ? '<div class="help">' . $field->help . '</div>' : '';
        $element = $this->lastFieldset->addDiv()->addChild('<div class="form-group grouped" data-field-id="' .
                                                           $field->id . '"><label>' . $label . '
</label>
<div>' . $value . '</div>' . $helpHtml . '</div>');
    }

    protected function createElementByType($type, $name, Field $field)
    {
        $auto = [
            'id',
            'hidden',
            'email',
            'text',
            'textarea',
            'editor',
            'integer',
            'decimal',
            'point',
        ];
        if (in_array($type, $auto)) {
            return $this->getFieldset()->{'add' . ucfirst($type)}($name);
        } elseif (in_array($type, ['file', 'pdf'])) {
            $dir = $field->getAbsoluteDir(
                $field->getSetting('pckg.dynamic.field.dir'),
                $field->getSetting('pckg.dynamic.field.privateUpload')
            );
            $fullPath = null;
            if ($this->record) {
                $fullPath = $this->record->{$field->field} ? media($this->record->{$field->field}, null, true, $dir)
                    : null;
            }

            if ($field->getSetting('pckg.dynamic.field.uploadDisabled')) {
                $element = $this->getFieldset()->addDiv();
                if ($this->record) {
                    if ($field->getSetting('pckg.dynamic.field.previewFileUrl')) {
                        $element->addChild('<a class="btn btn-default btn-md" title="Preview" href="' .
                                           $field->getPreviewFileUrlAttribute($this->record) .
                                           '?zoom=1"><i class="fal fa-fw fa-external-link" aria-hidden="true"></i> Preview ' .
                                           $type . '</a>&nbsp;&nbsp;');
                    }
                    if ($field->getSetting('pckg.dynamic.field.generateFileUrl')) {
                        $element->addChild('<a class="btn btn-default btn-md" title="Generate" href="' .
                           $field->getGenerateFileUrlAttribute($this->record) .
                           '"><i class="fal fa-fw fa-refresh" aria-hidden="true"></i> Generate ' . $type .
                           '</a>&nbsp;&nbsp;');
                    }
                    if ($this->record->{$field->field}) {
                        $element->addChild('<a class="btn btn-default btn-md" title="Download" href="' . $fullPath .
                           '"><i class="fal fa-fw fa-download" aria-hidden="true"></i> Download ' .
                           $this->record->{$field->field} . '</a>&nbsp;&nbsp;');
                    }
                }
            } else {
                $element = $this->getFieldset()->addFile($name);
                $element->setPrefix('<i class="fal fa-fw fa-file' . ($type == 'pdf' ? '-pdf' : '') .
                                '-o" aria-hidden="true"></i>');
                if ($fullPath) {
                    $element->setAttribute('data-image', $fullPath);
                }
                $element->setAttribute('data-type', $type);
                $element->setAttribute('data-url', $this->relation && $this->foreignRecord
                ? url('dynamic.records.field.upload.newForeign', [
                    'table'    => $this->table,
                    'field'    => $field,
                    'relation' => $this->relation,
                    'record'   => $this->foreignRecord,
                ])
                : ($this->record->id
                    ? url('dynamic.records.field.upload', [
                        'table'  => $this->table,
                        'field'  => $field,
                        'record' => $this->record,
                    ])
                    : url('dynamic.records.field.upload.new', [
                        'table' => $this->table,
                        'field' => $field,
                    ])));
            }

            return $element;
        } elseif ($type == 'picture') {
            $element = $this->getFieldset()->addPicture($name);
            $element->setAttribute('data-url', $this->relation && $this->foreignRecord
                ? url('dynamic.records.field.upload.newForeign', [
                    'table'    => $this->table,
                    'field'    => $field,
                    'relation' => $this->relation,
                    'record'   => $this->foreignRecord,
                ])
                : ($this->record->id
                    ? url('dynamic.records.field.upload', [
                        'table'  => $this->table,
                        'field'  => $field,
                        'record' => $this->record,
                    ])
                    : url('dynamic.records.field.upload.new', [
                        'table' => $this->table,
                        'field' => $field,
                    ])));
            $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
            $element->setAttribute('data-image', img($this->record->{$field->field}, null, true, $dir));
            $element->setAttribute('data-type', 'picture');
            return $element;
        } elseif ($type == 'datetime') {
            $element = $this->getFieldset()->addText($name);
            $element->addClass('vue-takeover');
            $element->setPrefix('<i class="fal fa-fw fa-calendar" aria-hidden="true"></i>');
            $element->a('autocomplete', 'off');
            $element->addClass('hidden')
                    ->addSibling('<pckg-datetime-picker v-model="form.' . $name . '" :options="{format: \'YYYY-MM-DD HH:mm\',type: \'datetime\',checkEnabled: null}"></pckg-datetime-picker>');
            return $element;
        } elseif ($type == 'date') {
            $element = $this->getFieldset()->addHidden($name);
            $element->addClass('vue-takeover');
            $element->setPrefix('<i class="fal fa-fw fa-calendar" aria-hidden="true"></i>');
            $element->a('autocomplete', 'off');
            $element->addClass('hidden')
                    ->addSibling('<pckg-datetime-picker v-model="form.' . $name . '" :options="{format: \'YYYY-MM-DD\',type: \'date\',checkEnabled: null}"></pckg-datetime-picker>');
            return $element;
        } elseif ($type == 'time') {
            $element = $this->getFieldset()->addTime($name);
            $element->addClass('vue-takeover');
            $element->setPrefix('<i class="fal fa-fw fa-clock-o" aria-hidden="true"></i>');
            $element->a('autocomplete', 'off');
            $element->addClass('hidden')
                    ->addSibling('<pckg-datetime-picker v-model="form.' . $name . '" :options="{format: \'HH:mm\',type: \'time\',checkEnabled: null}"></pckg-datetime-picker>');
            return $element;
        } elseif (in_array($type, ['password'])) {
            $element = $this->getFieldset()->addPassword($name);
            $element->setAttribute('autocomplete', 'off');
            $element->readonly();
            $element->setAttribute(
                'onfocus',
                "if (this.hasAttribute('readonly')) { this.removeAttribute('readonly'); this.blur(); this.focus(); }"
            );
            return $element;
        } elseif (in_array($type, ['slug', 'order', 'hash'])) {
            return $this->getFieldset()->addText($name);
        } elseif (in_array($type, ['json'])) {
            return $this->getFieldset()->addTextarea($name);
        } elseif ($type == 'boolean') {
            return $this->getFieldset()->addCheckbox($name);
        } elseif ($type == 'geo') {
            $element = $this->getFieldset()->addGeo($name);
            $element->setPrefix('<i class="fal fa-fw fa-map-marker" aria-hidden="true"></i>');
            return $element;
        } elseif ($type == 'select') {
            $relation = $field->getRelationForSelect($this->record, $this->foreignRecord);
            $element = $this->getFieldset()->addSelect($name);
        /**
                     * @T00D00 - add setting for select placeholder for speciffic field
                     */
            $options = [];
            $rawValue = $this->record->{$field->field} ?? null;
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
                $item = $field->getItemForSelect($this->record, null, $rawValue);
                if (!trim($item)) {
                    $item = $rawValue;
                }

                $options[$rawValue] = str_replace(['<br />', '<br/>', '<br>'], ' - ', $item);
            }

            $element->setAttribute('data-options', json_encode($options));
            $element->setAttribute(':initial-multiple', 'false');
            $element->setAttributes([
                                        'data-url'         => url('dynamic.record.list', [
                                            'table' => $field->hasOneSelectRelation->showTable,
                                        ]),
                                        'data-refresh-url' => url('dynamic.records.field.selectList' .
                                                                  ($this->record->id ? '' : '.none'), [
                                                                      'table'  => $this->table,
                                                                      'field'  => $field,
                                                                      'record' => $this->record,
                                                                  ]),
                                        'data-view-url'    => url('dynamic.record.view', [
                                            'table' => $field->hasOneSelectRelation->showTable,
                                        ]),
                                    ]);
        // $element->addClass('ajax');

            return $element;
        } else {
            ddd('Unknown dynamic form type: ' . $type);
        }
    }
}
