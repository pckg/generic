<?php namespace Pckg\Dynamic\Controller;

use Pckg\Concept\Reflect;
use Pckg\Database\Collection;
use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Query\Raw;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasAndBelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\MorphedBy;
use Pckg\Database\Relation\MorphsMany;
use Pckg\Dynamic\Dataset\Fields;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Form\Dynamic;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Func;
use Pckg\Dynamic\Record\Record;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Tab;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableView;
use Pckg\Dynamic\Service\Dynamic as DynamicService;
use Pckg\Framework\Controller;
use Pckg\Framework\Inter\Record\Language;
use Pckg\Framework\Locale\Lang;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View\Twig;
use Pckg\Maestro\Helper\Maestro;
use Pckg\Maestro\Service\Tabelize;
use Pckg\Manager\Upload;
use Throwable;

class Records extends Controller
{

    use Maestro;

    /**
     * @var DynamicService
     */
    protected $dynamic;

    /**
     * @var Plugin
     */
    protected $pluginService;

    public function __construct(
        DynamicService $dynamic,
        Plugin $pluginService
    ) {
        $this->dynamic = $dynamic;
        $this->pluginService = $pluginService;
    }

    public function getSelectListAction(
        Table $table,
        Field $field,
        Record $record = null
    ) {
        $collection = new Collection();
        $collection->push(' -- select value --', null);
        $search = get('search');
        $this->dynamic->setTable($table);
        if ($search) {
            $relation = $field->getFilteredRelationForSelect($record, null, $this->dynamic);
        } else {
            $relation = $field->getRelationForSelect($record);
        }
        foreach ($relation as $id => $value) {
            $collection->push(str_replace(['<br />', '<br/>', '<br>'], ' - ', $value), $id);
        }

        return $this->response()->respondWithSuccess(
            [
                'records' => $collection->all(),
            ]
        );
    }

    public function getViewTableViewAction(
        Table $tableRecord,
        DynamicService $dynamicService,
        DatabaseEntity $entity = null,
        TableView $tableView,
        $viewType = 'full'
    ) {
        /**
         * Set table.
         */
        $this->dynamic->setView($tableView);
        $tableView->loadToSession();

        return $this->getViewTableAction($tableRecord, $dynamicService, $entity, $viewType);
    }

    public function getConfigureTableViewAction(
        Table $tableRecord,
        DynamicService $dynamicService,
        DatabaseEntity $entity = null,
        $viewType = 'full'
    ) {
        $this->dynamic->setTable($tableRecord);
        $dynamicService->setTable($tableRecord);
        $this->getViewTableAction($tableRecord, $dynamicService, $entity, $viewType, true);

        $fields = $this->dynamic->getFieldsService()->getAvailableFields();
        $relations = $this->dynamic->getFieldsService()->getAvailableRelations();

        return [
            'fields'        => $fields,
            'relations'     => $relations,
            'directions'    => $this->dynamic->getSortService()->getDirections(),
            'filterMethods' => $this->dynamic->getFilterService()->getTypeMethods(),
        ];
    }

    public function postConfigureTableViewAction(Table $tableRecord, DynamicService $dynamicService)
    {
        $_SESSION['pckg']['dynamic']['view']['table_' . $tableRecord->id . '_']['view'] = post()->all();

        return [
            'message' => 'ok',
            'data'    => post()->all(),
        ];
    }

    /**
     * List table records.
     *
     * @param Table  $tableRecord
     * @param Entity $entity
     *
     * @return $this|Tabelize
     */
    public function getViewTableAction(
        Table $tableRecord,
        DynamicService $dynamicService = null,
        DatabaseEntity $entity = null,
        $viewType = 'full',
        $returnTabelize = false,
        Tab $tab = null
    ) {
        if (!$dynamicService) {
            $dynamicService = $this->dynamic;
        }

        /**
         * Set table so sub-services can reuse it later.
         */
        $dynamicService->setTable($tableRecord);

        if (!$entity) {
            $entity = $tableRecord->createEntity();

            $dir = path('app_src') . implode(path('ds'), array_slice(explode('\\', get_class($entity)), 0, -2))
                   . path('ds') . 'View' . path('ds');
            Twig::addDir($dir);
            /**
             * This is needed for table actions.
             */
            Twig::addDir($dir . 'tabelize' . path('ds') . 'recordActions' . path('ds'));
            Twig::addDir($dir . 'tabelize' . path('ds') . 'entityActions' . path('ds'));
        }

        /**
         * Apply entity extension.
         */
        $dynamicService->applyOnEntity($entity);

        /**
         * This is used for URLs.
         */
        $entity->setStaticDynamicTable($tableRecord);

        /**
         * Join extensions.
         */
        $dynamicService->joinTranslationsIfTranslatable($entity);
        $dynamicService->joinPermissionsIfPermissionable($entity);
        $dynamicService->removeDeletedIfDeletable($entity);

        /**
         * Get all relations for fields with type (select).
         */
        $relations = (new Relations())->withShowTable()
                                      ->withOnField()
                                      ->where('on_table_id', $tableRecord->id)
                                      ->where('dynamic_relation_type_id', 1)
                                      ->all();

        foreach ($relations as $relation) {
            /**
             * Right table entity is created here.
             */
            $alias = $relation->alias ?? $relation->showTable->table;
            $relationEntity = $relation->showTable->createEntity($alias);
            $dynamicService->joinTranslationsIfTranslatable($relationEntity);

            /**
             * We need to add relations to select.
             * $tableRecord is for example users.
             * So entity is entity with table users.
             * We will fetch all users and related user_group_id and language_id
             * as user.relation_user_group_id and user.relation_language_id.
             */
            $belongsToRelation = (new BelongsTo($entity, $relationEntity, $alias))
                ->foreignKey($relation->onField->field)
                ->fill('relation_' . $relation->onField->field)
                ->after(
                    function($record) use ($relation) {
                        $record->setRelation('select_relation_' . $relation->onField->field, $relation);
                    }
                );
            $entity->with($belongsToRelation);
        }

        /**
         * Filter records by $_GET['search']
         */
        $dynamicService->getFilterService()->filterByGet($entity);
        $groups = $dynamicService->getGroupService()->getAppliedGroups();
        $fieldsDataset = new Fields();
        $listableFields = $fieldsDataset->getListableFieldsForTable($tableRecord);
        $fieldTransformations = $fieldsDataset->getFieldsTransformations($listableFields, $entity);

        /**
         * @T00D00
         *  - find out joins / scopes / withs for field type = php
         */
        $records = $entity->count()->all();
        $total = $records->total();

        foreach ($groups as $group) {
            $records = $records->groupBy($group['field']);
        }

        $tabelize = $this->tabelize()
                         ->setTable($tableRecord)
                         ->setTitle($tableRecord->getListTitle())
                         ->setEntity($entity)
                         ->setRecords($records)
                         ->setFields($tableRecord->getFields($listableFields, $dynamicService->getFilterService()))
                         ->setPerPage(get('perPage', 50))
                         ->setPage(1)
                         ->setTotal($total)
                         ->setGroups($groups ? range(1, count($groups)) : [])
                         ->setEntityActions($tableRecord->getEntityActions())
                         ->setRecordActions($tableRecord->getRecordActions())
                         ->setViews($tableRecord->actions()->keyBy('slug'))
                         ->setFieldTransformations($fieldTransformations);

        if ($returnTabelize) {
            return $tabelize;
        }

        if (($this->request()->isAjax() && !get('html')) || get('search')) {
            return [
                'records'   => $tabelize->transformRecords(),
                'groups'    => $groups,
                'paginator' => [
                    'total' => $total,
                    'url'   => router()->getUri() . (get('search') ? '?search=' . get('search') : ''),
                ],
            ];
        }

        $tabelize->getView()->addData(
            [
                'dynamic'   => $dynamicService,
                'viewType'  => $viewType,
                'searchUrl' => router()->getUri(),
                'tab'       => $tab,
            ]
        );

        return $tabelize;
    }

    public function getAddAction(
        Dynamic $form,
        Table $table,
        Record $record,
        Relation $relation = null,
        $foreign = null
    ) {
        if (!$table->listableFields->count()) {
            $this->response()->notFound('Missing view field permissions.');
        }

        $tableEntity = $table->createEntity();
        $record->setEntity($tableEntity);

        if ($foreign && $relation->on_field_id) {
            $record->{$relation->onField->field} = $foreign;
            $form->setForeignFieldId($relation->on_field_id);
            $form->setForeignRecord($relation->onTable->createEntity()->where('id', $foreign)->one());
        }

        $form->setTable($table);
        $form->setRecord($record);
        $form->initFields();

        $form->populateFromRecord($record);

        if ($tableEntity->isTranslatable()) {
            $form->initLanguageFields();
        }

        if ($tableEntity->isPermissionable()) {
            $form->initPermissionFields();
        }

        $formalize = $this->formalize($form, $record, $table->getFormTitle('Add'));

        return view(
            'edit/singular',
            [
                'formalize' => $formalize,
            ]
        );
    }

    public function postAddAction(
        Dynamic $form,
        Table $table,
        Record $record,
        Relation $relation = null,
        $foreign = null
    ) {
        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record = $entity->transformRecordToEntities($record);
        $record->setEntity($entity);

        if ($foreign && $relation->on_field_id) {
            $record->{$relation->onField->field} = $foreign;
            $form->setForeignFieldId($relation->on_field_id);
            $form->setForeignRecord($relation->onTable->createEntity()->where('id', $foreign)->one());
        }

        $form->setTable($table);
        $form->setRecord($record);
        $form->initFields();

        if ($entity->isTranslatable()) {
            $form->initLanguageFields();
        }

        if ($entity->isPermissionable()) {
            $form->initPermissionFields();
        }

        $form->populateFromRequest();
        $form->populateToRecord($record);
        $form->populatePasswords($record);

        if ($record->language_id) {
            $lang = (new Lang())->setLangId($record->language_id);
            $entity->setTranslatableLang($lang);
        }

        $record->save($entity);

        if ($this->post()->p17n) {
            $this->saveP17n($record, $entity);
        }

        Record::$dynamicTable = $table;
        $record::$dynamicTable = $table;

        flash('dynamic.records.add.success', __('dynamic.records.add.success'));

        return $this->response()->respondWithSuccessRedirect($record->getEditUrl());
    }

    public function postCloneAction(Record $record, Table $table)
    {
        $clonedRecord = $record->duplicate($table->createEntity());

        $clonedRecord::$dynamicTable = $table;

        return $this->response()->respondWithSuccess(
            [
                'clonedUrl' => $clonedRecord->getViewUrl(),
            ]
        );
    }

    public function getViewAction(Dynamic $form, Record $record, Table $table)
    {
        $form->setEditable(false);

        return $this->getEditAction($form, $record, $table);
    }

    public function getEditAction(Dynamic $form, Record $record, Table $table)
    {
        $listableFields = $table->listableFields(
            function(HasMany $relation) {
                $relation->withFieldType();
            }
        );
        if (!$listableFields->count()) {
            $this->response()->notFound('Missing view field permissions.');
        }

        $tableEntity = $table->createEntity();

        $dir = path('app_src') . implode(path('ds'), array_slice(explode('\\', get_class($tableEntity)), 0, -2))
               . path('ds') . 'View' . path('ds');
        Twig::addDir($dir);
        /**
         * This is needed for table actions.
         */
        Twig::addDir($dir . 'tabelize' . path('ds') . 'recordActions' . path('ds'));
        Twig::addDir($dir . 'tabelize' . path('ds') . 'entityActions' . path('ds'));

        $record = $tableEntity->transformRecordToEntities($record);

        $form->setTable($table);
        $form->setRecord($record);
        $form->initFields();

        $form->populateFromRecord($record);

        if ($tableEntity->isTranslatable()) {
            $form->initLanguageFields();
        }

        if ($tableEntity->isPermissionable() && $form->isEditable()) {
            $form->initPermissionFields();
        }

        $title = ($form->isEditable() ? 'Edit' : 'View') . ' ' .
                 ($record->title ?? ($record->slug ?? ($record->email ?? ($record->num ?? $table->title))));

        $formalize = $this->formalize($form, $record, $title);

        /**
         * We also have to return related tables.
         */
        $tabs = $table->tabs;
        try {
            list($tabelizes, $functionizes) = $this->getTabelizesAndFunctionizes($tabs, $record, $table, $tableEntity);
        } catch (Throwable $e) {
            $tabelizes = [];
            $functionizes = [];
        }

        $record::$dynamicTable = $table;

        Tab::$dynamicRecord = $record;
        Tab::$dynamicTable = $table;

        $actions = $table->getRecordActions();

        ksort($tabelizes);
        ksort($functionizes);

        $fieldsDataset = new Fields();
        $listableFields = $fieldsDataset->getListableFieldsForTable($table);
        $fieldTransformations = $fieldsDataset->getFieldsTransformations($listableFields, $tableEntity);

        $tabelize = $this->tabelize()
                         ->setTable($table)
                         ->setEntity($tableEntity)
                         ->setEntityActions($table->getEntityActions())
                         ->setRecordActions($table->getRecordActions())
                         ->setViews($table->actions()->keyBy('slug'))
                         ->setFields($listableFields)
                         ->setFieldTransformations($fieldTransformations);

        $data = [
            'formalize'    => $formalize,
            'tabelize'     => $tabelize,
            'tabelizes'    => $tabelizes,
            'functionizes' => $functionizes,
            'record'       => $record,
            'actions'      => $actions,
            'tabs'         => $tabs,
        ];

        return view(
            $tabs->count() ? 'edit/tabs' : 'edit/singular',
            $data
        );
    }

    public function getTabAction(Record $record, Table $table, Tab $tab, \Pckg\Dynamic\Service\Dynamic $dynamicService)
    {
        $relations = $table->hasManyRelation(
            function(HasMany $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
            }
        );
        $table->hasAndBelongsToRelation(
            function(HasAndBelongsTo $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
            }
        )->each(
            function($item) use ($relations) {
                $relations->push($item);
            }
        );
        $table->morphsManyRelation(
            function(MorphsMany $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
            }
        )->each(
            function($item) use ($relations) {
                $relations->push($item);
            }
        );
        $table->morphedByRelation(
            function(MorphedBy $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
            }
        )->each(
            function($item) use ($relations) {
                $relations->push($item);
            }
        );
        $tabelizes = [];
        $relations->each(
            function(Relation $relation) use ($record, &$tabelizes, $dynamicService, $tab) {
                $entity = null;
                $tableId = $relation->over_table_id ?? $relation->show_table_id;
                if ($relation->over_table_id) {
                    $entity = $relation->overTable->createEntity();
                } else {
                    $entity = $relation->showTable->createEntity();
                }

                $entity->setStaticDynamicRecord($record);
                $entity->setStaticDynamicRelation($relation);
                $relation->applyRecordFilterOnEntity($record, $entity);
                $tabelize = $this->getViewTableAction(
                    (new Tables())->where('id', $tableId)->one(),
                    $this->dynamic,
                    $entity,
                    'related',
                    false,
                    $tab
                );

                $tabelizes[] = is_array($tabelize) ? \json_encode($tabelize) : (string)$tabelize;
            }
        );

        $functionizes = [];
        $functions = $table->functions(
            function(HasMany $functions) use ($tab) {
                $functions->where('dynamic_table_tab_id', $tab->id);
            }
        );

        $pluginService = $this->pluginService;
        $args = [$record];
        if ($table->framework_entity) {
            $args[] = $table->createEntity()->where('id', $record->id)->one();
        }
        $functions->each(
            function(Func $function) use (&$functionizes, $pluginService, $record, $args) {
                $functionize = $pluginService->make(
                    $function->class,
                    $function->method,
                    $args
                );

                $functionizes[] = (string)$functionize;
            }
        );

        /**
         * We have to build tab.
         */
        return view(
            'edit/tab',
            [
                'functionizes' => $functionizes,
                'tabelizes'    => $tabelizes,
            ]
        );
    }

    protected function getTabelizesAndFunctionizes($tabs, $record, Table $table, DatabaseEntity $entity)
    {
        $relations = $table->hasManyRelation(
            function(HasMany $query) {
                $query->where('dynamic_relation_type_id', 2);
                $query->where('dynamic_table_tab_id', null);
            }
        );

        $tabelizes = [];
        $recordsController = Reflect::create(Records::class);
        $relations->each(
            function(Relation $relation) use ($tabs, $record, &$tabelizes, $recordsController) {
                $entity = $relation->showTable->createEntity();
                $entity->where($relation->onField->field, $record->id);

                $tableResolver = Reflect::create(\Pckg\Dynamic\Resolver\Table::class);
                $table = $tableResolver->resolve($tableResolver->parametrize($relation->showTable));

                $tabelize = $recordsController->getViewTableAction(
                    $table,
                    $this->dynamic,
                    $entity
                );

                if ($tabs->count()) {
                    $tabelizes[$relation->dynamic_table_tab_id ?? 0][] = (string)$tabelize;
                } else {
                    $tabelizes[] = (string)$tabelize;
                }
            }
        );

        $functionizes = [];
        $functions = $table->functions;
        $pluginService = $this->pluginService;
        $functions->each(
            function(Func $function) use ($tabs, &$functionizes, $pluginService, $record, $table, $entity) {
                $functionize = $pluginService->make(
                    $function->class,
                    $function->method,
                    [$record, $table->fetchFrameworkRecord($record, $entity)]
                );
                if ($tabs->count()) {
                    $functionizes[$function->dynamic_table_tab_id ?? 0][] = (string)$functionize;
                } else {
                    $functionizes[] = (string)$functionize;
                }
            }
        );

        return [$tabelizes, $functionizes];
    }

    public function postEditAction(
        Dynamic $form,
        Record $record,
        Table $table,
        Entity $entity
    ) {
        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record = $entity->transformRecordToEntities($record);
        $record->setEntity($entity);

        $form->setTable($table);

        // @T00D00 - check if we can uncomment this?
        $form->setRecord($record);
        $form->initFields();

        if ($entity->isTranslatable()) {
            $form->initLanguageFields();
        }

        $form->populateFromRequest();
        $form->populateToRecord($record);
        $form->populatePasswords($record);

        if ($record->language_id) {
            $lang = (new Lang())->setLangId($record->language_id);
            $entity->setTranslatableLang($lang);
        }
        $record->save($entity);

        if ($this->post()->p17n) {
            $this->saveP17n($record, $entity);
        }

        flash('dynamic.records.edit.success', __('dynamic.records.edit.success'));

        return $this->response()->respondWithSuccessRedirect();
    }

    protected function saveP17n(Record $record, Entity $entity)
    {
        $p17n = $this->post()->p17n;

        if (isset($p17n['table'])) {
            $entity = (new Entity($entity->getRepository()))->setTable(
                $entity->getTable() . $entity->getPermissionableTableSuffix()
            );
            $entity->where('id', $record->id)->delete();
            foreach ($p17n['table'] as $userGroupId => $permissions) {
                foreach ($permissions as $permissionKey => $one) {
                    $permissionRecord = new Record();
                    $permissionRecord->setEntity($entity);
                    $permissionRecord->setData(
                        [
                            'id'            => $record->id,
                            'user_group_id' => $userGroupId,
                            'action'        => $permissionKey,
                        ]
                    )->insert($entity);
                }
            }
        }

        if (isset($p17n['action'])) {
            $entity = (new Entity($entity->getRepository()))->setTable('dynamic_table_actions_p17n');
            $entity->where(
                'id',
                new Raw('SELECT id FROM dynamic_table_actions WHERE dynamic_table_id = ?', [$record->id])
            )->delete();
            foreach ($p17n['action'] as $userGroupId => $permissions) {
                foreach ($permissions as $actionId => $one) {
                    $permissionRecord = new Record();
                    $permissionRecord->setEntity($entity);
                    $permissionRecord->setData(
                        [
                            'id'            => $actionId,
                            'user_group_id' => $userGroupId,
                            'action'        => 'execute',
                        ]
                    )->insert($entity);
                }
            }
        }
    }

    public function getDeleteAction(Record $record, Table $table)
    {
        $entity = $table->createEntity();
        $record->delete($entity);

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getDeleteTranslationAction(Record $record, Table $table, Language $language)
    {
        $entity = $table->createEntity();
        $record->deleteTranslation($language->slug, $entity);

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getForceDeleteAction(Record $record)
    {
        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record->forceDelete($entity);

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getToggleFieldAction(Table $table, Field $field, Record $record, $state)
    {
        if ($field->fieldType->slug == 'boolean') {
            $record->{$field->field} = $state
                ? 1
                : null;
        } elseif ($field->fieldType->slug == 'datetime') {
            $record->{$field->field} = $state
                ? $field->getMaxTogglableAttribute()
                : $field->getMinTogglableAttribute();
        }

        /**
         * @T00D00 - trigger event
         *         For example, when we change dt_payed, we want to send an email.
         *         Or when we change dt_confirmed, we also want to reset dt_rejected and dt_canceled.
         */

        $record->save($table->createEntity());

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getOrderFieldAction(Table $table, Field $field, Record $record, $order)
    {
        $record->{$field->field} = $order;

        $record->save($table->createEntity());

        return $this->response()->respondWithSuccessRedirect();
    }

    public function postUploadAction(Table $table, Record $record, Field $field)
    {
        $upload = new Upload('file');
        $success = $upload->validateUpload();

        if ($success !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $entity = $table->createEntity();
        $record->setEntity($entity);

        $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
        $upload->save($dir);
        $filename = $upload->getUploadedFilename();

        $record->{$field->field} = $filename;
        $record->save($entity);

        return [
            'success' => 'true',
            'url'     => img($record->{$field->field}, null, true, $dir),
        ];
    }

}