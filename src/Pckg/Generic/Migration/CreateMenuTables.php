<?php

namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateMenuTables extends Migration
{

    protected $dependencies = [
        // translatable, permissionable
    ];
    public function up()
    {
        $this->menusUp();
        $this->save();
    }

    protected function menusUp()
    {
        $menus = $this->table('menus');
        $menus->slug();
        $menus->varchar('template');
        $menuItems = $this->table('menu_items');
        $menuItems->orderable();
        $menuItems->varchar('icon', 64);
        $menuItems->integer('menu_id')->references('menus');
        $menuItems->integer('parent_id')->references('menu_items');
        $menuItemsI18n = $this->translatable('menu_items');
        $menuItemsI18n->title();
        $menuItemsI18n->varchar('url');
        $menuItemsP17n = $this->permissiontable('menu_items');
    }
}
