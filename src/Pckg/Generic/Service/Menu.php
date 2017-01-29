<?php namespace Pckg\Generic\Service;

use Pckg\Collection;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Generic\Entity\Menus;

class Menu
{

    public function build($slug, $repository = null, $language = null)
    {
        $menus = new Menus();

        if ($repository) {
            $menus->setRepository(context()->get(Repository::class . ($repository ? '.' . $repository : '')));
        }

        $menu = runInLocale(
            function() use ($menus, $slug) {
                return $menus->withMenuItems(
                    function(HasMany $relation) {
                        // $relation->joinPermissionTo('read');
                    }
                )->where('slug', $slug)->one();
            },
            first($language, config('pckg.locale'))
        );

        if (!$menu) {
            return '<!-- no menu ' . $slug . ' -->';
        }

        return view(
            'Pckg\Generic:menu\\' . $menu->template,
            [
                'menu'      => $menu,
                'menuItems' => $this->buildTree($menu->menuItems),
            ]
        );
    }

    protected function buildTree(Collection $menuItems)
    {
        return $menuItems->getTree('parent_id');
    }

}