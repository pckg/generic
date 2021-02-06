<?php

namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateCollectionsTable extends Migration
{

    public function up()
    {
        $this->collectionsUp();
        $this->collectionItemsUp();

        $this->save();
    }

    protected function collectionsUp()
    {
        $collections = $this->table('collections');

        $collectionsI18n = $this->translatable('collections');
        $collectionsI18n->title();
    }

    protected function collectionItemsUp()
    {
        $collectionItems = $this->table('collection_items');
        $collectionItems->integer('collection_id')->references('collections');
        $collectionItems->timeable();

        $collectionItemsI18n = $this->translatable('collection_items');
        $collectionItemsI18n->title();
        $collectionItemsI18n->subtitle();
        $collectionItemsI18n->text('short_content');
        $collectionItemsI18n->text('content');
    }
}
