<?php namespace Pckg\Dynamic\Record\Snippet;

trait RecordActions
{

    public static $dynamicTable;

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