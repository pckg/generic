<?php namespace Pckg\Generic\Console;

use Pckg\Collection;
use Pckg\Framework\Console\Command;
use Pckg\Generic\Entity\ListItems;
use Pckg\Generic\Entity\Lists;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Content as ContentRecord;
use Pckg\Generic\Record\Layout;
use Pckg\Generic\Record\ListItem;
use Pckg\Generic\Record\ListRecord;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Record\Variable;

class ImportGenericBackend extends Command
{

    protected function configure()
    {
        $this->setName('generic:import-backend')
             ->setDescription('Import actions, routes, variables, lists, items, ...');
    }

    public function handle()
    {
        /**
         * Import generic lists and items.
         */
        $lists = config('pckg.generic.lists', []);
        foreach ($lists as $listConfig) {
            $skipWhenExisting = $listConfig['skipWhenExisting'] ?? false;
            $list = (new Lists())->where('slug', $listConfig['id'])->one();

            if (!$list) {
                $this->output('Creating list ' . $listConfig['id']);
                $list = ListRecord::create([
                                               'id'    => $listConfig['id'],
                                               'slug'  => $listConfig['id'],
                                               'title' => $listConfig['title'],
                                           ]);
            } elseif ($skipWhenExisting) {
                continue;
            }

            foreach ($listConfig['items'] as $key => $title) {
                $listItem = (new ListItems())->where('list_id', $list->id)
                                             ->where('slug', $key)
                                             ->one();
                $deleted = strpos($title, '**deleted**') === 0;

                if (!$listItem && !$deleted) {
                    $this->output('Creating item ' . $listConfig['id'] . '.' . $key);
                    ListItem::create([
                                         'list_id' => $list->id,
                                         'slug'    => $key,
                                         'value'   => $title,
                                     ]);
                } else if ($listItem) {
                    if ($deleted) {
                        $this->output('Deleting item ' . $listConfig['id'] . '.' . $key);
                        $listItem->delete();
                    } else {
                        $listItem->setAndSave(['value' => $title]);
                    }
                }
            }
        }

        /**
         * Import layouts.
         */
        (new Collection(config('pckg.generic.layouts', [])))->each(function($template, $slug) {
            $layout = Layout::getOrNew(['slug' => $slug]);

            $layout->setAndSave(['template' => $template]);
        });

        /**
         * Import variables.
         */
        (new Collection(config('pckg.generic.variables', [])))->each(function($name, $slug) {
            Variable::getOrCreate(['slug' => $slug]);
        });

        /**
         * Import actions.
         */
        (new Collection(config('pckg.generic.actions', [])))->each(function($action, $slug) {
            $actionRecord = Action::getOrNew(['slug' => $slug]);

            $actionRecord->setAndSave($action);
        });

        /**
         * Import routes.
         */
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

        $this->output('Done');
    }

}