<?php namespace Pckg\Dynamic\Entity\Snippet;

use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Entity\Tables;

trait EntityActions
{

    public static $dynamicTable;

    public function getSavedViews()
    {
        return $this->getDynamicTable()->views(
            function($relation) {
                $relation->joinTranslations();
            }
        );
    }

    public function getDynamicTable()
    {
        if (!static::$dynamicTable) {
            static::$dynamicTable = (new Tables())->where('framework_entity', static::class)->oneOrFail(
                function() {
                    response()->notFound('Dynamic table for entity ' . static::class . ' is missing');
                }
            );
        }

        return static::$dynamicTable;
    }

    public function isTranslatable()
    {
        return isset($this->translatableTableSuffix)
            ? $this->getRepository()->getCache()->hasTable($this->table . $this->translatableTableSuffix)
            : false;
    }

    public function isPermissionable()
    {
        return isset($this->permissionableTableSuffix)
            ? $this->getRepository()->getCache()->hasTable($this->table . $this->permissionableTableSuffix)
            : false;
    }

    public function getAddUrl()
    {
        return url(
            'dynamic.record.add',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getExportUrl($type)
    {
        return url(
            'dynamic.record.export',
            [
                'table' => $this->getDynamicTable(),
                'type'  => $type,
            ]
        );
    }

    public function getSortUrl()
    {
        return url(
            'dynamic.record.sort',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getGroupUrl()
    {
        return url(
            'dynamic.record.group',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getFilterUrl()
    {
        return url(
            'dynamic.record.filter',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getFieldsUrl()
    {
        return url(
            'dynamic.record.fields',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getSaveViewUrl()
    {
        return url(
            'dynamic.record.view.save',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getShareViewUrl()
    {
        return url(
            'dynamic.record.view.share',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getResetViewUrl()
    {
        return url(
            'dynamic.record.view.reset',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

    public function getSaveFilterUrl()
    {
        return url(
            'dynamic.record.filter.save',
            [
                'table' => $this->getDynamicTable(),
            ]
        );
    }

}