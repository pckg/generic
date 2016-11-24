<?php namespace Pckg\Dynamic\Controller;

use Derive\Orders\Entity\Orders;
use Derive\Orders\Record\Order;
use Pckg\Concept\Reflect;
use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Helper\Convention;
use Pckg\Database\Query;
use Pckg\Database\Query\Raw;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\MorphedBy;
use Pckg\Database\Relation\MorphsMany;
use Pckg\Database\Repository;
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
use Pckg\Dynamic\Service\Dynamic as DynamicService;
use Pckg\Framework\Controller;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View\Twig;
use Pckg\Maestro\Helper\Maestro;

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
    )
    {
        $this->dynamic = $dynamic;
        $this->pluginService = $pluginService;
    }

    /**
     * List table records.
     *
     * @param Table  $tableRecord
     * @param Entity $entity
     *
     * @return $this
     */
    public function getViewTableAction(
        Table $tableRecord,
        DynamicService $dynamicService,
        DatabaseEntity $entity = null
    )
    {
        /**
         * Set table.
         */
        $this->dynamic->setTable($tableRecord);

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
        $this->dynamic->applyOnEntity($entity);

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
        $relations = (new Relations())->where('on_table_id', $tableRecord->id)
                                      ->where('dynamic_relation_type_id', 1)
                                      ->all();

        foreach ($relations as $relation) {
            /**
             * Right table entity is created here.
             */
            $relationEntity = $relation->showTable->createEntity();

            /**
             * We need to add relations to select.
             * $tableRecord is for example users.
             * So entity is entity with table users.
             * We will fetch all users and related user_group_id and language_id
             * as user.relation_user_group_id and user.relation_language_id.
             */
            $entity->with(
                (new BelongsTo($entity, $relationEntity))
                    ->foreignKey($relation->onField->field)
                    ->fill('relation_' . $relation->onField->field)
                    ->after(
                        function($record) use ($relation) {
                            $record->setRelation('select_relation_' . $relation->onField->field, $relation);
                        }
                    )
            );
        }

        /**
         * Filter records by $_GET['search']
         */
        $this->dynamic->getFilterService()->filterByGet($entity);
        $groups = $this->dynamic->getGroupService()->getAppliedGroups();
        $records = $entity->count()->all();
        $total = $records->total();

        foreach ($groups as $group) {
            $records = $records->groupBy($group['field']);
        }

        $fieldTransformations = ['tabelizeClass'];

        if (get_class($entity) == Orders::class) {
            $fieldTransformations['bills_payed'] = function(Order $order) {
                return $order->getPayedBillsSum();
            };
        }

        $tabelize = $this->tabelize()
                         ->setTable($tableRecord)
                         ->setTitle($tableRecord->getListTitle())
                         ->setEntity($entity)
                         ->setRecords($records)
                         ->setFields(
                             $tableRecord->listableFields(
                                 function(HasMany $relation) {
                                     $relation->withFieldType();
                                     $relation->joinTranslations();
                                 }
                             )->reduce(
                                 function(Field $field) use ($tableRecord) {
                                     $fields = $_SESSION['pckg']['dynamic']['view']['table_' . $tableRecord->id]['view']['fields'] ?? [];

                                     return (!$fields && $field->visible) || in_array($field->field, $fields);
                                 }
                             )
                         )
                         ->setPerPage(50)
                         ->setPage(1)
                         ->setTotal($total)
                         ->setGroups($groups ? range(1, count($groups)) : [])
                         ->setEntityActions($tableRecord->getEntityActions())
                         ->setRecordActions($tableRecord->getRecordActions())
                         ->setViews($tableRecord->actions()->keyBy('slug'))
                         ->setFieldTransformations($fieldTransformations);

        if ($this->request()->isAjax() && strpos($_SERVER['REQUEST_URI'], '/tab/') === false) {
            return [
                'records' => $tabelize->transformRecords(),
                'groups'  => $groups,
            ];
        }

        $tabelize->getView()->addData('dynamic', $this->dynamic);

        return $tabelize;
    }

    public function getAddAction(
        Dynamic $form,
        Table $table,
        Record $record,
        Relation $relation = null,
        $foreign = null
    )
    {
        if (!$table->listableFields->count()) {
            $this->response()->notFound('Missing view field permissions.');
        }

        $tableEntity = $table->createEntity();
        $record->setEntity($tableEntity);

        if ($foreign) {
            $record->{$relation->onField->field} = $foreign;
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

    public function postAddAction(Dynamic $form, Record $record, Table $table, Entity $entity)
    {
        if ($this->post('copy_to_language')) {
            die('copying to language is not implemented ... yet ;-)');
        }

        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record->setEntity($entity);

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

        $record->save($entity);

        if ($this->post()->p17n) {
            $this->saveP17n($record, $entity);
        }

        Record::$dynamicTable = $table;

        return $this->response()->respondWithSuccessRedirect($record->getEditUrl());
    }

    public function getEditAction(Dynamic $form, Record $record, Table $table)
    {
        if (!$table->listableFields->count()) {
            $this->response()->notFound('Missing view field permissions.');
        }

        $form->setTable($table);
        $form->setRecord($record);
        $form->initFields();

        $form->populateFromRecord($record);

        $tableEntity = $table->createEntity();

        if ($tableEntity->isTranslatable()) {
            $form->initLanguageFields();
        }

        if ($tableEntity->isPermissionable()) {
            $form->initPermissionFields();
        }

        $title = 'Edit ' . $record->title ?? ($record->slug ?? ($record->email ?? $table->title));
        $formalize = $this->formalize($form, $record, $title);

        /**
         * We also have to return related tables.
         */
        $tabs = $table->tabs;
        try {
            list($tabelizes, $functionizes) = $this->getTabelizesAndFunctionizes($tabs, $record, $table);
        } catch (\Exception $e) {
            $tabelizes = [];
            $functionizes = [];
        }

        /**
         * And build tabs ...
         */
        if (!$tabs->count()) {
            /**
             * Return simple html without tabs.
             */
            return view(
                'edit/singular',
                [
                    'formalize'    => $formalize,
                    'tabelizes'    => $tabelizes,
                    'functionizes' => $functionizes,
                ]
            );
        }

        ksort($tabelizes);
        ksort($functionizes);

        Tab::$dynamicRecord = $record;
        Tab::$dynamicTable = $table;

        /**
         * We have to build tabs.
         */
        return view(
            'edit/tabs',
            [
                'tabs'         => $tabs,
                'tabelizes'    => $tabelizes,
                'formalize'    => $formalize,
                'functionizes' => $functionizes,
            ]
        );
    }

    public function getTabAction(Record $record, Table $table, Tab $tab)
    {
        $relations = $table->hasManyRelation(
            function(HasMany $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
                //$relation->withShowTable();
                //$relation->withOnField();
            }
        );
        $table->morphsManyRelation(
            function(MorphsMany $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
                //$relation->withShowTable();
                //$relation->withOnField();
            }
        )->each(
            function($item) use ($relations) {
                $relations->push($item);
            }
        );
        $table->morphedByRelation(
            function(MorphedBy $relation) use ($tab) {
                $relation->where('dynamic_table_tab_id', $tab->id);
                //$relation->withShowTable();
                //$relation->withOnField();
            }
        )->each(
            function($item) use ($relations) {
                $relations->push($item);
            }
        );
        $tabs = $table->tabs;
        $tabelizes = [];
        $relations->each(
            function(Relation $relation) use ($tabs, $record, &$tabelizes) {
                $entity = $relation->showTable->createEntity();
                $entity->setStaticDynamicRecord($record);
                $entity->setStaticDynamicRelation($relation);
                $entity->where($relation->onField->field, $record->id);
                $tabelize = $this->getViewTableAction(
                    (new Tables())->where('id', $relation->showTable->id)->one(),
                    $this->dynamic,
                    $entity
                );

                $tabelizes[] = (string)$tabelize;
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
            function(Func $function) use ($tabs, &$functionizes, $pluginService, $record, $args) {
                $functionize = $pluginService->make(
                    $function->class,
                    ($this->request()->isGet() ? 'get' : 'post') . ucfirst($function->method),
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

    protected function getTabelizesAndFunctionizes($tabs, $record, $table)
    {
        $relations = $table->hasManyRelation(
            function(HasMany $query) {
                $query->where('dynamic_relation_type_id', 2);
            }
        );

        $tabelizes = [];
        $recordsController = Reflect::create(Records::class);
        $relations->each(
            function(Relation $relation) use ($tabs, $record, &$tabelizes, $recordsController) {
                /*$entity = $relation->showTable->createEntity();
                $entity->where($relation->onField->field, $record->id);

                $tableResolver = Reflect::create(TableResolver::class);
                $table = $tableResolver->resolve($tableResolver->parametrize($relation->showTable));

                $tabelize = $recordsController->getViewTableAction(
                    $table,
                    $this->dynamic,
                    $entity
                );*/
                $tabelize = null;

                if ($tabs->count()) {
                    $tabelizes[$relation->dynamic_table_tab_id ?: 0][] = $tabelize;

                } else {
                    $tabelizes[] = $tabelize;

                }
            }
        );

        $functionizes = [];
        $functions = $table->functions;
        $pluginService = $this->pluginService;
        $functions->each(
            function(Func $function) use ($tabs, &$functionizes, $pluginService, $record) {
                $functionize = $pluginService->make(
                    $function->class,
                    ($this->request()->isGet() ? 'get' : 'post') . ucfirst($function->method),
                    [$record]
                );
                if ($tabs->count()) {
                    $functionizes[$function->dynamic_table_tab_id ?: 0][] = $functionize;

                } else {
                    $functionizes[] = $functionize;

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
    )
    {
        if ($this->post('copy_to_language')) {
            die('copying to language is not implemented ... yet ;-)');
        }

        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record->setEntity($entity);

        $form->setTable($table);
        $form->initFields();

        if ($entity->isTranslatable()) {
            $form->initLanguageFields();
        }

        $form->populateFromRequest();
        $form->populateToRecord($record);
        $form->populatePasswords($record);

        $record->save($entity);

        if ($this->post()->p17n) {
            $this->saveP17n($record, $entity);
        }

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

    public function getDeleteAction(Record $record)
    {
        $table = $this->router()->resolved('table');
        $entity = $table->createEntity();
        $record->delete($entity);

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
        $file = $_FILES['file'];

        if ($file['error']) {
            return [
                'success' => false,
                'message' => 'Error uploading file ...',
            ];
        }

        if (!$file['size']) {
            return [
                'success' => false,
                'message' => 'Empty file size ...',
            ];
        }

        /**
         * @T00D00 - save and process file!
         */
        $entity = $table->createEntity();
        $record->setEntity($entity);

        $dir = $field->getAbsoluteDir($field->getSetting('pckg.dynamic.field.dir'));
        $name = Convention::url(substr($file['name'], 0, strrpos($file['name'], '.')));
        $extension = substr($file['name'], strrpos($file['name'], '.'));
        $i = 0;
        do {
            $filename = $name . ($i ? '_' . $i : '') . $extension;
            $i++;
        } while (is_file($dir . $filename));

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        move_uploaded_file($file['tmp_name'], $dir . $filename);

        $record->{$field->field} = $filename;
        $record->save($entity);

        return [
            'success' => 'true',
            'url'     => img($record->{$field->field}, null, true, $dir),
        ];
    }

}