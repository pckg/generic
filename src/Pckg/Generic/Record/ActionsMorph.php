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

    public function export()
    {
        /**
         * @T00D00 - export permissions
         *         - export contents (translations)
         */
        $data = $this->data();
        $settings = $this->settings->toArray();

        return [
            'parent_id' => $this->parent_id,
            'id'        => $this->id,
            'data'      => $data,
            'settings'  => $settings,
            'content'   => $this->content_id ? $this->content->data() : [],
        ];
    }

    public static function import($export, Route $route)
    {
        $data = $export['data'];
        unset($data['id']);

        /**
         * Clone content.
         */
        if ($data['content_id']) {
            $content = $data['content'];
            unset($content['id']);
            $content = Content::create($content);
            $data['content_id'] = $content->id;
        }

        /**
         * Set new route id.
         */
        $data['poly_id'] = $route->id;

        /**
         * Clone actions morph.
         */
        $actionsMorph = ActionsMorph::create($data);

        /**
         * Clone settings.
         */
        foreach ($export['settings'] ?? [] as $setting) {
            $setting['data']['poly_id'] = $actionsMorph->id;
            unset($setting['data']['id']);
            SettingsMorph::create($setting['data']);
        }

        /**
         * Clone subactions.
         */
        foreach ($export['actions'] ?? [] as $subaction) {
            $subaction['data']['parent_id'] = $actionsMorph->id;
            ActionsMorph::import($subaction, $route);
        }

        return $actionsMorph;
    }

}