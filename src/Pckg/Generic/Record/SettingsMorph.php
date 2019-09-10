<?php namespace Pckg\Generic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Resolver\Table;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Settings;
use Pckg\Generic\Entity\SettingsMorphs;

class SettingsMorph extends Record
{

    protected $entity = SettingsMorphs::class;

    public function resolve(&$args)
    {
        if ($this->setting->slug == 'resolve-table' ||
            $this->setting->slug == 'pckg-generic-routes-page-structure-actionsmorph'
        ) {
            $args[] = Reflect::create(Table::class)->resolve($this->value);
        } else if (false && $this->setting_id == 7) {
            $args[] = (new ActionsMorphs())->where('id', $this->value)->oneOrFail();
        }
    }

    public function registerToConfig()
    {
        $slug = $this->setting->slug;
        if ($slug == 'derive.fiscalization.settings') {
            return $this;
        }

        if (!config()->hasKey($slug)) {
            /**
             * Don't allow unexistent settings.
             */
            return $this;
        }

        $newValue = $this->setting->type == 'array'
            ? json_decode($this->value, true)
            : $this->value;
        $merge = true;
        $current = config($slug);
        if (is_associative_array($current)) {
            $merge = false;
        }
        config()->set($slug, $newValue, $merge);

        return $this;
    }

    public function getJsonValueAttribute()
    {
        return json_decode($this->value, true);
    }

    public function getFinalValueAttribute()
    {
        return $this->setting->type == 'array' ? $this->getJsonValueAttribute() : $this->value;
    }

    public static function makeItHappen($key, $value, $morph, $poly, $type = null)
    {
        $setting = Setting::getOrNew(['slug' => $key]);
        if (!$setting->id) {
            $setting->setAndSave(['type' => $type ? $type : (is_array($value) ? 'array' : null)]);
        }
        $settingsMorph = SettingsMorph::getOrNew([
                                                     'setting_id' => $setting->id,
                                                     'poly_id'    => $poly,
                                                     'morph_id'   => $morph,
                                                 ]);
        $settingsMorph->setAndSave([
                                       'value' => is_array($value) ? json_encode($value) : $value,
                                   ]);
    }

    public static function getSettingOrDefault($slug, $morph, $poly, $default = null)
    {
        $setting = (new Settings())->where('slug', $slug)->one();

        if (!$setting) {
            return $default;
        }

        $settingsMorph = (new SettingsMorphs())->where('setting_id', $setting->id)
                                               ->where('morph_id', $morph)
                                               ->where('poly_id', $poly)
                                               ->one();

        if (!$settingsMorph) {
            return $default;
        }

        return $settingsMorph->getFinalValueAttribute();
    }

}