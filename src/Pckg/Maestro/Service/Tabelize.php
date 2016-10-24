<?php namespace Pckg\Maestro\Service;

use Exception;
use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Helper\Convention;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Record\RecordInterface;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Record;
use Pckg\Framework\Service\Plugin;

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
        'edit',
        'delete',
    ];

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

    public function __construct(Entity $entity = null, $fields = [])
    {
        $this->entity = $entity;
        $this->fields = $fields;
        $this->view = view(
            'Pckg/Maestro:tabelize',
            [
                'tabelize' => $this,
            ]
        );
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getTable()
    {
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
        return $this->title . ' (' . $this->total . ')';
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
        return (new Collection($this->fields))->map(
            function($item, $key) {
                return is_string($key)
                    ? $key
                    : $item;
            }
        )->toArray();
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
            } else if (is_string($key)) {
                $keys[] = $key;
            } else if (is_object($field) && $field instanceof Field) {
                $keys[] = $field->field;
            } else if (is_int($key)) {
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

    /**
     * @param Field|string $field
     * @param Record       $originalRecord
     *
     * @return mixed|null
     */
    public function getRecordValue($field, $originalRecord)
    {
        try {
            if (is_string($field)) {
                return $originalRecord->{$field};

            } else if (is_callable($field)) {
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

                    return $this->dataOnly
                        ? $eval
                        : ('<a href="' . $relation->showTable->getEditUrl($record) . '">' . $eval . '</a>');
                }
            } elseif ($field->dynamic_field_type_id == 19) {
                /**
                 * Mixed type.
                 */
                $eval = $this->eval($field->eval, $originalRecord, $originalRecord);

                return $eval;

            }

            return $originalRecord->{$field->field};

        } catch (Exception $e) {
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
     *
     * See if we can do this in more secure way! @T00D00
     */
    public function eval($eval, $record, $originalRecord, $relation = null)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (\Exception $e) {
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

    public function __toString()
    {
        try {
            /**
             * Parse tabelize view.
             */
            $string = '<!-- start tabelize -->' . $this->view->autoparse() . '<!-- end tabelize -->';

            /**
             * Then parse all additional views (custom actions).
             */
            foreach ($this->views as $view) {
                $string .= '<!-- start tabelize view' . (is_string($view) ? ' ' . $view : '') . ' -->';

                if (is_object($view)) {
                    $view = $view->template;
                }

                if (!is_string($view)) {
                    $string .= $view;

                } elseif (strpos($view, '@')) {
                    list($class, $method) = explode('@', $view);
                    if (strpos($method, ':')) {
                        list($method, $view) = explode(':', $method);
                    }

                    $string .= resolve(Plugin::class)->make($class, $method, [$this->entity, $this->table], true);

                } else {
                    $string .= view('tabelize/listActions/' . $view)->autoparse();

                }

                $string .= '<!-- end tabelize view' . (is_string($view) ? ' ' . $view : '') . ' -->';
            }
        } catch (Exception $e) {
            return exception($e);
        }

        return (string)$string;
    }

    /**
     * @return array
     *
     * Transforms collection to array of arrays.
     */
    public function transformRecords()
    {
        $records = $this->transformCollection($this->getRecords());

        return $records;
    }

    protected function transformCollection($collection)
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

    protected function transformRecord(RecordInterface $record)
    {
        $transformed = [];

        /**
         * Table fields
         */
        foreach ($this->getFields() as $key => $field) {
            $transformed[is_string($key) ? $key : (is_string($field) ? $field : $field->field)] =
                $this->getRecordValue($field, $record);
        }

        /**
         * Additional fields
         */
        foreach ($this->getFieldTransformations() as $key => $field) {
            $transformed[is_string($key) ? $key : (is_string($field) ? $field : $field->field)] = $this->getRecordValue(
                $field,
                $record
            );
        }

        /**
         * We also need to fetch URLs.
         */
        if (!$this->dataOnly) {
            foreach ($this->getRecordActions() as $recordAction) {
                $method = is_string($recordAction)
                    ? $recordAction
                    : $recordAction->slug;
                if ($method && method_exists($record, 'get' . Convention::toPascal($method) . 'Url')) {
                    $transformed[$method . 'Url'] = $record->{'get' . Convention::toPascal($method) . 'Url'}();
                }
            }
            $transformed = array_merge($record->getToArrayValues(), $transformed);
            /*foreach ($record->getToArrayValues() as $key => $value) {
                $transformed[$key] = $value;
            }*/
        }

        return $transformed;
    }

}