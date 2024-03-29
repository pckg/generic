<?php

namespace Pckg\Generic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Resolver\Table;
use CommsCenter\Pagebuilder\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Settings;
use Pckg\Generic\Entity\SettingsMorphs;

/**
 * Class SettingsMorph
 * @package Pckg\Generic\Record
 * @property Setting $setting
 * @property string $value
 * @property int $setting_id
 */
class SettingsMorph extends Record
{
    protected $entity = SettingsMorphs::class;

    public function resolve(&$args)
    {
        if (
            $this->setting->slug == 'resolve-table' ||
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
        return $this->setting->type === 'array' || $this->setting->type === 'object'
            ? $this->getJsonValueAttribute()
            : $this->value;
    }

    /**
     * @param      $key
     * @param      $value
     * @param      $morph
     * @param      $poly
     * @param null $type
     *
     * @return SettingsMorph
     */
    public static function makeItHappen($key, $value, $morph, $poly, $type = null)
    {
        $setting = Setting::getOrNew(['slug' => $key]);
        if (!$setting->id) {
            $setting->setAndSave(['type' => $type ? $type : (is_array($value) || is_object($value) ? 'array' : null)]);
        }
        $settingsMorph = SettingsMorph::getOrNew([
                                                     'setting_id' => $setting->id,
                                                     'poly_id'    => $poly,
                                                     'morph_id'   => $morph,
                                                 ]);
        $settingsMorph->setAndSave([
                                       'value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
                                   ]);

        return $settingsMorph;
    }

    /**
     * @param $key
     * @param $value
     * @param $morph
     * @param $poly
     *
     * @return SettingsMorph
     */
    public static function makeItPush($key, $value, $morph, $poly)
    {
        $setting = Setting::getOrNew(['slug' => $key]);
        if (!$setting->id) {
            $setting->setAndSave(['type' => 'array']);
        }
        $settingsMorph = SettingsMorph::getOrNew([
                                                     'setting_id' => $setting->id,
                                                     'poly_id'    => $poly,
                                                     'morph_id'   => $morph,
                                                 ]);
        $finalValue = collect($settingsMorph->getFinalValueAttribute())->push($value)->unique()->all();
        $settingsMorph->setAndSave([
                                       'value' => json_encode($finalValue),
                                   ]);

        return $settingsMorph;
    }

    public static function getSettingOrDefault($slug, $morph, $poly, $default = null)
    {
        $setting = (new Settings())->where('slug', $slug)->one();

        if (!$setting) {
            return $default;
        }

        $settingsMorph = (new SettingsMorphs())->where('setting_id', $setting->id)
            ->where('morph_id', [$morph, str_replace('CommsCenter\\Pagebuilder', 'Pckg\\Generic', $morph)])
            ->where('poly_id', $poly)
            ->one();

        if (!$settingsMorph) {
            return $default;
        }

        return $settingsMorph->getFinalValueAttribute();
    }
}
