<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\ActionsMorphs;

class ActionsMorph extends Record
{

    protected $entity = ActionsMorphs::class;

    protected $toArray = ['variable'];

    public function saveSetting($key, $value)
    {
        $setting = Setting::getOrCreate(['slug' => $key]);

        $settingsMorph = SettingsMorph::getOrCreate([
                                                        'setting_id' => $setting->id,
                                                        'morph_id'   => ActionsMorphs::class,
                                                        'poly_id'    => $this->id,
                                                    ]);

        $settingsMorph->setAndSave(['value' => $value]);

        return $this;
    }

}