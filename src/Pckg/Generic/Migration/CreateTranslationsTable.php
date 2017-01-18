<?php namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateTranslationsTable extends Migration
{

    public function up()
    {
        $translationTypes = $this->table('translation_types');
        $translationTypes->slug();

        $translationTypesI18n = $this->translatable('translation_types');
        $translationTypesI18n->title();

        $translations = $this->table('translations');
        $translations->slug();

        $translationsI18n = $this->translatable('translations');
        $translationsI18n->text('value');

        $this->save();
    }

}