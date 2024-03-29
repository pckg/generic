<?php

namespace Pckg\Generic\Service;

use Pckg\Collection;
use Pckg\Database\Repository;
use Pckg\Generic\Entity\MenuItems;
use Pckg\Generic\Entity\Menus;

class Menu
{
    public function build($slug, $repository = null, $language = null, $permissions = false, $params = [])
    {
        $repositoryObject = null;
        if ($repository) {
            $repositoryObject = context()->get(Repository::class . ($repository ? '.' . $repository : ''));
        }

        $menus = new Menus($repositoryObject);
        $locale = first($language, config('pckg.locale.default'), 'en_GB');
        $menus = runInLocale(function () use ($menus, $slug) {
                return $menus->where('slug', $slug)->all();
        }, $locale);
        if (!$menus->count()) {
            return '<!-- no menu -->';
        }

        $menuItems = runInLocale(function () use ($menus, $permissions, $repositoryObject) {

            $entity = (new MenuItems($repositoryObject))->where('menu_id', $menus->map('id')->all());
            if ($permissions) {
                $entity->joinPermissionTo('read');
            }

            return $entity->all();
        }, $locale);

        if ($slug == 'admin') {
            $menuItems = new Collection();
        }
        if (is_string($slug)) {
            trigger(Menu::class . '.collectMenuItems.' . $slug, $menuItems);
        }

        return view('Pckg\Generic:menu\\' . $menus->first()->template, [
                'menu'      => $menus->first(),
                'menus'     => $menus,
                'menuItems' => $this->buildTree($menuItems),
                'params'    => $params,
            ]);
    }

    protected function buildTree(Collection $menuItems)
    {
        return $menuItems->tree('parent_id', 'id');
    }
}
