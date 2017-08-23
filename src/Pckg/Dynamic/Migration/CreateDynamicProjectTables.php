<?php namespace Pckg\Dynamic\Migration;

use Pckg\Auth\Migration\CreateAuthTables;
use Pckg\Database\Repository;
use Pckg\Migration\Migration;

class CreateDynamicProjectTables extends Migration
{

    public function up()
    {
        $this->dynamicTableViewsUp();

        $this->save();
    }

    protected function dynamicTableViewsUp()
    {
        $dynamicTableViews = $this->table('dynamic_table_views');
        $dynamicTableViews->integer('dynamic_table_id')->references('dynamic_tables');
        $dynamicTableViews->text('settings');

        $dynamicTableViewsI18n = $this->translatable('dynamic_table_views');
        $dynamicTableViewsI18n->title();

        $dynamicTableViewsP17n = $this->permissiontable('dynamic_table_views');
    }

}