<?php namespace Pckg\Generic\Record;

trait SettingsHelper
{

    public function saveSetting($key, $value, $type = null)
    {
        $setting = Setting::getOrNew(['slug' => $key]);

        if ($setting->isNew()) {
            $setting->setAndSave(['type' => $type]);
        }

        $settingsMorph = SettingsMorph::getOrCreate([
                                                        'setting_id' => $setting->id,
                                                        'morph_id'   => static::$entity,
                                                        'poly_id'    => $this->id,
                                                    ]);

        $settingsMorph->setAndSave(['value' => $value]);

        return $this;
    }

    public function getSetting($slug)
    {
        return $this->settings->first(function(Setting $setting) use ($slug) {
            return $setting->slug == $slug;
        });
    }

}