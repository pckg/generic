<?php namespace Pckg\Generic\Console;

use Pckg\Collection;
use Pckg\Framework\Console\Command;
use Pckg\Generic\Entity\ListItems;
use Pckg\Generic\Entity\Lists;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\Layout;
use Pckg\Generic\Record\ListItem;
use Pckg\Generic\Record\ListRecord;
use Pckg\Generic\Record\Menu;
use Pckg\Generic\Record\SettingType;
use Pckg\Generic\Record\Variable;
use Symfony\Component\Console\Input\InputOption;

class ImportGenericBackend extends Command
{

    protected function configure()
    {
        $this->setName('generic:import-backend')
             ->setDescription('Import actions, routes, variables, lists, items, ...')
             ->addOptions([
                              'do' => 'Manually select items to import',
                          ],
                          InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL);
    }

    public function importGenericList()
    {
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
                $listItem = (new ListItems())->where('list_id', $list->id)->where('slug', $key)->one();
                $deleted = strpos($title, '**deleted**') === 0;

                if (!$listItem && !$deleted) {
                    $this->output('Creating item ' . $listConfig['id'] . '.' . $key);
                    ListItem::create([
                                         'list_id' => $list->id,
                                         'slug'    => $key,
                                         'value'   => $title,
                                     ]);
                } elseif ($listItem) {
                    if ($deleted) {
                        $this->output('Deleting item ' . $listConfig['id'] . '.' . $key);
                        $listItem->delete();
                    } else {
                        $listItem->setAndSave(['value' => $title]);
                    }
                }
            }
        }
    }

    public function importLayouts()
    {

        (new Collection(config('pckg.generic.layouts', [])))->each(function($template, $slug) {
            $layout = Layout::getOrNew(['slug' => $slug]);

            $layout->setAndSave(['template' => $template]);
        });
    }

    public function importVariables()
    {
        (new Collection(config('pckg.generic.variables', [])))->each(function($name, $slug) {
            Variable::getOrCreate(['slug' => $slug]);
        });
    }

    public function importMenus()
    {
        (new Collection([['slug' => 'frontend', 'template' => 'frontendMainNav']]))->each(function($menu, $slug) {
            $menuR = Menu::getOrNew(['slug' => $menu['slug']]);

            if ($menuR->isNew()) {
                $menuR->setAndSave(['template' => $menu['template']]);
            }
        });
    }

    public function importSettingTypes()
    {
        (new Collection([['slug' => 'array']]))->each(function($settingType, $slug) {
            SettingType::getOrCreate(['slug' => $settingType['slug']]);
        });
    }

    public function importActions()
    {
        (new Collection(config('pckg.generic.actions', [])))->each(function($action, $slug) {
            $actionRecord = Action::getOrNew(['slug' => $slug]);

            $actionRecord->setAndSave($action);
        });
    }

    public function handle()
    {
        /**
         * Check if we should import selectively.
         */
        $dos = $this->option('do');

        /**
         * Import generic lists and items.
         */
        if (!$dos || in_array('lists', $dos)) {
            $this->importGenericList();
        }

        /**
         * Import layouts.
         */
        if (!$dos || in_array('layouts', $dos)) {
            $this->importLayouts();
        }

        /**
         * Import variables.
         */
        if (!$dos || in_array('variables', $dos)) {
            $this->importVariables();
        }

        /**
         * Import menus.
         */
        if (!$dos || in_array('menus', $dos)) {
            $this->importMenus();
        }

        /**
         * Import setting types.
         */
        if (!$dos || in_array('settingTypes', $dos)) {
            $this->importSettingTypes();
        }

        /**
         * Import actions.
         */
        if (!$dos || in_array('actions', $dos)) {
            $this->importActions();
        }

        $this->output('Done');
    }

}