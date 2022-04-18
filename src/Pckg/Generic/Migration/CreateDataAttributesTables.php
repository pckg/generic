<?php

namespace Pckg\Generic\Migration;

use Pckg\Auth\Migration\CreateAuthTables;
use Pckg\Migration\Migration;
use Pckg\Translator\Migration\CreateTranslationsTable;

class CreateDataAttributesTables extends Migration
{
    public function up()
    {
        $dataAttributes = $this->morphtable('data_attributes', null, null);
        $dataAttributes->varchar('slug');
        $dataAttributes->longtext('value');
        $dataAttributes->unique('poly_id', 'morph_id', 'slug');
        $this->save();

        return $this;
    }
}
