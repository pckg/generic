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
        $lists = [
            [
                'id'    => 'actionsMorphs.backgrounds',
                'title' => 'Action backgrounds',
                'items' => [
                    'primary'            => 'Primary',
                    'primary-inverted'   => 'Inverted primary',
                    'secondary'          => 'Secondary',
                    'secondary-inverted' => 'Inverted secondary',
                ],
            ],
            [
                'id'    => 'actionsMorphs.widths',
                'title' => 'Action widths',
                'items' => [
                    'fluid'     => 'Fluid',
                    'container' => 'Container',
                    'small'     => 'Small',
                    'medium'    => 'Medium',
                    'large'     => 'Large',
                ],
            ],
        ];

        foreach ($lists as $listConfig) {
            $list = (new Lists())->where('slug', $listConfig['id'])->one();

            if (!$list) {
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
                    ListItem::create([
                                         'list_id' => $list->id,
                                         'slug'    => $key,
                                         'value'   => $title,
                                     ]);
                }
            }
        }
    }

}