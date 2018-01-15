<?php namespace Pckg\Generic\Console;

use Pckg\Collection;
use Pckg\Framework\Console\Command;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Content as ContentRecord;
use Pckg\Generic\Record\Route;

class CreateGenericActionsData extends Command
{

    protected function configure()
    {
        $this->setName('generic:import-actions')
             ->setDescription('Impor pckg.generic.actions and routes');
    }

    public function handle()
    {
        (new Collection(config('pckg.generic.actions', [])))->each(function($action, $slug) {
            $actionRecord = Action::getOrNew(['slug' => $slug]);

            if (!$actionRecord->isSaved()) {
                d($slug);
            }

            $actionRecord->setAndSave($action);
        });

        (new Collection(config('pckg.generic.routes', [])))->each(function($route) {
            $routeRecord = (new Routes())->joinTranslations()->where('slug', $route['slug'])->one();

            if ($routeRecord) {
                return;
            }

            $routeRecord = Route::create([
                                             'route'     => $route['url'],
                                             'slug'      => $route['slug'],
                                             'title'     => $route['title'],
                                             'layout_id' => $route['layout_id'],
                                         ]);

            $action = Action::getOrFail(['slug' => $route['morph']['action']]);

            $content = ContentRecord::create(['content' => $route['morph']['content']]);

            ActionsMorph::create([
                                     'action_id'   => $action->id,
                                     'content_id'  => $content->id,
                                     'morph_id'    => Routes::class,
                                     'poly_id'     => $routeRecord->id,
                                     'variable_id' => 1,
                                 ]);
        });
    }

}