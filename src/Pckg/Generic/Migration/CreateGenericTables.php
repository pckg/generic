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
            CreateSettingsTable::class,
            CreateTranslationsTable::class,
        ];
    }

    public function up()
    {
        $this->layoutsUp();
        $this->routesUp();
        $this->variablesUp();
        $this->contentsUp();
        $this->actionsUp();

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

}