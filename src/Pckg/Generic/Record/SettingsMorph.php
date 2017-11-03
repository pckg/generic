<?php namespace Pckg\Generic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Resolver\Table;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\SettingsMorphs;

class SettingsMorph extends Record
{

    protected $entity = SettingsMorphs::class;

    public function resolve(&$args)
    {
        if ($this->setting_id == 1 || $this->setting_id == 9) {
            $args[] = Reflect::create(Table::class)->resolve($this->value);
        } else if ($this->setting_id == 7) {
            $args[] = (new ActionsMorphs())->where('id', $this->value)->oneOrFail();
        }
    }

    public function registerToConfig()
    {
        if ($this->setting->slug == 'derive.fiscalization.settings') {
            return $this;
        }

        if (!config()->hasKey($this->setting->slug)) {
            /**
             * Don't allow unexistent settings.
             */
            return $this;
        }

        config()->set($this->setting->slug,
                      $this->setting->type == 'array' ? json_decode($this->value, true) : $this->value);

        return $this;
    }

    public function getJsonValueAttribute()
    {
        return json_decode($this->value, true);
    }

    public static function makeItHappen($key, $value, $morph, $poly, $type = null)
    {
        $setting = Setting::getOrCreate(['slug' => $key]);
        $setting->setAndSave(['type' => $type]);
        $settingsMorph = SettingsMorph::getOrCreate([
                                                        'setting_id' => $setting->id,
                                                        'poly_id'    => $poly,
                                                        'morph_id'   => $morph,
                                                    ]);
        $settingsMorph->setAndSave([
                                       'value' => is_array($value) ? json_encode($value) : $value,
                                   ]);
    }

}