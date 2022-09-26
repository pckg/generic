<?php

namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateListTables extends Migration
{

    protected $dependencies = [
        // translatable, permissionable
    ];
    public function up()
    {
        $lists = $this->table('lists', false);
        $lists->idString();
        $lists->slug();
        $lists->title();
        $listItems = $this->table('list_items');
        $listItems->varchar('list_id')->references('lists');
        $listItems->slug('slug', 128, false);
    // @T00D00 - double index
        $listItems->varchar('value');
        $listItems->unique('slug', 'list_id');
    /*$listItemsI18n = $this->translatable('list_items');
        $listItemsI18n->varchar('value');*/

        $this->save();

        return $this;
    }
}
