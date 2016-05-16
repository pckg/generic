<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\Menu;

class Menus extends Entity
{

    protected $record = Menu::class;

    public function menuItems()
    {
        return $this->hasMany(MenuItems::class)
            ->foreignKey('menu_id')
            ->primaryKey('id');
    }

}