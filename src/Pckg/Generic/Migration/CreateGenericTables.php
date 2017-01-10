<?php namespace Pckg\Generic\Migration;

use Pckg\Database\Repository;
use Pckg\Migration\Migration;

class CreateGenericTables extends Migration
{

    protected $repository = Repository::class . '.dynamic';

    /**
     * Those packets need to be installed before this migration.
     */
    public function dependencies()
    {
        return [
            // translatable, permissionable
            CreateMenuTables::class,
            CreateLanguagesTable::class,
        ];
    }

    public function up()
    {
        $this->layoutsUp();
        $this->routesUp();
        $this->variablesUp();
        $this->contentsUp();
        $this->actionsUp();
        $this->menusUp();
        $this->settingsUp();
        $this->translationsUp();

        $this->save();
    }

    protected function layoutsUp()
    {
        $layouts = $this->table('layouts');
        $layouts->slug();
        $layouts->varchar('template');

        $layoutsI18n = $this->translatable('layouts');
        $layoutsI18n->title();
    }

    protected function routesUp()
    {
        $routes = $this->table('routes');
        $routes->slug();
        $routes->integer('layout_id')->references('layouts');
        $routes->timeable();

        $routesI18n = $this->translatable('routes');
        $routesI18n->title();
        $routesI18n->varchar('route');
    }

    protected function variablesUp()
    {
        $variables = $this->table('variables');
        $variables->slug();
    }

    protected function contentsUp()
    {
        $contents = $this->table('contents');
        $contents->integer('parent_id')->references('contents');
        $contents->timeable();

        $contentsI18n = $this->translatable('contents');
        $contentsI18n->title();
        $contentsI18n->subtitle();
        $contentsI18n->description();
        $contentsI18n->content();
    }

    protected function actionsUp()
    {
        $actions = $this->table('actions');
        $actions->slug();
        $actions->varchar('class');
        $actions->varchar('method');

        $actionsI18n = $this->translatable('actions');
        $actionsI18n->title();

        $actionsMorphs = $this->morphtable('actions', 'action_id');
        $actionsMorphs->integer('content_id')->references('contents');
        $actionsMorphs->integer('variable_id')->references('variables');
        $actionsMorphs->orderable();

        $actionsMorphsP17n = $this->permissiontable('actions_morphs');
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

    protected function settingsUp()
    {
        $settingTypes = $this->table('setting_types');
        $settingTypes->slug();

        $settingTypesI18n = $this->translatable('setting_types');
        $settingTypesI18n->title();

        $settings = $this->table('settings');
        $settings->integer('setting_type_id')->references('setting_types');

        $settingsI18n = $this->translatable('settings');
        $settingsI18n->text('value');

        $settingsMorphs = $this->morphtable('settings', 'setting_id');
        $settingsMorphs->varchar('value', 512);
    }

    protected function translationsUp()
    {
        $translationTypes = $this->table('translation_types');
        $translationTypes->slug();

        $translationTypesI18n = $this->translatable('translation_types');
        $translationTypesI18n->title();

        $translations = $this->table('translations');
        $translations->slug();

        $translationsI18n = $this->translatable('translations');
        $translationsI18n->text('value');
    }

}