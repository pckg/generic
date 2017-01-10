<?php namespace Pckg\Generic\Migration;

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
        $listItems->slug();
        // @T00D00 - double index
        $listItems->varchar('value');

        $this->save();
    }

}