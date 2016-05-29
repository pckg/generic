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
            $relation->withTranslations();
        })->where('slug', $slug)->one();

        if (!$menu) {
            return '<!-- no menu ' . $slug . ' -->';
        }

        $first = $this->buildTree($menu->menuItems)->first();
        //dd($first, $first->getRelation('_translations')->first()->title, $first->title);
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