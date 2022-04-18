<?php

namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateLanguagesTable extends Migration
{
    protected $dependencies = [
        // translatable, permissionable
    ];
    public function up()
    {
        $this->languagesUp();
        $this->save();

        return $this;
    }

    protected function languagesUp()
    {
        $languages = $this->table('languages');
/**
         * @T00D00 - add index for relations on slug
         */
        $languages->slug();
        $languages->varchar('flag');
        $languages->varchar('floating_point', 1);
        $languages->varchar('thousand_separator', 1);
        $languages->varchar('currency', 3);
        $languages->varchar('locale', 5);
        $languages->varchar('domain');
        $languages->boolean('frontend');
        $languages->boolean('backend');
        $languages->boolean('default');
        $languagesI18n = $this->translatable('languages');
        $languagesI18n->title();
    }
}
