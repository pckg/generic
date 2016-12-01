<?php namespace Pckg\Dynamic\Record\Snippet;

use Pckg\Database\Record\Extension\Deletable;

trait RecordActions
{

    use Deletable;

    public static $dynamicTable;

    public function getViewUrl()
    {
        return url(
            'dynamic.record.view',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getEditUrl()
    {
        return url(
            'dynamic.record.edit',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getDeleteUrl()
    {
        return url(
            'dynamic.record.delete',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getForceDeleteUrl()
    {
        return url(
            'dynamic.record.forceDelete',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getListUrl()
    {
        return url(
            'dynamic.record.list',
            [
                'table' => $this,
            ]
        );
    }

}