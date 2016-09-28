<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Tabs;

class Tab extends Record
{

    protected $entity = Tabs::class;

    public static $dynamicRecord;

    public static $dynamicTable;

    public function getUrl()
    {
        return url(
            'dynamic.record.tab',
            [
                'tab'    => $this,
                'table'  => static::$dynamicTable,
                'record' => static::$dynamicRecord,
            ]
        );
    }

}