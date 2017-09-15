<?php namespace Pckg\Generic\Migration;

use Pckg\Migration\Migration;

class CreateSettingsTable extends Migration
{

    public function up()
    {
        $settingTypes = $this->table('setting_types');
        $settingTypes->slug();

        $settingTypesI18n = $this->translatable('setting_types');
        $settingTypesI18n->title();

        $settings = $this->table('settings');
        $settings->varchar('type')->references('setting_types', 'slug');

        $settingsI18n = $this->translatable('settings');
        $settingsI18n->text('value');

        $settingsMorphs = $this->morphtable('settings', 'setting_id');
        $settingsMorphs->text('value');

        $this->save();
    }

}