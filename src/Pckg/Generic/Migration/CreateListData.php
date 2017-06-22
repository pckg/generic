<?php namespace Pckg\Generic\Migration;

use Pckg\Generic\Entity\ListItems;
use Pckg\Generic\Entity\Lists;
use Pckg\Generic\Record\ListItem;
use Pckg\Generic\Record\ListRecord;
use Pckg\Migration\Migration;

class CreateListData extends Migration
{

    public function up()
    {
        $lists = config('pckg.generic.lists', []);

        foreach ($lists as $listConfig) {
            $list = (new Lists())->where('slug', $listConfig['id'])->one();

            if (!$list) {
                $this->output('Creating list ' . $listConfig['id']);
                $list = ListRecord::create([
                                               'id'    => $listConfig['id'],
                                               'slug'  => $listConfig['id'],
                                               'title' => $listConfig['title'],
                                           ]);
            }

            foreach ($listConfig['items'] as $key => $title) {
                $listItem = (new ListItems())->where('list_id', $list->id)
                                             ->where('slug', $key)
                                             ->one();

                if (!$listItem) {
                    $this->output('Creating item ' . $listConfig['id'] . '.' . $key);
                    ListItem::create([
                                         'list_id' => $list->id,
                                         'slug'    => $key,
                                         'value'   => $title,
                                     ]);
                } else {
                    $listItem->setAndSave(['value' => $title]);
                }
            }
        }
    }

}