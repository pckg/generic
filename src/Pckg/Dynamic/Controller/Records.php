<?php

namespace Pckg\Dynamic\Controller;

use Pckg\Concept\Reflect;
use Pckg\Database\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Query\Raw;
use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Entity\TableActions;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Form\Dynamic;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Func;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\Tab;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableView;
use Pckg\Dynamic\Service\Dynamic as DynamicService;
use Pckg\Framework\Controller;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View\Twig;
use Pckg\Generic\Record\Setting;
use Pckg\Generic\Service\Generic;
use Pckg\Htmlbuilder\Datasource\Method\Request;
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

    public function __construct(Plugin $pluginService)
    {
        $this->pluginService = $pluginService;
    }

    public function postSwitchLanguageAction()
    {
        $language = post('language');
        $_SESSION['pckg_dynamic_lang_id'] = $language;
        return $this->response()->respondWithSuccess();
    }

    public function getSelectListAction(Table $table, Field $field = null, Record $record = null, DynamicService $dynamicService)
    {
        $dynamicService->setTable($table);
        if (!$field) {
            $field = (new Relations())->where('show_table_id', $table->id)->one()->onField;
        }

        $entity = $field->getEntityForSelect($record, null);
        $relation = (new Relations())->where('on_field_id', $field->id)->one();
        $relations = (new Relations())->withShowTable()
                                      ->withOnField()
                                      ->withForeignField()
                                      ->where('on_table_id', $relation->show_table_id)
                                      ->where('dynamic_relation_type_id', 1)
                                      ->all();
        foreach ($relations as $relation) {
            $relation->loadOnEntity($entity, $dynamicService);
        }

        if ($search = get('search')) {
            $dynamicService->getFilterService()->filterByGet($entity, $relations);
        }

        $relation = $field->getRelationForSelect($record, null, $entity);
        return [
            'records' => $relation,
        ];
    }

    public function getViewTableViewAction(Table $tableRecord, DynamicService $dynamicService, Entity $entity = null, TableView $tableView, $viewType = 'full')
    {
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

        return $this->getViewTableAction(
            $tableRecord,
            $dynamicService,
            $entity,
            $viewType,
            false,
            null,
            null,
            null,
            $tableView
        );
    }

    public function getConfigureTableViewAction(Table $tableRecord, DynamicService $dynamicService, Entity $entity = null, $viewType = 'full', TableView $tableView = null)
    {
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

    public function postConfigureTableViewAction(Table $tableRecord, TableView $tableView = null, DynamicService $dynamicService)
    {
        $_SESSION['pckg']['dynamic']['view']['table_' . $tableRecord->id . '_' .
        ($tableView ? $tableView->id : '')]['view'] = post()->all();
        return [
            'message' => 'ok',
            'data'    => post()->all(),
        ];
    }

    public function searchViewTableAction(Table $tableRecord, DynamicService $dynamicService, Entity $entity = null, $viewType = 'full', $returnTabelize = false, Tab $tab = null, $dynamicRecord = null, $dynamicRelation = null, TableView $tableView = null)
    {
        return $this->getViewTableApiAction(
            $tableRecord,
            $dynamicService,
            $entity,
            $viewType,
            $returnTabelize,
            $tab,
            $dynamicRecord,
            $dynamicRelation,
            $tableView
        );
        return [
            'records' => [],
        ];
    }

    /**
     * List table records.
     * This action will then first fetch fields and relations.
     * Then it will load records and paginator.
     *
     * @param Table  $tableRecord
     * @param Entity $entity
     *
     * @return $this|Tabelize
     */
    public function getViewTableAction(Table $tableRecord, DynamicService $dynamicService, Entity $entity = null, $viewType = 'full', $returnTabelize = false, Tab $tab = null, $dynamicRecord = null, $dynamicRelation = null, TableView $tableView = null)
    {
        return '<pckg-maestro-table :table-id="' . $tableRecord->id . '"' .
            ($dynamicRelation ? ' :relation-id="' . $dynamicRelation->id . '"' : '') .
            ($dynamicRecord ? ' :record-id="' . $dynamicRecord->id . '"' : '') . '></pckg-maestro-table>';
    }

    public function getViewTableApiAction(Table $tableRecord, DynamicService $dynamicService, Entity $entity = null, $viewType = 'full', $returnTabelize = false, Tab $tab = null, Record $record = null, Relation $relation = null, TableView $tableView = null)
    {
        $executor = function () use ($tableRecord, $dynamicService, $entity, $viewType, $tab, $record, $relation, $tableView) {

            /**
             * Set table so sub-services can reuse it later.
             */
            $dynamicService->setTable($tableRecord);
            $entity = $tableRecord->loadTwigDirsForEntity($entity, $dynamicService);
            /**
             * Get all relations for fields with type (select).
             */
            $listableFields = $tableRecord->listableFields;
            $listedFields = $tableRecord->getFields($listableFields, $dynamicService->getFilterService());
            /**
             * @T00D00
             *  - find out joins / scopes / with for field type = php and mysql
             */

            $groups = $dynamicService->getGroupService()->getAppliedGroups();
            if (method_exists($entity, 'isTranslatable') && $entity->isTranslatable()) {
                $entity->groupBy('`' . $entity->getTable() . '`.`id`, `' . $entity->getTable() . '_i18n`.`language_id`');
            } else {
                $entity->groupBy('`' . $entity->getTable() . '`.`id`');
            }

            /**
             * Allow extensions.
             */
            trigger(get_class($entity) . '.applyOnEntity', [$entity, 'listableFields' => $listedFields, collect()]);
            $tabelize = $this->tabelize()
                             ->setTable($tableRecord)
                             ->setTitle($tableRecord->getListTitle())
                             ->setEntity($entity)
                             ->setRecords(new Collection())
                             ->setFields($listedFields)
                             ->setPerPage(get('perPage', 50))
                             ->setPage(1)
                             ->setTotal($entity->total())
                             ->setEntityActions($tableRecord->getEntityActions())
                             ->setRecordActions($tableRecord->getRecordActions())
                             ->setViews($tableRecord->actions()->keyBy('slug'))
                             ->setFieldTransformations([])
                             ->setDynamicRecord($record)
                             ->setDynamicRelation($relation)
                             ->setViewData([
                                               'view' => $dynamicService->getView(),
                                           ])
                             ->setTableView($tableView);
            $tabelize->getView()->addData([
                                              'dynamic'   => $dynamicService,
                                              'viewType'  => $viewType,
                                              'searchUrl' => router()->getUri(),
                                              'tab'       => $tab,
                                          ]);
            $columns = $tableRecord->fields->filter(function (Field $field) {

                return $field->visible;
            })->map(function (Field $field) {

                $f['field'] = $field->field;
                $f['freeze'] = false;
                return $f;
            })->rekey()->toArray();
            $filters = [];
            return [
                'actions'   => [
                    'entity' => $tabelize->getEntityActionsArray(false),
                    'record' => $tabelize->getRecordActionsArray(),
                ],
                'table'     => $tableRecord,
                'tabs' => $tableRecord->tabs,
                'fields'    => $tableRecord->fields->map(function (Field $field) {

                    $data = $field->toArray();
                    $options = [];
                    if ($field->fieldType->slug === 'picture') {
                        $options = [
                            'dir' => $field->getSetting('pckg.dynamic.field.dir', ''),
                        ];
                    }
                    $data['options'] = $options;
                    $data['type'] = $field->fieldType->slug ?? null;
                    return $data;
                }),
                'relations' => $tableRecord->relations,
                'view'      => [
                    'columns' => $columns,
                    'filters' => $filters,
                ],
                'views'     => $tabelize->getSavedViews(),
            ];
        };
        $this->response()->sendCacheHeaders(1);

        return cache(
            Records::class . '.getViewTableApiAction.' . $tableRecord->id . '.' . $viewType .
                     ($record ? '.record-' . $record->id : '') . ($relation ? '.relation-' . $relation->id : ''),
            $executor,
            'app',
            1
        );
    }

    public function getAddAction(
        Dynamic $form,
        Table $table,
        Record $record = null,
        Relation $relation = null,
        Record $foreign = null
    ) {
        return component('dynamic-singular', [
            ':table' => $table,
            ':record' => $record,
            ':actions' => [],
        ]);
    }

    /**
     * @param Dynamic       $form     - resolved by injection
     * @param Table         $table    - resolved from url
     * @param Record        $record
     * @param Relation|null $relation - resolved from url
     * @param Record|null   $foreign  - resolved from url
     *
     * @return \Pckg\Framework\Response
     */
    public function postAddAction(Dynamic $form, Table $table, Record $record = null, Relation $relation = null, Record $foreign = null)
    {
        (new Tables())->joinPermissionTo('write')
                      ->where('id', $table->id)
                      ->oneOrFail(function () {

                          $this->response()->unauthorized();
                      });
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

        /**
         * Populate from session?
         */
        $newRecord = null;
        $sessionUpload = $_SESSION[Records::class]['upload'] ?? [];
        foreach ($sessionUpload as $i => $uploadedData) {
            /**
             * Skip data from other relations.
             */
            if ($uploadedData['_relation'] != $relation->id) {
                continue;
            }

            /**
             * This is usually url or picture field?
             */
            $field = collect((new Request())->setElement($form)->getElements())->first(function ($e) use ($uploadedData) {
                return $e->getName() === $uploadedData['_field'];
            });
            if (!$field) {
                throw new \Exception('No field to save');
            }
            /**
             * Set to validate.
             */
            $field->setValue($uploadedData[$uploadedData['_field']]);
        }

        if (!$form->isValid($errors, $descriptions)) {
            return response()->code(422)->respond([
                                                      'error'        => true,
                                                      'success'      => false,
                                                      'errors'       => $errors,
                                                      'descriptions' => $descriptions,
                                                  ]);
        }

        $form->populateToRecord($record);
        $form->populatePasswords($record);
        if ($record->language_id) {
            $lang = (new Lang($record->language_id));
            $entity->setTranslatableLang($lang);
        }

        $newRecord = null;
        foreach ($sessionUpload as $i => $uploadedData) {
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

        return $this->response()->respondWithSuccess([
            'message' => __('dynamic.records.add.success'),
            'redirect' => $url,
            'record' => $newRecord ?? $record,
        ]);
    }

    public function postCloneAction(Record $record, Table $table)
    {
        $clones = between(post('clones'), 1, 99);
        while ($clones > 0) {
            $clonedRecord = $record->duplicate($table->createEntity());
            $clones--;
        }

        return $this->response()->respondWithSuccess([
                                                         'clonedUrl' => url('dynamic.record.edit', [
                                                             'table'  => $table,
                                                             'record' => $clonedRecord ?? null,
                                                         ]),
                                                     ]);
    }

    public function getViewAction(Dynamic $form, Record $record, Table $table, DynamicService $dynamic)
    {
        $form->setEditable(false);
        return $this->getEditAction($form, $record, $table, $dynamic, 'view');
    }

    public function getEditAction(Dynamic $form, Record $record, Table $table, $mode = 'edit')
    {
        // $this->seoManager()->setTitle(($form->isEditable() ? 'Edit' : 'View') . ' ' . $table->title . ' #' . $record->id . ' - ' . config('site.title'));

        $tableEntity = $table->createEntity();

        $dir = path('app_src') . implode(path('ds'), array_slice(explode('\\', get_class($tableEntity)), 0, -2)) . path('ds') . 'View' . path('ds');

        Twig::addDir($dir);
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
        $formalize = $this->formalize($form, $record, $title)->setTable($table);
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

        $tabelize = $table->getTabelize($tableEntity)->setDynamicRecord($record);

        $relations = (new Relations())->where('on_table_id', $table->id)
            ->where('dynamic_table_tab_id', null, 'IS NOT')
            ->all();

        if (strpos(router()->getUri(), '/api/http-ql') !== false) {
            $tabelize->setEnriched(false);
        }

        /**
         * Resolve record for the frontend.
         * @var $generic Generic
         */
        return ([
        //return component('pckg-dynamic-record-tabs', [
            'mappedRecord' => $tabelize->transformRecord($record),
            //'table' => $table,
            'actions' => $tabelize->getActionsArray(),
            'tabs' => $tabs,
            'relations' => $relations,
            'mode' => $form->isEditable() ? 'edit' : 'view',
        ]);
    }

    public function getTabAction(Record $record, Table $table, Tab $tab)
    {
        $relations = (new Relations())->where('on_table_id', $table->id)
                                      ->where('dynamic_table_tab_id', $tab->id)
                                      ->all();
        $tabelizes = [];
        $relations->each(function (Relation $relation) use ($record, &$tabelizes, $tab) {

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
            $tabelizes[] = $tabelize;
        });
        $functionizes = [];
        $functions = $table->functions(function (HasMany $functions) use ($tab) {

            $functions->where('dynamic_table_tab_id', $tab->id);
        });
        $pluginService = $this->pluginService;
        $args = [$record];
        if ($table->framework_entity) {
            $args[] = $table->createEntity()->where('id', $record->id)->one();
        }
        $functions->each(function (Func $function) use (&$functionizes, $pluginService, $args) {
            /**
             * This is where a controller is called.
             */
            $functionize = $pluginService->make($function->class, $function->method, $args);
            $functionizes[] = (string)$functionize;
        });
        /*if (!get('html') && (request()->isAjax() || $this->request()->isJson())) {
            return [
                'functionizes' => $functionizes,
                'tabelizes'    => $tabelizes,
            ];
        }*/

        /**
         * We have to build tab.
         */
        return [
        //return view('edit/tab', [
            'functionizes' => $functionizes,
            'tabelizes'    => $tabelizes,
        ];
    }

    protected function getTabelizesAndFunctionizes($tabs, $record, Table $table, Entity $entity)
    {
        $relations = $table->hasManyRelation(function (HasMany $query) {

            $query->where('dynamic_relation_type_id', 2);
            $query->where('dynamic_table_tab_id', null);
        });
        $tabelizes = [];
        $recordsController = Reflect::create(Records::class);
        $relations->each(function (Relation $relation) use ($tabs, $record, &$tabelizes, $recordsController) {

            $entity = $relation->showTable->createEntity();
            $entity->where($relation->onField->field, $record->id);
            $tableResolver = Reflect::create(\Pckg\Dynamic\Resolver\Table::class);
            $table = $tableResolver->resolve($tableResolver->parametrize($relation->showTable));
            $dynamicService = Reflect::create(DynamicService::class);
            $tabelize = $recordsController->getViewTableAction($table, $dynamicService, $entity);
            if ($tabs->count()) {
                $tabelizes[$relation->dynamic_table_tab_id ?? 0][] = (string)$tabelize;
            } else {
                $tabelizes[] = (string)$tabelize;
            }
        });
        $functionizes = [];
        $functions = $table->functions;
        $pluginService = $this->pluginService;
        $functions->each(function (Func $function) use ($tabs, &$functionizes, $pluginService, $record, $table, $entity) {

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
        });
        return [$tabelizes, $functionizes];
    }

    public function patchEditAction(Dynamic $form, Record $record, Table $table, Entity $entity)
    {
        return $this->postEditAction($form, $record, $table, $entity);
    }

    public function postEditAction(Dynamic $form, Record $record, Table $table, Entity $entity)
    {
        (new TableActions())->joinPermissionTo('execute')
                            ->where('dynamic_table_id', $table->id)
                            ->where('slug', 'edit')
                            ->oneOrFail(function () {
                                $this->response()->unauthorized();
                            });

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
        if (!$form->isValid($errors, $descriptions)) {
            return response()->code(422)->respond([
                                                                'error'        => true,
                                                                'success'      => false,
                                                                'errors'       => $errors,
                                                                'descriptions' => $descriptions,
                                                            ]);
        }

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

        return $this->response()->respondWithSuccess([
                                                         'message'  => __('dynamic.records.edit.success'),
                                                         'redirect' => post('as_new') ? url('dynamic.record.edit', [
                                                             'table'  => $table,
                                                             'record' => $record,
                                                         ]) : null,
            'record' => $record,
                                                     ]);
    }

    /**
     * Patches the selection of records with selected value.
     *
     * @param Table $table
     * @param Field $field
     * @return bool[]
     */
    public function postBulkEditAction(Table $table, Field $field)
    {
        $total = (int)post('confirmTotal');
        if (!$total) {
            throw new \Exception('Total is required');
        }

        $posted = post()->all();
        if (!array_key_exists($field->field, $posted)) {
            throw new \Exception('Missing field data');
        }

        $entity = $table->createEntity();
        if ($entity->isDeletable()) {
            $entity->nonDeleted();
        }

        $ids = $posted['ids'] ?? null;
        $filters = $posted['appliedFilters'] ?? null;
        if ($ids) {
            // apply only on ids
            $entity->where('id', $ids);
        } else if ($filters) {
            $dynamicService = resolve(\Pckg\Dynamic\Service\Dynamic::class);
            $dynamicService->setTable($table);
            $dynamicService->getFilterService()->applyOnEntity($entity, $filters);
            // apply filters
        } else {
            // change all?
        }

        $entityTotal = (clone $entity)->total();
        if ($entityTotal !== $total) {
            throw new \Exception('Total does not match - is ' . $entityTotal);
        }

        $newValue = $posted[$field->field];
        $entity->set([
            $field->field => $newValue,
        ])
            ->update();

        return [
            'success' => true,
            'table' => $table->table,
            'field' => $field->field,
            'value' => $newValue,
        ];
    }

    protected function saveP17n(Record $record, Entity $entity)
    {
        $p17n = $this->post()->p17n;
        if (isset($p17n['table'])) {
            $entity = (new Entity($entity->getRepository()))->setTable($entity->getTable() .
                                                                       $entity->getPermissionableTableSuffix());
            $entity->where('id', $record->id)->delete();
            foreach ($p17n['table'] as $userGroupId => $permissions) {
                foreach ($permissions as $permissionKey => $one) {
                    $permissionRecord = new Record();
                    $permissionRecord->setEntity($entity);
                    $permissionRecord->setData([
                                                   'id'            => $record->id,
                                                   'user_group_id' => $userGroupId,
                                                   'action'        => $permissionKey,
                                               ])->insert($entity);
                }
            }
        }

        if (isset($p17n['action'])) {
            $entity = (new Entity($entity->getRepository()))->setTable('dynamic_table_actions_p17n');
            $entity->where(
                'id',
                new Raw('SELECT id FROM dynamic_table_actions WHERE dynamic_table_id = ?', [$record->id])
            )
                   ->delete();
            foreach ($p17n['action'] as $userGroupId => $permissions) {
                foreach ($permissions as $actionId => $one) {
                    $permissionRecord = new Record();
                    $permissionRecord->setEntity($entity);
                    $permissionRecord->setData([
                                                   'id'            => $actionId,
                                                   'user_group_id' => $userGroupId,
                                                   'action'        => 'execute',
                                               ])->insert($entity);
                }
            }
        }
    }

    public function deleteDeleteAction(Record $record, Table $table)
    {
        (new TableActions())->joinPermissionTo('execute')
                            ->where('dynamic_table_id', $table->id)
                            ->where('slug', 'delete')
                            ->oneOrFail(function () {

                                $this->response()->unauthorized();
                            });
        $entity = $table->createEntity();
        $record->delete($entity);
        return $this->response()->respondWithSuccessRedirect();
    }

    public function deleteDeleteTranslationAction(Record $record, Table $table, Language $language)
    {
        $entity = $table->createEntity();
        $record->deleteTranslation($language->slug, $entity);
        return $this->response()->respondWithSuccessRedirect();
    }

    public function deleteForceDeleteAction(Record $record)
    {
        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record->forceDelete($entity);
        return $this->response()->respondWithSuccessRedirect();
    }

    public function getToggleFieldAction(Table $table, Field $field, Record $record, $state)
    {
        if ($field->fieldType->slug == 'boolean') {
            $record->{$field->field} = $state ? 1 : null;
        } elseif ($field->fieldType->slug == 'datetime') {
            $record->{$field->field} = $state ? $field->getMaxTogglableAttribute() : $field->getMinTogglableAttribute();
        }

        /**
         * @T00D00 - trigger event
         *         For example, when we change dt_payed, we want to send an email.
         *         Or when we change dt_confirmed, we also want to reset dt_rejected and dt_canceled.
         */

        $record->save($table->createEntity());
        return $this->response()->respondWithSuccessRedirect();
    }

    public function postOrderFieldAction(Table $table, Field $field, Record $record, $order)
    {
        $record->{$field->field} = $order;
        $record->save($table->createEntity());

        return ['success' => true];
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

    protected function processUpload(Table $table, Record $record = null, Field $field, Relation $relation = null, Record $foreignRecord = null)
    {
        $upload = new Upload();
        if (($message = $upload->validateUpload()) !== true) {
            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $dir = $field->getAbsoluteDir(
            $field->getSetting('pckg.dynamic.field.dir'),
            $field->getSetting('pckg.dynamic.field.privateUpload')
        );
        $infoUpload = new Upload('info');
        $finalDestination = null;
        if ($infoUpload->validateUpload() === true) {
            $finalDestination = json_decode(file_get_contents($infoUpload->getFile()['tmp_name']))->final ?? null;
        }

        try {
            $upload->save($dir, $finalDestination);
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => exception($e),
            ];
        }
        $filename = $upload->getUploadedFilename();
        $entity = $table->createEntity();
        if (!$record) {
            /**
             * Redis issue: locking sessions
             */
            $_SESSION[Records::class]['upload'][] = [
                '_relation'                     => $relation->id,
                '_field' => $field->field,
                $relation->onField->field ?? '' => $foreignRecord ? $foreignRecord->id : null,
                $field->field                   => $filename,
            ];
        } else {
            $record->setEntity($entity)->setAndSave([$field->field => $filename]);
        }

        return [
            'success' => true,
            'url'     => img($filename, null, true, $dir),
            'filename' => $filename,
            'total'   => count($_SESSION[Records::class]['upload']),
        ];
    }

    public function postEditorUploadAction()
    {
        $upload = new Upload();
        if (($message = $upload->validateUpload()) !== true) {
            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $dir = path('uploads') . 'editor' . path('ds');
        $upload->save($dir);
        $filename = $upload->getUploadedFilename();
        $location = img($filename, null, true, $dir);
        return [
            'success' => true,
            'url'     => $location,
            'location' => $location, // for tinymce
        ];
    }

    public function deleteDeleteViewAction(TableView $tableView)
    {
        $tableView->delete();
        return $this->response()->respondWithSuccess();
    }

    public function getTableActionsAction(Table $table)
    {
        $tabelize = new Tabelize();
        $tabelize->setEntityActions($table->getEntityActions(true));
        return [
            'template' => view('Pckg/Maestro:_table_actions', [
                'tabelize' => $tabelize,
            ]),
        ];
    }

    public function getViewFormApiAction(Table $table)
    {
        return $this->getViewFormApiRecordAction($table);
    }

    public function getViewFormApiRecordAction(Table $table, Record $record = null)
    {
        $fields = $table->fields;
        $vueTypeMap = [
            'boolean' => 'toggle',
            'decimal' => 'number',
            'select'  => 'select:single',
            'order'   => 'number',
            'integer' => 'number',
            'slug'    => 'text',
            'picture' => 'file:picture',
        ];
        $typeMapper = function (Field $field) use ($vueTypeMap) {

            if (array_key_exists($field->fieldType->slug, $vueTypeMap)) {
                return $vueTypeMap[$field->fieldType->slug];
            }

            return $field->fieldType->slug;
        };

        // select options for bulk edit requests
        if (!$record) {
            context()->bind(Dynamic::class . ':fullFields', true);
        }

        $formObject = (new Dynamic())->setTable($table)->setRecord($record)->initFields();
        $initialOptions = $formObject->getDynamicInitialOptions();
        $form = [
            'fields' => $fields->map(function (Field $field) use ($initialOptions, $record, $typeMapper) {
                $type = $typeMapper($field);
                return [
                    'id'       => $field->id,
                    'title'    => $field->title,
                    'slug'     => $field->field,
                    'type'     => $type,
                    'help'     => $field->help,
                    'required' => !!$field->required,
                    'options'  => $field->getVueOptions($initialOptions, $record),
                    'group'    => $field->fieldGroup,
                    'relation' => $type === 'select:single' ? $field->hasOneSelectRelation : null,
                    'reverseRelation' => $type === 'select:single' ? $field->hasOneReverseSelectRelation : null,
                    'settings' => $field->settings
                        ->keyBy(fn(Setting $setting) => str_replace('pckg.generic.setting.', '', $setting->slug))
                        ->map(function(Setting $setting) {
                            if (in_array($setting->slug, ['pckg.dynamic.field.previewFileUrl','pckg.dynamic.field.generateFileUrl'])) {
                                return url($setting->pivot->value);
                            }
                            return $setting->pivot->value;
                        }),
                ];
            })->rekey(),
        ];

        $data = [
            'form' => $form,
            'table' => $table,
        ];

        if ($record) {
            $entity = $record->getEntity()->where('id', $record->id);
            $tabelize = $table->getTabelize($entity);
            $dynamic = resolve(DynamicService::class);
            $dynamic->joinTranslationsIfTranslatable($entity);
            foreach ($table->getBelongsToRelations() as $relation) {
                $relation->loadOnEntity($entity, $dynamic);
            }
            $model = $entity->allOrFail()->first();

            $data['model'] = $tabelize->setDynamicRecord($model)->transformRecord($model);
        } else {
            // do we need default hydrated model?
        }

        return $data;
    }
}
