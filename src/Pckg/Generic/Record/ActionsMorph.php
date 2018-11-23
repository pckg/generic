<?php namespace Pckg\Generic\Record;

use Complex\Exception;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Service\Partial\AbstractPartial;
use Pckg\Stringify;

class ActionsMorph extends Record
{

    use SettingsHelper;

    protected $entity = ActionsMorphs::class;

    protected $toArray = ['variable', '+content'];

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

    public function addClass($class)
    {
        $setting = Setting::getOrCreate(['slug' => 'pckg.generic.pageStructure.class']);
        $settingsMorph = SettingsMorph::getOrNew([
                                                     'setting_id' => $setting->id,
                                                     'poly_id'    => $this->id,
                                                     'morph_id'   => ActionsMorphs::class,
                                                 ]);

        return $settingsMorph->setAndSave([
                                              'value' => $settingsMorph->value . ($settingsMorph->value ? ' ' : '') .
                                                         $class,
                                          ]);
    }

    public function setTemplateAttribute($template)
    {
        if ($template && (!is_array($template) && !is_string($template))) {
            throw new Exception('Template should be empty, string or array');
        }

        if (!$template) {
            $template = [
                'template' => null,
                'list'     => null,
                'item'     => null,
            ];
        } elseif (is_string($template)) {
            if (substr($template, 0, 1) === '{') {

            } else {
                $template = [
                    'template' => $template,
                    'list'     => null,
                    'item'     => null,
                ];
            }
        }

        $template = is_string($template) ? $template : json_encode($template);
        $this->data['template'] = $template;

        return $this;
    }

    public function getTemplateAttribute() {
        $template = $this->data('template');
        if (!$template) {
            /**
             * @T00D00 - fetch defaults?
             */
            return [
                'template' => null,
                'list' => null,
                'item' => null,
            ];
        }

        if (substr($template, 0, 1) == '{') {
            $template = (array)json_decode($template, true);
            return $template;
        }

        return [
            'template' => $template,
            'list' => null,
            'item' => null,
        ];
    }

    public function getSettingsArrayAttribute()
    {
        $settings = $this->settings
            ->map(function(
                Setting $setting
            ) {
                return [
                    'slug'  => str_replace('pckg.generic.pageStructure.', '',
                                           $setting->slug),
                    'value' => $setting->type == 'array'
                        ? ($setting->pivot->value
                            ? (json_decode($setting->pivot->value, true) ?? [])
                            : [])
                        : $setting->pivot->value,
                ];
            })->keyBy('slug')->map('value');

        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        $pluginDefaults = $this->getPluginSettings();
        foreach ($pluginDefaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        $allClasses = (new Stringify($settings->getKey('class')))->explodeToCollection(' ')
                                                                 ->unique()
                                                                 ->removeEmpty()
                                                                 ->all();
        $scopeClasses = [];
        $otherClasses = [];
        $widthClasses = [];
        $offsetClasses = [];
        $containerClass = '';
        foreach ($allClasses as $class) {
            $found = false;
            foreach (config('pckg.generic.scopes') as $title => $scopes) {
                if (in_array($title, ['Padding', 'Margin'])) {
                    foreach ($scopes as $scps) {
                        if (array_key_exists($class, $scps)) {
                            $scopeClasses[] = $class;
                            $found = true;
                            break 2;
                        }
                    }
                } else {
                    if (array_key_exists($class, $scopes)) {
                        $scopeClasses[] = $class;
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                if (strpos($class, 'col-') === 0 && !strpos($class, '-pull-') && !strpos($class, '-push-')) {
                    if (strpos($class, '-offset-')) {
                        $offsetClasses[] = $class;
                    } else {
                        $widthClasses[] = $class;
                    }
                } elseif (strpos($class, 'container') === 0) {
                    $containerClass = $class;
                } else {
                    $otherClasses[] = $class;
                }
            }
        }

        $settings->push($scopeClasses, 'scopes');
        $settings->push($offsetClasses, 'offset');
        $settings->push($widthClasses, 'width');
        $settings->push($containerClass, 'container');
        $settings->push(implode(' ', $otherClasses), 'class');

        /**
         * Add path before image.
         */
        $settings->push(media($settings->getKey('bgImage'), null, true, path('app_uploads')) ?? '', 'bgImage');

        /**
         * We also need to fetch some settings which are saved on layout.
         */
        /*if ($actionsMorph->morph_id == Layouts::class) {
            $layout = Layout::gets(['id' => $actionsMorph->poly_id]);
            if ($layout) {
                $settings->push($layout->getSettingValue('pckg.generic.pageStructure.wrapperLockHide'),
                                'wrapperLockHide');
                $settings->push($layout->getSettingValue('pckg.generic.pageStructure.wrapperLockShow'),
                                'wrapperLockShow');
            }
        }*/

        return $settings;
    }

    public function getDefaultSettings()
    {
        /**
         * Add defaults.
         *
         * @T00D00
         * This should be refactored to plugins. ;-)
         */
        return [
            'class'             => '',
            'container'         => '',
            'style'             => '',
            'width'             => [], // column
            'offset'            => [], // column
            'bgColor'           => '',
            'bgImage'           => '',
            'bgSize'            => '',
            'bgAttachment'      => '',
            'bgRepeat'          => '',
            'bgPosition'        => '',
            'bgVideo'           => '',
            'bgVideoSource'     => '',
            'bgVideoDisplay'    => '',
            'bgVideoAutoplay'   => '',
            'bgVideoControls'   => '',
            'bgVideoLoop'       => '',
            'bgVideoMute'       => '',
            'wrapperLockShow'   => [],
            'wrapperLockHide'   => [],
            'wrapperLockSystem' => [],
        ];
    }

    protected function getPluginSettings()
    {
        $separate = [];
        foreach (config('pckg.generic.actions') as $actionKey => $actionSettings) {
            foreach ($actionSettings['settings'] ?? [] as $settingKey => $settingType) {
                $separate[$settingKey] = $settingType == 'array' ? [] : '';
            }
        }

        return [
            'sourceOffers'    => [],
            'sourcePackets'   => [],
            'sourceGalleries' => [],
        ];
    }

}