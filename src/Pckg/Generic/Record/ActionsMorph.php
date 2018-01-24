<?php namespace Pckg\Generic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Service\Partial\AbstractPartial;

class ActionsMorph extends Record
{

    protected $entity = ActionsMorphs::class;

    protected $toArray = ['variable'];

    public function lockToLayout()
    {
        foreach ($this->subActions as $subAction) {
            $subAction->lockToLayout();
        }

        $this->setAndSave([
                              'morph_id' => Layouts::class,
                              'poly_id'  => 2,
                          ]);
    }

    public function lockToRoute(Route $route)
    {
        foreach ($this->subActions as $subAction) {
            $subAction->lockToRoute($route);
        }

        $this->setAndSave([
                              'morph_id' => Routes::class,
                              'poly_id'  => $route->id,
                          ]);
    }

    public function moveToRoute(Route $route)
    {
        $this->setAndSave(['morph_id' => Routes::class, 'poly_id' => $route->id]);
        $parent = $this->parent;
        if ($parent) {
            $parent->moveToRoute($route);
        }
    }

    public function deleteWidely()
    {
        /**
         * Delete content only if only usage.
         */
        if ($this->content_id) {
            $usages = (new ActionsMorphs())->where('content_id', $this->content_id)->total();
            if (!$usages == 1) {
                //$this->content->delete();
            }
        }

        /**
         * Delete all child actions with contents.
         */
        (new ActionsMorphs())->where('parent_id', $this->id)->all()->each->deleteWidely();

        /**
         * Delete this action
         */
        $this->delete();
    }

    public function saveSetting($key, $value, $type = null)
    {
        $setting = Setting::getOrNew(['slug' => $key]);

        if ($setting->isNew()) {
            $setting->setAndSave(['type' => $type]);
        }

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
                ? $this->content->data()
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

    public function createNewContent($content = null)
    {
        if (!$content) {
            $content = [];
        } else if (is_string($content)) {
            $content = ['content' => $content];
        }

        $content = array_merge([
                                   'title' => 'Content #' . $this->id,
                               ], $content);

        $content = Content::create($content);

        $this->setAndSave(['content_id' => $content->id]);
    }

    /**
     * @param $partial
     *
     * @return ActionsMorph
     */
    public function addPartial($partial)
    {
        $partial = $this->preparePartial($partial);

        return $partial->add($this);
    }

    /**
     * @param $partial
     *
     * @return object|AbstractPartial
     */
    protected function preparePartial($partial)
    {
        return Reflect::create($partial);
    }

}