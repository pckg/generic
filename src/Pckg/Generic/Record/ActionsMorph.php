<?php namespace Pckg\Generic\Record;

use Complex\Exception;
use Derive\Layout\Command\GetLessVariables;
use Derive\Newsletter\Controller\Newsletter;
use Pckg\Collection;
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
        } elseif (is_string($content)) {
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

    public function getTemplateAttribute()
    {
        $template = $this->data('template');

        if (!$template) {
            return $this->fillTemplateSettings([
                                                   'template' => null,
                                                   'list'     => null,
                                                   'item'     => null,
                                                   'slot'     => null,
                                               ]);
        }

        if (substr($template, 0, 1) == '{') {
            $template = (array)json_decode($template, true);

            return $this->fillTemplateSettings($template);
        }

        return $this->fillTemplateSettings([
                                               'template' => $template,
                                               'list'     => null,
                                               'item'     => null,
                                               'slot'     => null,
                                           ]);
    }

    public function fillTemplateSettings($template)
    {
        $configKey = 'pckg.generic.templates.' . $this->action->class . '.' . $this->action->method;
        $config = config($configKey, []);

        if (!$config) {
            return $template;
        }

        /**
         * If config exists set first template if wrong template is set or template is not existent.
         */
        if (!array_key_exists('template', $template)) {
            $template['template'] = null;
        }

        if (!$template['template'] || !isset($config[$template['template']])) {
            $template['template'] = array_keys($config)[0];
        }

        if (!is_string($config[$template['template']])) {
            $subconfig = $config[$template['template']];

            if (isset($subconfig['item'])) {
                if (!isset($template['item']) || !isset($subconfig['item'][$template['item']])) {
                    $template['item'] = array_keys($subconfig['item'])[0];
                }
            }

            if (isset($subconfig['list']) || isset($subconfig['item'])) {
                $listTemplates = config('pckg.generic.templateEngine.list', []);
                if (!isset($template['list']) || !isset($subconfig['list'][$template['list']])) {
                    $template['list'] = array_keys($subconfig['list'] ?? $listTemplates)[0];
                }
            }
        }

        if (!isset($template['slot'])) {
            $template['slot'] = null;
        }

        return $template;
    }

    public function getSettingsArrayAttribute()
    {
        /**
         * Map settings by clean slug and value.
         */
        $settings = $this->settings->map(function(
            Setting $setting
        ) {
            return [
                'slug'  => str_replace('pckg.generic.pageStructure.', '', $setting->slug),
                'value' => $setting->type == 'array' ? ($setting->pivot->value ? (json_decode($setting->pivot->value,
                                                                                              true) ?? []) : [])
                    : $setting->pivot->value,
            ];
        })->keyBy('slug')->map('value');

        /**
         * Merge default settings to settings.
         */
        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        /**
         * Merge plugin settings to settings.
         */
        $pluginDefaults = $this->getPluginSettings();
        foreach ($pluginDefaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        /**
         * Get all custom classes.
         */
        $allClasses = (new Stringify($settings->getKey('class')))->explodeToCollection(' ')
                                                                 ->unique()
                                                                 ->removeEmpty()
                                                                 ->all();

        /**
         * Split classes by type.
         */
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

        /**
         * Set proper settings.
         */
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

        if ($this->action->slug == 'pckg-mail-mailchimp-enews') {
            $settings->push((new Newsletter())->getActionConsentsAction($this)['consents'],
                            'pckg.generic.actions.pckg-mail-mailchimp-enews.consents');
        }

        /**
         * Parse settings to attributes.
         */
        $this->parseToAttributes($settings);

        return $settings;
    }

    public function parseToAttributes(Collection $settings)
    {
        /**
         * There are scope classes that should be converted to custom style.
         * This should be moved to migration and migrate all platforms to new definition.
         */
        $scopes = $settings['scopes'] ?? [];
        $classes = explode(' ', $settings['class'] ?? '');
        $attributes = [
            'default'     => [],
            'desktop'     => [],
            'laptop'      => [],
            'tablet'      => [],
            'mobile'      => [],
            'smallMobile' => [],
        ];
        
        /**
         * We want to get rid of scope classes and add attributes to css selector.
         */
        $scopes = (new GetLessVariables())->parseAttributes($scopes, $attributes);
        $classes = (new GetLessVariables())->parseAttributes($classes, $attributes);

        $finalAttributes = [];
        foreach ($attributes as $device => $attrs) {
            $finalAttributes[] = [
                'device' => $device,
                'selector' => '.__action-' . $this->id,
                'css' => $attrs,
            ];
        }

        $settings->push($finalAttributes, 'attributes');
        $settings->push($scopes, 'scopes');
        $settings->push(implode(' ', $classes), 'class');
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
            'animation'         => [
                'event'     => null,
                'effect'    => null,
                'infinite'  => false,
                'delay'     => null,
                'threshold' => 80,
            ],
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

    public function getBuildAttribute()
    {
        if ($this->hasKey('build')) {
            return $this->data('build');
        }

        $build = $this->buildHtml();
        $this->set('build', $build);

        return $build;
    }

    public function buildHtml($args = [])
    {
        if ($this->type != 'action') {
            return;
        }

        try {
            $build = $this->action->build($args);
            $this->set('build', $build);

            return $build;
        } catch (\Throwable $e) {
            if (!prod()) {
                throw $e;
            }
        }
    }

    public function overloadViewTemplate($result)
    {
        if (!($result instanceof View\Twig)) {
            return;
        }

        /**
         * Awh, and check for allowed templates. :)
         */
        if (!$this->template['template']) {
            return;
        }

        /**
         * In template we store template, list template and item template designs.
         */
        message('Using action template ' . $newFile . ' ' . $this->action->slug);
        $newFile = str_replace(':', '/View/', $this->template['template']);
        $result->setFile($newFile);
    }

    public function resolveSettings(&$args = [])
    {
        measure('Resolving', function() use (&$args) {
            if (isset($args['settings'])) {
                $args['settings']->each(function(Setting $setting) use (&$args) {
                    $setting->pivot->resolve($args);
                });
            }

            foreach ($args['resolved'] ?? [] as $key => $val) {
                $args[$key] = $val;
            }

            /**
             * Proper resolve by setting implementation, remove others.
             */
            $actionsMorphResolver = $this->settings->keyBy('slug')->getKey('pckg.generic.actionsMorph.resolver');
            if ($actionsMorphResolver) {
                foreach ($actionsMorphResolver->pivot->getJsonValueAttribute() as $key => $conf) {
                    if (isset($conf['resolver'])) {
                        /**
                         * @deprecated
                         */
                        $args[$key] = Reflect::create($conf['resolver'])->resolve($conf['value']);
                    } elseif (is_array($conf)) {
                        $resolver = array_keys($conf)[0];
                        $args[$key] = Reflect::create($resolver)->resolve($conf[$resolver]);
                    }
                }
            }
        });
    }

    public function overloadType()
    {
        if ($this->type) {
            return $this->type;
        }

        $method = $this->action->method;
        if (in_array($method, ['wrapper', 'container', 'row', 'column'])) {
            return $this->action->method;
        }

        return 'action';
    }

    public function jsonSerialize()
    {
        $config = config('pckg.generic.actions.' . $this->action->slug, []);
        $slots = $config['slots'] ?? [];
        $content = $this->content ? $this->content->jsonSerialize() : null;
        $type = $this->overloadType();

        $data = [
            'id'        => $this->id,
            'title'     => $this->action->title,
            'morph'     => $this->morph_id,
            'type'      => $type,
            'slug'      => $this->action->slug,
            'parent_id' => $this->parent_id,
            'class'     => $this->action->class,
            'method'    => $this->action->method,
            'settings'  => $this->settingsArray,
            'content'   => $content,
            'build'     => $this->getBuildAttribute(),
            'template'  => $this->template,
            'order'     => strpos($this->morph, 'Layout') !== false ? $this->order + 999999 : $this->order,
            'focus'     => false,
            'active'    => false,
            'slots'     => $slots,
            'component' => 'pckg-' . $type,
            'config'    => $config['config'] ?? null,
        ];

        return $data;
    }

    public function flattenForGenericResponse(Collection $collection)
    {
        $action = $this->action;
        $action->pivot = $this;
        $collection->push($this);

        foreach ($this->subActions as $action) {
            $action->flattenForGenericResponse($collection);
        }

        return $collection;
    }

    public function getMostParentAttribute()
    {
        if (!$this->parent_id) {
            return $this;
        }

        return $this->parentAction->mostParent;
    }

    public function cloneRecursively($overwrite = [])
    {
        /**
         * Clone content.
         */
        $contentId = $this->content_id;
        if ($contentId) {
            $overwrite['content_id'] = $this->content->saveAs()->id;
        }
        /**
         * Clone actions morph.
         */
        $newActionsMorph = $this->saveAs($overwrite);
        /**
         * Clone settings.
         */
        $this->settings->each(function(Setting $setting) use ($newActionsMorph) {
            $setting->pivot->saveAs(['poly_id' => $newActionsMorph->id]);
        });
        /**
         * Clone subactions.
         */
        $this->subActions->each(function(ActionsMorph $subaction) use ($newActionsMorph) {
            $subaction->cloneRecursively(['parent_id' => $newActionsMorph->id]);
        });

        return $newActionsMorph;
    }

}