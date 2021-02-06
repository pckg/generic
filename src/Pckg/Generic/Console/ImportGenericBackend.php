<?php

namespace Pckg\Generic\Console;

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
use Symfony\Component\Console\Input\InputOption;

class ImportGenericBackend extends Command
{

    protected function configure()
    {
        $this->setName('generic:import-backend')
             ->setDescription('Import actions, routes, variables, lists, items, ...')
             ->addOptions(
                 [
                              'do' => 'Manually select items to import',
                          ],
                 InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL
             );
    }

    public function importGenericList()
    {
        $languages = localeManager()->getLanguages();
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
                /*foreach ($languages as $language) {
                    runInLocale(function() use ($title, $key, $listConfig, $list, $language) {*/
                        //$translatedTitle = is_array($title) ? ($title[$language->slug] ?? $title['en']) : $title;
                        $translatedTitle = is_array($title) ? $title['en'] : $title;
                        $deleted = strpos($translatedTitle, '**deleted**') === 0;

                if (!$listItem && !$deleted) {
                    $this->output('Creating item ' . $listConfig['id'] . '.' . $key);
                    ListItem::create([
                                         'list_id' => $list->id,
                                         'slug'    => $key,
                                         'value'   => $translatedTitle,
                                     ]);
                } elseif ($listItem) {
                    if ($deleted) {
                        $this->output('Deleting item ' . $listConfig['id'] . '.' . $key);
                        $listItem->delete();
                    } else {
                        $listItem->setAndSave(['value' => $translatedTitle]);
                    }
                }

                /*    }, $language->locale);
                }*/
            }
        }
    }

    public function importLayouts()
    {
        (new Collection(config('pckg.generic.layouts', [])))->each(function ($template, $slug) {
            $layout = Layout::getOrNew(['slug' => $slug]);

            $layout->setAndSave(['template' => $template]);
        });
    }

    public function importMenus()
    {
        (new Collection([['slug' => 'frontend', 'template' => 'frontendMainNav']]))->each(function ($menu, $slug) {
            $menuR = Menu::getOrNew(['slug' => $menu['slug']]);

            if ($menuR->isNew()) {
                $menuR->setAndSave(['template' => $menu['template']]);
            }
        });
    }

    public function importSettingTypes()
    {
        (new Collection([['slug' => 'array']]))->each(function ($settingType, $slug) {
            SettingType::getOrCreate(['slug' => $settingType['slug']]);
        });
    }

    public function importActions()
    {
        (new Collection(config('pckg.generic.actions', [])))->each(function ($action, $slug) {
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
            $this->outputDated('Importing generic list');
            $this->importGenericList();
        }

        /**
         * Import layouts.
         */
        if (!$dos || in_array('layouts', $dos)) {
            $this->outputDated('Importing layouts');
            $this->importLayouts();
        }

        /**
         * Import menus.
         */
        if (!$dos || in_array('menus', $dos)) {
            $this->outputDated('Importing menus');
            $this->importMenus();
        }

        /**
         * Import setting types.
         */
        if (!$dos || in_array('settingTypes', $dos)) {
            $this->outputDated('Setting types');
            $this->importSettingTypes();
        }

        /**
         * Import actions.
         */
        if (!$dos || in_array('actions', $dos)) {
            $this->outputDated('Importing generic actions');
            $this->importActions();
        }

        $this->output('Done');
    }
}
