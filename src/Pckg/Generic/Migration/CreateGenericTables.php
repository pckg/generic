<?php

namespace Pckg\Generic\Migration;

use Pckg\Auth\Migration\CreateAuthTables;
use Pckg\Migration\Migration;

class CreateGenericTables extends Migration
{

    /**
     * Those packets need to be installed before this migration.
     */
    public function dependencies()
    {
        return [
            // translatable, permissionable
            CreateLanguagesTable::class,
            CreateMenuTables::class,
            CreateListTables::class,
            CreateTranslationsTable::class,
        ];
    }

    public function partials()
    {
        return [
            (new CreateSettingsTable())->setRepository($this->repository),
            (new CreateAuthTables())->setRepository($this->repository),
        ];
    }

    public function up()
    {
        $this->layoutsUp();
        $this->routesUp();
        $this->contentsUp();
        $this->actionsUp();
        $dataAttributes = $this->morphtable('data_attributes', null, null);
        $dataAttributes->varchar('slug');
        $dataAttributes->longtext('value');
        $dataAttributes->unique('poly_id', 'morph_id', 'slug');
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
        $routes->slug('slug', 128, false);
// not unique, unique by slug and deleted_at
        $routes->integer('layout_id')->references('layouts');
        $routes->timeable();
        $routes->varchar('tags');
        $routes->varchar('resolvers');
        $routes->datetime('published_at')->nullable();
        $routes->unique('slug', 'deleted_at');
        $routesI18n = $this->translatable('routes');
        $routesI18n->title();
        $routesI18n->varchar('route');
    }

    protected function contentsUp()
    {
        $contents = $this->table('contents');
        $contents->integer('parent_id')->references('contents');
        $contents->timeable();
        $contents->orderable();
        $contentsI18n = $this->translatable('contents');
        $contentsI18n->title();
        $contentsI18n->subtitle();
        $contentsI18n->description();
        $contentsI18n->content();
        $contentsI18n->varchar('picture');
        $contentsI18n->varchar('video');
        $contentsI18n->varchar('icon');
        $contentsI18n->varchar('url');
    }

    protected function actionsUp()
    {
        $actions = $this->table('actions');
        $actions->slug();
        $actions->varchar('class');
        $actions->varchar('method');
        $actionsI18n = $this->translatable('actions');
        $actionsI18n->title();
        $actionsI18n->description();
        $actionsMorphs = $this->morphtable('actions', 'action_id');
        $actionsMorphs->parent();
        $actionsMorphs->varchar('type');
        $actionsMorphs->integer('content_id')->references('contents');
        $actionsMorphs->varchar('variable', 32);
        $actionsMorphs->orderable();
        $actionsMorphs->longtext('template');
        $actionsMorphsP17n = $this->permissiontable('actions_morphs');
    }
}
