<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Orderable;
use Pckg\Generic\Record\MenuItem;

/**
 * @method withMenu(?callable $callable = null)
 */
class MenuItems extends Entity
{
    use Orderable;


    protected $record = MenuItem::class;
    public function boot()
    {
        $this->joinTranslations();
        return $this;
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItems::class)
            ->foreignKey('parent_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menus::class)->foreignKey('menu_id');
    }
}
