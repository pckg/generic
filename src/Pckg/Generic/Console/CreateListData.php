<?php namespace Pckg\Generic\Console;

use Pckg\Framework\Console\Command;
use Pckg\Generic\Entity\ListItems;
use Pckg\Generic\Entity\Lists;
use Pckg\Generic\Record\ListItem;
use Pckg\Generic\Record\ListRecord;

class CreateListData extends Command
{

    protected function configure()
    {
        $this->setName('generic:import-lists')
             ->setDescription('Impor pckg.generic.lists');
    }

    public function up()
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
    }

}