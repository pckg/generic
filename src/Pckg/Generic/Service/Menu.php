<?php namespace Pckg\Generic\Service;

use Pckg\Collection;
use Pckg\Database\Relation\HasMany;
use Pckg\Generic\Entity\Menus;

class Menu
{

    /**
     * @var Menus
     */
    protected $menus;

    public function __construct(Menus $menus)
    {
        $this->menus = $menus;
    }

    public function build($slug)
    {
        $menu = $this->menus->withMenuItems(function (HasMany $relation) {
            /**
             * @T00D00 - make this join for better performance ;-)
             */
            $relation->withTranslations();
            $relation->joinPermissionTo('read');
        })->where('slug', $slug)->one();

        if (!$menu) {
            return '<!-- no menu ' . $slug . ' -->';
        }

        $first = $this->buildTree($menu->menuItems)->first();
        return view('Pckg\Generic:menu\\' . $menu->template, [
            'menu'      => $menu,
            'menuItems' => $this->buildTree($menu->menuItems),
        ]);
    }

    protected function buildTree(Collection $menuItems)
    {
        return $menuItems->getTree('parent_id');
    }

}