<?php namespace Pckg\Generic\Service;

use Pckg\Collection;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Generic\Entity\Menus;

class Menu
{

    public function build($slug, $repository = null, $language = null, $permissions = false)
    {
        $repositoryObject = null;
        if ($repository) {
            $repositoryObject = context()->get(Repository::class . ($repository ? '.' . $repository : ''));
        }

        $menus = new Menus($repositoryObject);

        $menu = runInLocale(
            function() use ($menus, $slug, $permissions) {
                return $menus->withMenuItems(
                    function(HasMany $relation) use ($permissions) {
                        if ($permissions) {
                            $relation->joinPermissionTo('read');
                        }
                    }
                )->where('slug', $slug)->one();
            },
            first($language, config('pckg.locale.default'))
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