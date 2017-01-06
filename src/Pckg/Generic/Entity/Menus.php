<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Repository;
use Pckg\Generic\Record\Menu;

class Menus extends Entity
{

    protected $record = Menu::class;

    protected $repositoryName = Repository::class . '.dynamic';

    public function menuItems()
    {
        return $this->hasMany(MenuItems::class)
                    ->fill('menuItems')
                    ->foreignKey('menu_id');
    }

}