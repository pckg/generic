<?php namespace Pckg\Dynamic\Controller;

use Pckg\Collection;
use Pckg\Concept\Reflect;
use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Query\Raw;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Relation\HasAndBelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\MorphedBy;
use Pckg\Database\Relation\MorphsMany;
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
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View\Twig;
use Pckg\Locale\Lang;
use Pckg\Locale\Record\Language;
use Pckg\Maestro\Helper\Maestro;
use Pckg\Maestro\Service\Tabelize;
use Pckg\Manager\Upload;
use Throwable;

class Records extends Controller
{

    use Maestro;

    /**
     * @var Plugin
     */
    protected $pluginService;

    public function __construct(
        Plugin $pluginService
    ) {
        $this->pluginService = $pluginService;
    }

    public function getSelectListAction(
        Table $table,
        Field $field = null,
        Record $record = null,
        DynamicService $dynamicService
    ) {
        $dynamicService->setTable($table);
        if (!$field) {
            $field = (new Relations())->where('show_table_id', $table->id)->one()->onField;
        }

        $entity = $field->getEntityForSelect($record, null);
        if ($search = get('search')) {
            $dynamicService->getFilterService()->filterByGet($entity);
        }

        $relation = $field->getRelationForSelect($record, null, $entity);

        return $this->response()->respondWithSuccess(
            [
                'records' => $relation,
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
         * Set view.
         */
        $dynamicService->setView($tableView);
        if (get('force')) {
            $tableView->loadToSession();
            $this->response()->redirect();
        } else {
            $tableView->loadToSessionIfNotLoaded();
        }

        return $this->getViewTableAction($tableRecord, $dynamicService, $entity, $viewType, false, null, null, null,
                                         $tableView);
    }

    public function getConfigureTableViewAction(
        Table $tableRecord,
        DynamicService $dynamicService,
        DatabaseEntity $entity = null,
        $viewType = 'full',
        TableView $tableView
    ) {
        $dynamicService->setTable($tableRecord);
        $dynamicService->setView($tableView);
        $this->getViewTableAction($tableRecord, $dynamicService, $entity, $viewType, true);

        $fields = $dynamicService->getFieldsService()->getAvailableFields();
        $relations = $dynamicService->getFieldsService()->getAvailableRelations();

        return [
            'fields'        => $fields,
            'relations'     => $relations,
            'directions'    => $dynamicService->getSortService()->getDirections(),
            'filterMethods' => $dynamicService->getFilterService()->getTypeMethods(),
        ];
    }

    public function postConfigureTableViewAction(
        Table $tableRecord, TableView $tableView = null, DynamicService $dynamicService
    ) {
        $_SESSION['pckg']['dynamic']['view']['table_' . $tableRecord->id . '_' .
                                             ($tableView ? $tableView->id : '')]['view'] = post()->all();

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
        DynamicService $dynamicService,
        DatabaseEntity $entity = null,
        $viewType = 'full',
        $returnTabelize = false,
        Tab $tab = null,
        $dynamicRecord = null,
        $dynamicRelation = null,
        TableView $tableView = null
    ) {
        if ($viewType == 'full') {
            $this->seoManager()->setTitle($tableRecord->title . ' - ' . config('site.title'));
        }

        /**
         * Set table so sub-services can reuse it later.
         */
        $dynamicService->setTable($tableRecord);

        if (!$entity) {
            $entity = $tableRecord->createEntity(null, false);

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
        if ($viewType != 'related') {
            /**
             * Dont activate filters, group bys etc. in tabs.
             */
            $dynamicService->applyOnEntity($entity);
        } else {
            $dynamicService->getSortService()->applyOnEntity($entity);
            $dynamicService->getPaginateService()->applyOnEntity($entity);
        }

        /**
         * Get tabelize class
         */
        if (method_exists($entity, 'selectTabelizeClassField')) {
            $entity->selectTabelizeClassField();
        }

        /**
         * Join extensions.
         */
        $dynamicService->selectScope($entity);

        /**
         * Get all relations for fields with type (select).
         */
        $listableFields = $tableRecord->listableFields;
        $listedFields = $tableRecord->getFields($listableFields, $dynamicService->getFilterService());
        $relations = (new Relations())->withShowTable()
                                      ->withOnField()
                                      ->where('on_table_id', $tableRecord->id)
                                      ->where('dynamic_relation_type_id', 1)
                                      ->where('on_field_id', $listedFields->map('id'))
                                      ->all();

        foreach ($relations as $relation) {
            $relation->loadOnEntity($entity, $dynamicService);
        }

        /**
         * Filter records by $_GET['search']
         */
        $dynamicService->getFilterService()->filterByGet($entity);
        $fieldTransformations = $dynamicService->getFieldsTransformations($entity, $listableFields);

        /**
         * Also, try optimizing php fields. ;-)
         */
        $dynamicService->optimizeSelectedFields($entity, $listedFields);

        /**
         * @T00D00
         *  - find out joins / scopes / with for field type = php and mysql
         */

        $groups = $dynamicService->getGroupService()->getAppliedGroups();
        if ($groups) {
            $entity->addCount();
            $listedFields->push(['field' => 'count', 'title' => 'Count', 'type' => 'text']);

            if ($tableRecord->id == 26) {
                $entity->addSelect(['sumPrice' => 'SUM(orders_bills.price)', 'sumPayed' => 'SUM(orders_bills.payed)']);
                $listedFields->push(['field' => 'sumPrice', 'title' => 'Sum price', 'type' => 'decimal']);
                $listedFields->push(['field' => 'sumPayed', 'title' => 'Sum payed', 'type' => 'decimal']);
            } elseif ($tableRecord->id == 76) {
                $entity->addSelect(['sumPrice' => 'SUM(payments.price)']);
                $listedFields->push(['field' => 'sumPrice', 'title' => 'Sum price', 'type' => 'decimal']);
            }
        }

        /**
         * Temp test.
         */
        $records = $entity->count()->all();
        $total = $records->total();

        $tabelize = $this->tabelize()
                         ->setTable($tableRecord)
                         ->setTitle($tableRecord->getListTitle())
                         ->setEntity($entity)
                         ->setRecords($records)
                         ->setFields($listedFields)
                         ->setPerPage(get('perPage', 50))
                         ->setPage(1)
                         ->setTotal($total)
                         ->setEntityActions($tableRecord->getEntityActions())
                         ->setRecordActions($tableRecord->getRecordActions())
                         ->setViews($tableRecord->actions()->keyBy('slug'))
                         ->setFieldTransformations($fieldTransformations)
                         ->setDynamicRecord($dynamicRecord)
                         ->setDynamicRelation($dynamicRelation)
                         ->setViewData([
                                           'view' => $dynamicService->getView(),
                                       ])
                         ->setTableView($tableView);

        if ($returnTabelize) {
            return $tabelize;
        }

        if (($this->request()->isAjax() && !get('html')) || get('search')) {
            return [
                'records'   => $tabelize->transformRecords(),
                'groups'    => [],
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
        Record $record = null,
        Relation $relation = null,
        DatabaseRecord $foreign = null
    ) {
        if (!$table->listableFields->count()) {
            $this->response()->notFound('Missing view field permissions.');
        }

        $tableEntity = $table->createEntity();
        $record = $record ? $tableEntity->transformRecordToEntities($record) : $tableEntity->getRecord();
        $record->setEntity($tableEntity);

        $form->setRelation($relation);

        if ($foreign && $relation->on_field_id) {
            $record->{$relation->onField->field} = $foreign->id;
            $form->setForeignFieldId($relation->on_field_id);
            $form->setForeignRecord($relation->onTable->createEntity()->where('id', $foreign->id)->one());
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

    /**
     * @param Dynamic             $form     - resolved by injection
     * @param Table               $table    - resolved from url
     * @param Record              $record
     * @param Relation|null       $relation - resolved from url
     * @param DatabaseRecord|null $foreign  - resolved from url
     *
     * @return \Pckg\Framework\Response
     */
    public function postAddAction(
        Dynamic $form,
        Table $table,
        Record $record = null,
        Relation $relation = null,
        DatabaseRecord $foreign = null
    ) {
        $entity = $table->createEntity();
        $record = $record ? $entity->transformRecordToEntities($record) : $entity->getRecord();
        $record->setEntity($entity);

        if ($foreign && $relation->on_field_id) {
            $record->{$relation->onField->field} = $foreign->id;
            $form->setForeignFieldId($relation->on_field_id);
            $form->setForeignRecord($relation->onTable->createEntity()->where('id', $foreign->id)->one());
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
            $lang = (new Lang($record->language_id));
            $entity->setTranslatableLang($lang);
        }

        $newRecord = null;
        foreach ($_SESSION[Records::class]['upload'] ?? [] as $i => $uploadedData) {
            if ($uploadedData['_relation'] != $relation->id) {
                continue;
            }
            $data = array_merge($uploadedData, $record->data());
            unset($data['id']);
            $newRecord = new $record($data);
            $newRecord->save($entity);
            unset($_SESSION[Records::class]['upload'][$i]);
        }

        if (!$newRecord) {
            $record->save($entity);

            if ($this->post()->p17n) {
                $this->saveP17n($record, $entity);
            }
        } else {
            flash('dynamic.records.upload.success', __('dynamic.records.upload.success'));
        }

        $url = url('dynamic.record.edit', [
            'table'  => $table,
            'record' => $newRecord ?? $record,
        ]);

        if ($relation && $foreign) {
            $url = url('dynamic.record.edit.foreign', [
                'table'    => $table,
                'record'   => $newRecord ?? $record,
                'relation' => $relation,
                'foreign'  => $foreign,
            ]);
        }

        return $this->response()
                    ->respondWithSuccess([
                                             'message'  => __('dynamic.records.add.success'),
                                             'redirect' => $url,
                                         ]);
    }

    public function postCloneAction(Record $record, Table $table)
    {
        $clones = between(post('clones'), 1, 99);

        while ($clones > 0) {
            $clonedRecord = $record->duplicate($table->createEntity());
            $clones--;
        }

        return $this->response()->respondWithSuccess(
            [
                'clonedUrl' => url('dynamic.record.edit', [
                    'table'  => $table,
                    'record' => $clonedRecord,
                ]),
            ]
        );
    }

    public function getViewAction(Dynamic $form, Record $record, Table $table, DynamicService $dynamic)
    {
        $form->setEditable(false);

        return $this->getEditAction($form, $record, $table, $dynamic);
    }

    public function getEditAction(Dynamic $form, Record $record, Table $table, DynamicService $dynamicService)
    {
        $this->seoManager()->setTitle(($form->isEditable() ? 'Edit' : 'View') . ' ' . $table->title . ' #' .
                                      $record->id . ' - ' . config('site.title'));

        $listableFields = $table->listableFields;
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

        $formalize = $this->formalize($form, $record, $title)
                          ->setTable($table);

        /**
         * We also have to return related tables.
         */
        $tabs = $table->tabs;
        try {
            list($tabelizes, $functionizes) = $this->getTabelizesAndFunctionizes($tabs, $record, $table, $tableEntity);
        } catch (Throwable $e) {
            if (!prod()) {
                throw $e;
            }
            $tabelizes = [];
            $functionizes = [];
        }

        $actions = $table->getRecordActions();

        ksort($tabelizes);
        ksort($functionizes);

        $listableFields = $table->listableFields;
        $fieldTransformations = $dynamicService->getFieldsTransformations($tableEntity, $listableFields);

        $tabelize = $this->tabelize()
                         ->setTable($table)
                         ->setEntity($tableEntity)
                         ->setEntityActions($table->getEntityActions())
                         ->setRecordActions($table->getRecordActions())
                         ->setViews($table->actions()->keyBy('slug'))
                         ->setFields($listableFields)
                         ->setFieldTransformations($fieldTransformations)
                         ->setDynamicRecord($record);

        $this->vueManager()
             ->addView('Pckg/Maestro:_pckg_chart')
             ->addView('Pckg/Maestro:_pckg_maestro_actions_template',
                       [
                           'recordActions' => $actions,
                           'table'         => $table->table,
                       ]
             )
             ->addView('Pckg/Maestro:_pckg_maestro_actions',
                       [
                           'recordActions' => $actions,
                       ]
             )
             ->addView('Pckg/Maestro:_pckg_maestro_actions_custom',
                       [
                           'table' => $table->table,
                       ]
             )
             ->addView('Pckg/Maestro:_pckg_dynamic_record_tabs',
                       [
                           'tabelize'     => $tabelize,
                           'formalize'    => $formalize,
                           'tabs'         => $tabs,
                           'table'        => $table->table,
                           'tabelizes'    => $tabelizes,
                           'functionizes' => $functionizes,
                           'record'       => $record,
                       ]
             );

        return view('edit/tabs', ['tabelize' => $tabelize]);
    }

    public function getTabAction(Record $record, Table $table, Tab $tab)
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
            function(Relation $relation) use ($record, &$tabelizes, $tab) {
                $entity = null;
                $tableId = $relation->over_table_id ?? $relation->show_table_id;
                if ($relation->over_table_id) {
                    $entity = $relation->overTable->createEntity();
                } else {
                    $entity = $relation->showTable->createEntity();
                }

                $dynamicService = Reflect::create(DynamicService::class);
                $relation->applyRecordFilterOnEntity($record, $entity);
                $tabelize = $this->getViewTableAction(
                    (new Tables())->where('id', $tableId)->one(),
                    $dynamicService,
                    $entity,
                    'related',
                    false,
                    $tab,
                    $record,
                    $relation
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

    protected function getTabelizesAndFunctionizes(
        $tabs, $record, Table $table, DatabaseEntity $entity
    ) {
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

                $dynamicService = Reflect::create(DynamicService::class);
                $tabelize = $recordsController->getViewTableAction(
                    $table,
                    $dynamicService,
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
        if (post('as_new')) {
            $record = $record->saveAs();
        } else {
            $record->save($entity);
        }

        if ($this->post()->p17n) {
            $this->saveP17n($record, $entity);
        }

        return $this->response()
                    ->respondWithSuccess([
                                             'message'  => __('dynamic.records.edit.success'),
                                             'redirect' => post('as_new') ? url('dynamic.record.edit', [
                                                 'table'  => $table,
                                                 'record' => $record,
                                             ]) : null,
                                         ]);
    }

    protected function saveP17n(
        Record $record, Entity $entity
    ) {
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

    public function getDeleteAction(
        Record $record, Table $table
    ) {
        $entity = $table->createEntity();
        $record->delete($entity);

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getDeleteTranslationAction(
        Record $record, Table $table, Language $language
    ) {
        $entity = $table->createEntity();
        $record->deleteTranslation($language->slug, $entity);

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getForceDeleteAction(
        Record $record
    ) {
        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record->forceDelete($entity);

        return $this->response()->respondWithSuccessRedirect();
    }

    public function getToggleFieldAction(
        Table $table, Field $field, Record $record, $state
    ) {
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

    public function getOrderFieldAction(
        Table $table, Field $field, Record $record, $order
    ) {
        $record->{$field->field} = $order;

        $record->save($table->createEntity());

        return $this->response()->respondWithSuccessRedirect();
    }

    public function deleteUploadAction(Table $table, Record $record = null, Field $field)
    {
        $this->processDelete($table, $record, $field, '');
    }

    public function deleteUploadNewAction(Table $table, Field $field)
    {
        $this->processDelete($table, null, $field, '');
    }

    public function deleteUploadNewForeignAction(Table $table, Field $field, Record $record, Relation $relation)
    {
        $this->processDelete($table, $record, $field, '');
    }

    protected function processDelete(Table $table, Record $record = null, Field $field, $filename)
    {
        $entity = $table->createEntity();
        $record->setEntity($entity);
        $record->{$field->field} = $filename;
        $record->save($entity);

        return [
            'success' => true,
        ];
    }

    public function postUploadAction(Table $table, Record $record = null, Field $field)
    {
        return $this->processUpload($table, $record, $field);
    }

    public function postUploadNewAction(Table $table, Field $field)
    {
        return $this->processUpload($table, null, $field);
    }

    public function postUploadNewForeignAction(Table $table, Field $field, Record $record, Relation $relation)
    {
        return $this->processUpload($table, null, $field, $relation, $record);
    }

    protected function processUpload(
        Table $table, Record $record = null, Field $field, Relation $relation = null, Record $foreignRecord = null
    ) {
        $upload = new Upload('file');
        $success = $upload->validateUpload();

        if ($success !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $dir = $field->getAbsoluteDir(
            $field->getSetting('pckg.dynamic.field.dir'),
            $field->getSetting('pckg.dynamic.field.privateUpload')
        );
        $upload->save($dir);
        $filename = $upload->getUploadedFilename();

        $entity = $table->createEntity();
        if (!$record) {
            $_SESSION[Records::class]['upload'][] = [
                '_relation'                     => $relation->id,
                $relation->onField->field ?? '' => $foreignRecord ?? $foreignRecord->id,
                $field->field                   => $filename,
            ];
        } else {
            $record->setEntity($entity);
            $record->{$field->field} = $filename;
            $record->save($entity);
        }

        return [
            'success' => true,
            'url'     => img($filename, null, true, $dir),
        ];
    }

    public function postEditorUploadAction()
    {
        $upload = new Upload('file');
        $success = $upload->validateUpload();

        if ($success !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $dir = path('app_uploads') . 'editor' . path('ds');
        $upload->save($dir);
        $filename = $upload->getUploadedFilename();

        return [
            'success' => true,
            'url'     => img($filename, null, true, $dir),
        ];
    }

    public function deleteDeleteViewAction(TableView $tableView)
    {
        $tableView->delete();

        return $this->response()->respondWithSuccess();
    }

}