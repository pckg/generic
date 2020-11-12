<?php namespace Pckg\Generic\Record;

use Comms\Hub\Api;
use Complex\Exception;
use Derive\Layout\Command\GetLessVariables;
use Derive\Newsletter\Controller\Newsletter;
use Pckg\Collection;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Service\Generic;
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

        if ($content['contents'] ?? null) {
            foreach ($content['contents'] as $subcontent) {
                $subcontent['parent_id'] = $content->id;
                $this->createNewContent($subcontent);
            }
        }

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

    public function addHubShare($share, $defaults = [])
    {
        /**
         * @var $generic Generic
         */
        $generic = resolve(Generic::class);
        $partial = $generic->prepareHubPartial($share);

        return $partial->add($this, null, $defaults);
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

    /**
     * Return object with template, item, list and slot keys.
     *
     * @return mixed
     */
    public function getTemplateAttribute()
    {
        $template = $this->data('template');

        /**
         * Set default settings for action.
         */
        if (!$template) {
            return $this->fillTemplateSettings([
                                                   'list'     => null,
                                                   'item'     => null,
                                                   'slot'     => null,
                                               ]);
        }

        /**
         * Something is set, check if everything is okay.
         */
        if (substr($template, 0, 1) == '{') {
            $template = (array)json_decode($template, true);

            return $this->fillTemplateSettings($template);
        }

        /**
         * We have only template selected, migrate to object structure?
         */
        return $this->fillTemplateSettings([
                                               'list'     => null,
                                               'item'     => null,
                                               'slot'     => null,
                                           ]);
    }

    /**
     * Update template settings with correct settings.
     *
     * @param $template
     * @return mixed
     */
    public function fillTemplateSettings($template)
    {
        $configKey = 'pckg.generic.templates.' . $this->action->class . '.' . $this->action->method;
        $config = config($configKey, []);

        if (!$config) {
            return $template;
        }

        /**
         * Set default slot for slotted actions.
         */
        if (!isset($template['slot'])) {
            $template['slot'] = null;
        }

        /**
         * This can be decided on frontend?
         */
        if ($config) {
            $engine = $config['engine'] ?? [];

            /**
             * Set default item template.
             */
            if (in_array('item', $engine)) {
                $itemTemplates = config('derive.library.shares.item', []);
                if (!in_array($template['item'] ?? null, array_keys($itemTemplates))) {
                    $template['item'] = array_keys($itemTemplates)[0] ?? null;
                }
            } else if (isset($config['item'])) {
                if (!in_array($template['item'] ?? null, array_keys($config['item']))) {
                    $template['item'] = array_keys($config['item'])[0] ?? null;
                }
            }

            /**
             * Set default list template.
             */
            if (in_array('list', $engine)) {
                $listTemplates = config('derive.library.shares.list', []);
                if (!in_array($template['list'] ?? null, array_keys($listTemplates))) {
                    $template['list'] = array_keys($listTemplates)[0] ?? null;
                }
            } else if (isset($config['list'])) {
                if (!in_array($template['list'] ?? null, array_keys($config['list']))) {
                    $template['list'] = array_keys($config['list'])[0] ?? null;
                }
            }
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
                'value' => $setting->type == 'array'
                    ? ($setting->pivot->value
                        ? (json_decode($setting->pivot->value, true) ?? [])
                        : []
                    )
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
        $allClasses = (new Stringify($settings->getKey('class')))->explodeToCollection(' ')->unique()->filter(function(
            $class
        ) {
            return substr(strrev($class), 0, 1) !== '-';
        })->removeEmpty()->all();

        /**
         * Set proper settings.
         */
        $settings->push($allClasses, 'classes');

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
        $classes = $settings['classes'];
        $attributes = [
            'default'     => [],
            'desktop'     => [],
            'laptop'      => [],
            'tablet'      => [],
            'smallTablet' => [],
            'mobile'      => [],
        ];

        /**
         * We want to get rid of scope classes and add attributes to css selector.
         */
        $classes = (new GetLessVariables())->parseAttributes($classes, $attributes);

        /**
         * Db attributes.
         */
        foreach ($settings->getKey('attributes', []) as $attr) {
            foreach ($attr['css'] as $a => $v) {
                $device = $attr['device'] === 'smallMobile' ? 'mobile' : $attr['device'];
                $state = $attr['state'] ?? 'nostate';
                $attributes[$device][$state][$a] = $v;
            }
        }

        /**
         * Array of final attributes.
         */
        $finalAttributes = [];
        foreach ($attributes as $device => $states) {
            foreach ($states as $state => $attrs) {
                $state = ($state === 'nostate' ? null : $state);
                $finalAttributes[] = [
                    'device'   => $device,
                    'selector' => '.__action-' . $this->id,
                    'state' => $state,
                    'css'      => $attrs,
                ];
            }
        }

        /**
         * We also need to add existing attributes.
         */

        $settings->push($finalAttributes, 'attributes');
        $settings->push($classes, 'classes');
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
            'attributes'        => [],
            'style'             => '',
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
            'bgVideoBranding'   => '',
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
        measure('Resolving #' . $this->id, function() use (&$args) {
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
        return measure('Serializing action #' . $this->id, function() {
            $config = config('pckg.generic.actions.' . $this->action->slug, []);
            $slots = $config['slots'] ?? [];
            $content = $this->content ? $this->content->jsonSerialize() : null;
            $type = $this->overloadType();

            $data = [
                'id'           => $this->id,
                'title'        => $this->action->title,
                'slug'         => $this->action->slug,
                'class'        => $this->action->class,
                'method'       => $this->action->method,
                'morph'        => $this->morph_id,
                'type'         => $type,
                'component'    => 'pckg-' . $type,
                'parent_id'    => $this->parent_id,
                'settings'     => $this->settingsArray,
                'content'      => $content,
                'build'        => $this->getBuildAttribute(),
                'template'     => $this->template,
                'variable'     => $this->variable ?? ($this->morph_id === Layouts::class ? 'footer' : 'content'),
                'order'        => strpos($this->morph, 'Layout') !== false ? $this->order + 999999 : $this->order,
                'focus'        => false,
                'active'       => false,
                'slots'        => $slots,
                'config'       => $config['config'] ?? null,
                'raw'          => !!($config['raw'] ?? null),
                'capabilities' => $config['capabilities'] ?? [],
                'customTemplate' => '<div><p>This is title {{ content.title }}</p>And content:<div v-html="content.content"></div></div>',
            ];

            return $data;
        });
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