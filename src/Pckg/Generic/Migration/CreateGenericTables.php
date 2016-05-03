<?php namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateGenericTables extends Migration
{

    public function up()
    {
        $this->languagesUp();
        $this->layoutsUp();
        $this->routesUp();
        $this->variablesUp();
        $this->contentsUp();
        $this->actionsUp();
    }

    protected function languagesUp()
    {
        $languages = $this->table('languages');
        $languages->slug();
        $languages->varchar('flag');

        $languagesI18n = $this->translatable('languages');
        $languagesI18n->title();
    }

    protected function layoutsUp()
    {
        $layouts = $this->table('layouts');
        $layouts->slug();

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

        $actionsMorphs = $this->morphtable('actions', 'action');
        $actionsMorphs->integer('content_id')->references('contents');
        $actionsMorphs->integer('variable_id')->references('variables');
    }

}