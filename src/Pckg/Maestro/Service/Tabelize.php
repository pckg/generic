<?php namespace Pckg\Maestro\Service;

use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Obj;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Entity\TableViews;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\TableView;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View;
use Pckg\Maestro\Service\Tabelize\Cloner;
use Pckg\Maestro\Service\Tabelize\Delete;
use Throwable;

class Tabelize
{

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $fieldTransformations = [];

    /**
     * @var Collection
     */
    protected $records = [];

    /**
     * @var array
     */
    protected $recordActions = [
        'view',
        'edit',
        'delete',
        'clone',
    ];

    protected $listActions = [];

    /**
     * @var array
     */
    protected $entityActions = [
        'add',
        'options',
        'export',
    ];

    /**
     * @var null
     */
    protected $groups = [];

    /**
     * @var string
     */
    protected $title;

    protected $page;

    protected $perPage;

    protected $total;

    protected $views = [];

    protected $dataOnly = false;

    protected $view;

    protected $table;

    protected $dynamicTable;

    protected $dynamicRecord;

    protected $dynamicRelation;

    protected $listableFields = [];

    protected $listableRelations = [];

    protected $viewData = [];

    /**
     * @var TableView
     */
    protected $tableView;

    public function __construct(Entity $entity = null, $fields = [])
    {
        $this->entity = $entity;
        $this->fields = $fields;
        $this->view = view('Pckg/Maestro:tabelize', [
            'tabelize' => $this,
        ]);
    }

    public function make()
    {
        $all = $this->entity->count()->all();
        $total = $this->entity->total();

        $this->setRecords($all)
             ->setPerPage(50)
             ->setPage(1)
             ->setTotal($total)
             ->setGroups([])
             ->setEntityActions([])
             ->setRecordActions([])
             ->setListActions([])
             ->setFieldTransformations([]);

        return $this;
    }

    public function setViewData($data)
    {
        $this->viewData = $data;

        return $this;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function setDynamicRecord($record)
    {
        $this->dynamicRecord = $record;

        return $this;
    }

    public function setDynamicRelation($relation)
    {
        $this->dynamicRelation = $relation;

        return $this;
    }

    public function getTable()
    {
        if (!$this->table) {
            return new Record(['title' => $this->title]);
        }

        return $this->table;
    }

    public function setDataOnly($dataOnly = true)
    {
        $this->dataOnly = $dataOnly;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setFields($fields = [])
    {
        $this->fields = $fields;

        return $this;
    }

    public function setFieldTransformations(array $fields = [])
    {
        $this->fieldTransformations = $fields;

        return $this;
    }

    public function getFields()
    {
        if (!$this->fields instanceof Collection) {
            $this->fields = new Collection($this->fields);
        }

        return $this->fields->map(function($item) {
            if (is_object($item)) {
                return $item;
            }

            if (is_string($item)) {
                return new Record([
                                      'title'                 => $item,
                                      'field'                 => $item,
                                      'dynamic_field_type_id' => 1,
                                  ]);
            }

            return new Record([
                                  'title'                 => $item['title'] ?? null,
                                  'field'                 => $item['field'] ?? null,
                                  'dynamic_field_type_id' => 1,
                              ]);
        });
    }

    public function getFieldTransformations()
    {
        return $this->fieldTransformations;
    }

    public function getFieldsKeys()
    {
        $keys = [];

        foreach ($this->fields as $key => $field) {
            if (is_string($field)) {
                $keys[] = $field;
            } elseif (is_string($key)) {
                $keys[] = $key;
            } elseif (is_object($field) && $field instanceof Field) {
                $keys[] = $field->field;
            } elseif (is_int($key)) {
                $keys[] = $field;
            }
        }

        return $keys;
    }

    public function setRecordActions($recordActions = [])
    {
        $this->recordActions = $recordActions;

        return $this;
    }

    public function setListActions($listActions = [])
    {
        $this->listActions = $listActions;

        return $this;
    }

    /**
     * @param Field|string $field
     * @param Record       $originalRecord
     *
     * @return mixed|null
     */
    public function getRecordValue($field, $originalRecord, &$enrichedValue)
    {
        try {
            if (is_string($field)) {
                return $originalRecord->{$field};
            } elseif (is_only_callable($field)) {
                return $field($originalRecord);
            }

            /**
             * If it's type is not 'select' or 'mixed', return value.
             */
            if ($field->dynamic_field_type_id != 8 && $field->dynamic_field_type_id != 19) {
                return $originalRecord->{$field->field};
            }

            /**
             * We have $originalRecord which represents user record.
             * $field is user_group_id (object).
             * $originalRecord should have relation $originalRecord->relation_user_group_id,
             * but what we need to print is stored in select_relation_user_group_id
             */
            if ($originalRecord->relationExists('relation_' . $field->field)) {
                /**
                 * Select type.
                 */
                $record = $originalRecord->getRelation('relation_' . $field->field);

                if ($record) {
                    $relation = $originalRecord->getRelation('select_relation_' . $field->field);

                    $eval = $this->eval($relation->value, $record, $originalRecord, $relation);

                    if (!trim($eval)) {
                        $eval = '#' . $originalRecord->{$field->field};
                    }

                    $enrichedValue = $this->dataOnly
                        ? $eval
                        : ('<a href="' . $relation->showTable->getEditUrl($record) . '" title="Open related record">' .
                            $eval . '</a>');
                }
            }

            return $originalRecord->{$field->field};
        } catch (Throwable $e) {
            return '-- ' . exception($e) . ' --';
        }
    }

    /**
     * @param      $eval
     * @param      $record
     * @param      $originalRecord
     * @param null $relation
     *
     * @return mixed|string
     * See if we can do this in more secure way! @T00D00
     */
    public function eval($eval, $record, $originalRecord, $relation = null)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (Throwable $e) {
            return '-- ' . exception($e) . ' --';
        }
    }

    public function getRecordActions()
    {
        return $this->recordActions;
    }

    public function setEntityActions($entityActions = [])
    {
        $this->entityActions = $entityActions;

        return $this;
    }

    public function getEntityActions()
    {
        return $this->entityActions;
    }

    public function setRecords($records)
    {
        $this->records = $records;

        return $this;
    }

    public function getRecords()
    {
        if (!$this->records) {
            $this->records = $this->entity->all();
        }

        return $this->records;
    }

    /**
     * @param Field|string $field
     *
     * @return string
     */
    public function getColumnHeading($field, $key)
    {
        return __(is_string($key) ? $key : (is_string($field) ? $field : $field->field));
    }

    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    public function getView()
    {
        return $this->view;
    }

    public function __toStringViews()
    {
        $string = '';

        /**
         * Then parse all additional views (custom actions).
         */
        foreach ([$this->views, $this->listActions] as $data) {
            foreach ($data as $key => $view) {
                try {
                    /**
                     * @T00D00 - this should be automatic ...
                     */
                    if (is_string($key) && in_array($key, ['delete', 'clone'])) {
                        $view = $key;
                    }

                    $string .= '<!-- start tabelize view' . (is_string($view) ? ' ' . $view : '') . ' -->';

                    $wasObject = false;
                    $listAction = true;
                    if (is_object($view)) {
                        if ($view instanceof View\Twig) {
                            $view = $view->autoparse();
                            $wasObject = true;
                        } else {
                            if ($view->type == 'record') {
                                $listAction = false;
                            }
                            $view = $view->template;
                        }
                    }

                    if (!is_string($view)) {
                        $string .= "\n" . '<!-- entity view (string) -->';
                        $string .= $view;
                    } elseif (!$wasObject && strpos($view, '@')) {
                        list($class, $method) = explode('@', $view);
                        if (strpos($method, ':')) {
                            list($method, $view) = explode(':', $method);
                        }

                        $string .= "\n" . '<!-- entity view (plugin ' . $class . '->' . $method . ') -->';
                        $string .= resolve(Plugin::class)->make($class, $method, [$this->entity, $this->table], true);
                    } elseif (!$wasObject && $view) {
                        if ($listAction) {
                            $string .= "\n" . '<!-- entity view (tabelize/listActions/' . $view . ') -->';
                            if ($view === 'delete') {
                                $delete = new Delete();
                                $string .= $delete->getListAction($this);
                            } elseif ($view === 'clone') {
                                $cloner = new Cloner();
                                $string .= $cloner->getListAction($this);
                            } else {
                                $string .= view('tabelize/listActions/' . $view)->autoparse();
                            }
                        }
                    } else {
                        $string .= "\n" . '<!-- entity view (else) -->';
                        $string .= $view;
                    }

                    $string .= '<!-- end tabelize view' . (is_string($view) && !$wasObject ? ' ' . $view : '') . ' -->';
                } catch (Throwable $e) {
                    if (!prod()) {
                        throw $e;
                    }
                }
            }
        }

        return $string;
    }

    public function __toString()
    {
        try {
            $string = '';
            measure('Tabelize', function() use (&$string) {
                $string .= '<!-- start tabelize -->';
                $string .= $this->view->autoparse();
                $string .= '<!-- end tabelize -->';

                $actionsTemplate = $this->__toStringParsedViews();
                $string .= $actionsTemplate;
            });
        } catch (Throwable $e) {
            return exception($e);
        }

        return (string)$string;
    }

    public function __toStringParsedViews()
    {
        /**
         * @T00D00 ... scripts should be added to vue manager
         *         ... component usages should be added to template
         */
        $actionsTemplate = '<!-- start tabelize views -->' . $this->__toStringViews() . '<!-- end tabelize views-->';
        $vueTemplate = '';
        $pattern = "#<\s*?script\b[^>]*>(.*?)</script\b[^>]*>#s";

        /**
         * Add all scripts to vue header.
         */
        preg_match_all($pattern, $actionsTemplate, $matches);
        foreach ($matches[0] ?? [] as $match) {
            $actionsTemplate = str_replace($match, '', $actionsTemplate);
            $vueTemplate .= $match;
        }

        vueManager()->addStringView($vueTemplate);

        return $actionsTemplate;
    }

    /**
     * @return array
     * Transforms collection to array of arrays.
     */
    public function transformRecords()
    {
        return $this->transformCollection($this->getRecords());
    }

    public function transformCollection($collection)
    {
        $records = [];
        foreach ($collection as $key => $record) {
            if (is_object($record) && object_implements($record, \Iterator::class) || is_array($record)) {
                $records[$key] = $this->transformCollection($record);
            } else {
                $records[$key] = $this->transformRecord($record);
            }
        }

        return $records;
    }

    public function transformRecord(Obj $record)
    {
        $transformed = [];

        /**
         * Table fields
         */
        foreach ($this->getFields() as $key => $field) {
            $realKey = is_string($key)
                ? $key : (is_string($field)
                    ? $field : (is_object($field) ? $field->field
                        : $field['field']));
            $enriched = null;
            $transformed[$realKey] = $this->getRecordValue($field, $record, $enriched);
            if ($enriched) {
                $transformed['*' . $realKey] = $enriched;
            }
        }

        /**
         * Additional fields
         */
        foreach ($this->getFieldTransformations() as $key => $field) {
            $realKey = is_string($key) ? $key : (is_string($field) ? $field : $field->field);
            $enriched = null;
            $transformed[$realKey] = $this->getRecordValue($field, $record, $enriched);
            if ($enriched) {
                $transformed['*' . $realKey] = $enriched;
            }
        }

        if ($this->dataOnly) {
            return $transformed;
        }

        /**
         * We also need to fetch URLs.
         */
        foreach ($this->getRecordActions() as $recordAction) {
            $method = is_string($recordAction) ? $recordAction : $recordAction->slug;

            if (router()->hasUrl('dynamic.record.' . $method)) {
                $transformed[$method . 'Url'] = url('dynamic.record.' . $method, [
                    'record' => $record,
                    'table'  => $this->table,
                ]);
            }

            if (router()->hasUrl('dynamic.record.' . $method . 'Translation')) {
                $transformed[$method . 'TranslationUrl'] = url('dynamic.record.' . $method . 'Translation', [
                    'record'   => $record,
                    'table'    => $this->table,
                    'language' => $_SESSION['pckg_dynamic_lang_id'],
                ]);
            }

            if (method_exists($record, 'get' . ucfirst($method) . 'UrlAttribute')) {
                $transformed[$method . 'Url'] = $record->{'get' . ucfirst($method) . 'UrlAttribute'}();
            }
        }
        $transformed = array_merge($record->getToArrayValues(), $transformed);
        $transformed = array_merge($transformed, $record->getToJsonValues());

        /**
         * ID is mandatory.
         */
        if (!isset($transformed['id'])) {
            $transformed['id'] = $record->id;
        }

        /**
         * @T00D00 - tabelize class is not needed anymore?
         */
        if (!isset($transformed['tabelizeClass']) && method_exists($record, 'getTabelizeClassAttribute')) {
            $transformed['tabelizeClass'] = $record->tabelizeClass;
        }

        if ($record->hasKey('language_id') && !isset($transformed['language_id'])) {
            $transformed['language_id'] = $record->language_id;
        }

        if ($record->hasKey('hash') && !isset($transformed['hash'])) {
            $transformed['hash'] = $record->hash;
        }

        return $transformed;
    }

    public function getEntityActionsHtml($normal = true)
    {
        $html = null;
        $data = $this->viewData;
        $data['tabelize'] = $this;
        foreach ($this->getEntityActions() as $action) {
            $template = null;
            if (isset($action->slug) && isset($action->entityTemplate)) {
                $template = 'tabelize/entityActions/' . $action->entityTemplate;
            } else {
                $template = 'tabelize/entityActions/' . $action;
            }

            if ($normal && in_array($action, ['add', 'edit', 'export', 'view', 'import', 'delete'])) {
                $html .= "\n" . '<!-- entity action template ' . $template . ' -->';
                $parsed = view($template, $data)->autoparse();
                $html .= $parsed;
            } elseif (!$normal && !in_array($action, ['add', 'edit', 'export', 'view', 'import', 'delete'])) {
                $html .= "\n" . '<!-- entity action template ' . $template . ' -->';
                $parsed = view($template, $data)->autoparse();
                $html .= '<li>' . $parsed . '</li>';
            }
        }

        return $html;
    }

    public function getEntityActionsArray($normal = true)
    {
        $html = null;
        $data = $this->viewData;
        $data['tabelize'] = $this;
        $actions = [];
        foreach ($this->getEntityActions() as $action) {
            try {
            $template = null;
            if (isset($action->slug) && isset($action->entityTemplate)) {
                $template = 'tabelize/entityActions/' . $action->entityTemplate;
            } else {
                $template = 'tabelize/entityActions/' . $action;
            }

            if (($normal && in_array($action, ['add', 'edit', 'export', 'view', 'import', 'delete'])) ||
                (!$normal && !in_array($action, ['add', 'edit', 'export', 'view', 'import', 'delete']))) {
                $html .= "\n" . '<!-- entity action template ' . $template . ' -->';
                $parsed = trim(view($template, $data)->autoparse());

                if (strpos($parsed, '{') !== 0) {
                    dd($template, $parsed);
                    $actions[] = [
                        'title' => $template,
                    ];
                    continue;
                }

                $actions[] = json_decode($parsed);
            }
            } catch (Throwable $e) {
                if (!prod()) {
                    throw $e;
                }
            }
        }

        return $actions;
    }

    public function getActionsArray()
    {
        return [
            'entity' => $this->getEntityActionsArray(false),
            'record' => $this->getRecordActionsArray(),
        ];
    }

    public function getRecordActionsArray()
    {
        $html = null;
        $data = $this->viewData;
        $data['tabelize'] = $this;
        $actions = [];
        foreach ($this->getRecordActions() as $action) {
            try {
            $template = 'tabelize/recordActions/' .
                (is_string($action) ? $action : ($action->template ? $action->template : $action->slug));

            $parsed = trim(view($template, $data)->autoparse());

            if (strpos($parsed, '{') !== 0) {
                dd($template, $parsed, $action);
                $actions[] = [
                    'title' => $template,
                ];
                continue;
            }

            $action = json_decode($parsed);
            if (!$action) {
                dd($parsed);
            }
            $actions[] = $action;
            } catch (Throwable $e) {
                if (!prod()) {
                    throw $e;
                }
            }
        }

        return $actions;
    }

    public function getPaginator()
    {
        return [
            'perPage'  => $this->getPerPage(),
            'page'     => $this->getPage(),
            'filtered' => $this->getTotal(),
            'total'    => $this->getTotal(),
            'url'      => router()->getUri(),
        ];
    }

    public function getEntityUrl($slug, ...$params)
    {
        if (!$this->entity) {
            return null;
        }

        try {
            return $this->{'get' . ucfirst($slug) . 'Url'}(...$params);
        } catch (Throwable $e) {
        }
    }

    public function getResetViewUrl()
    {
        return url('dynamic.record.view.reset', [
            'table' => $this->table,
        ]);
    }

    public function getSaveViewUrl()
    {
        return url($this->tableView ? 'dynamic.record.view.savePlusView' : 'dynamic.record.view.save', [
            'table'     => $this->table,
            'tableView' => $this->tableView,
        ]);
    }

    public function getImportUrl()
    {
        return url('dynamic.record.import', [
            'table' => $this->table,
        ]);
    }

    public function getExportUrl($type)
    {
        return url('dynamic.record.export', [
            'table' => $this->table,
            'type'  => $type,
        ]);
    }

    public function getAddUrl()
    {
        if ($this->dynamicRelation && $this->dynamicRecord) {
            return url('dynamic.record.add.related', [
                'table'    => $this->table,
                'relation' => $this->dynamicRelation,
                'foreign'  => $this->dynamicRecord,
            ]);
        }

        return url('dynamic.record.add', [
            'table' => $this->table,
        ]);
    }

    public function getSavedViews()
    {
        return (new TableViews())->where('dynamic_table_id', $this->getDynamicTable()->id)->all();
    }

    public function getDynamicTable()
    {
        if (!$this->table) {
            $this->table = (new Tables())->where('framework_entity', get_class($this->entity))->oneOrFail(function() {
                response()->notFound('Dynamic table is missing');
            });
        }

        return $this->table;
    }

    public function getTabUrl($tab)
    {
        return url('dynamic.record.tab', [
            'tab'    => $tab,
            'table'  => $this->table,
            'record' => $this->dynamicRecord,
        ]);
    }

    public function getViewUrl()
    {
        return url('dynamic.record.list' . ($this->tableView ? 'View' : ''), [
            'table'     => $this->table,
            'tableView' => $this->tableView,
        ]);
    }

    public function getConfigureUrl()
    {
        return url('dynamic.record.list' . ($this->tableView ? 'View' : '') . 'Configure', [
            'table'     => $this->table,
            'tableView' => $this->tableView,
        ]);
    }

    public function setTableView(TableView $tableView = null)
    {
        $this->tableView = $tableView;

        return $this;
    }

}