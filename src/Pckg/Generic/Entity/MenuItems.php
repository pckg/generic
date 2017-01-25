<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Orderable;
use Pckg\Database\Entity\Extension\Permissionable;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Generic\Record\MenuItem;

class MenuItems extends Entity
{

    use Translatable, Orderable, Permissionable;

    protected $record = MenuItem::class;

    public function boot()
    {
        $this->joinTranslations();
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItems::class)
                    ->foreignKey('parent_id');
    }

}