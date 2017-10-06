<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Generic\Entity\Actions;
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
        $data['action_slug'] = $this->action->slug;
        $settings = $this->settings->map(function(Setting $setting) {
            $data = $setting->pivot->data();
            $data['slug'] = $setting->slug;

            return $data;
        })->toArray();

        return [
            'parent_id' => $this->parent_id,
            'id'        => $this->id,
            'data'      => $data,
            'settings'  => $settings,
            'content'   => $this->content_id
                ? $this->content(function(BelongsTo $content) { $content->joinTranslations(); })->data()
                : [],
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
            $content = $export['content'];
            unset($content['id']);
            $content = Content::create($content);
            $data['content_id'] = $content->id;
        }

        /**
         * Set new route id.
         */
        $data['poly_id'] = $route->id;
        $data['action_id'] = Action::getOrCreate(['slug' => $data['action_slug']])->id;

        /**
         * Clone actions morph.
         */
        $actionsMorph = ActionsMorph::create($data);

        /**
         * Clone settings.
         */
        foreach ($export['settings'] ?? [] as $setting) {
            $setting['setting_id'] = Setting::getOrCreate(['slug' => $setting['slug']])->id;
            $setting['poly_id'] = $actionsMorph->id;
            unset($setting['id']);
            SettingsMorph::create($setting);
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