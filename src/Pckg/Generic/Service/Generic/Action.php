<?php

namespace Pckg\Generic\Service\Generic;

use Exception;
use Pckg\Concept\Reflect;
use Pckg\Framework\Service\Plugin;
use Pckg\Framework\View;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action as ActionRecord;
use Pckg\Generic\Record\Content;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Record\Setting;
use Throwable;

/**
 * Class Action
 *
 * @package Pckg\Generic\Service\Generic
 */
class Action implements \JsonSerializable
{

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var ActionRecord
     */
    protected $action;

    /**
     * @param string $template
     *
     * @return View\Twig
     */
    public function toView(string $template)
    {
        return view($template, ['action' => $this]);
    }

    /**
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct(ActionRecord $action, Route $route = null, $resolved = [])
    {
        $this->args = [
            'settings' => $action->pivot->settings ?? [],
            'route'    => $route,
            'resolved' => $resolved,
        ];
        $this->action = $action;
    }

    public function getOrder()
    {
        return $this->action->pivot->order + ($this->action->pivot->morph_id == Routes::class ? 0 : 10000);
    }

    public function getClass()
    {
        return $this->action->class;
    }

    public function getMethod()
    {
        return $this->action->method;
    }

    public function getType()
    {
        return $this->action->pivot->type ?? 'action';
    }

    /**
     * @return mixed|null|Content
     */
    public function getContent()
    {
        return $this->action->pivot->content;
    }

    public function getFlat()
    {
        if (!$this->action) {
            return;
        }

        $defaults = $this->jsonSerialize();

        return array_merge([
                                'title'    => $this->action->title,
                                'morph'    => $this->action->pivot->morph,
                                'type'     => $this->getType(),
                                'slug'     => $this->action->slug,
                            ], $defaults);
    }

    public function getTree()
    {
        $tree = $this->getFlat();
        $tree['actions'] = [];

        foreach ($this->action->getChildren ?? [] as $action) {
            $genericAction = new Action($action, $this->args['route'], $this->args['resolved']);
            $tree['actions'][] = $genericAction->getTree();
        }

        return $tree;
    }

    public function hasChildren(...$index) {
        if (!$this->action) {
            return false;
        }

        $children = $this->action->getChildren;
        if (!$children) {
            return false;
        }

        if (!$index) {
            return true;
        }

        foreach ($index as $i) {
            if (isset($children[$i])) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function getSubHtml(...$index)
    {
        $html = [];
        foreach ($this->action->getChildren as $i => $action) {
            if ($index && !in_array($i, $index)) {
                continue;
            }

            $genericAction = new Action($action, $this->args['route'], $this->args['resolved']);

            $html[] = $genericAction->getHtml();
        }

        return implode($html);
    }

    public function getChild($index)
    {
        $action = $this->action->getChildren[$index] ?? null;
        if (!$action) {
            return null;
        }

        return new Action($action, $this->args['route'], $this->args['resolved']);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHtml($innerOnly = false)
    {
        return measure('Generic action ' . $this->getType() . ' #' . $this->action->pivot->id . ' ' . $this->getClass() . ' @ ' . $this->getMethod(),
            function() use ($innerOnly) {
                $type = $this->getType();

                if (in_array($type, ['wrapper', 'container', 'row', 'column'])) {
                    $content = '<pckg-' . $type
                        . ' :action-id="' . $this->action->pivot->id . '">';
                    $content .= '<template slot="body">';
                    $content .= $this->getBackgroundVideoHtml();
                    $content .= $this->getSubHtml();
                    $content .= '</template>';
                    $content .= '</pckg-' . $type . '>';
                    return $content;
                }

                $return = measure('Building pre-wrap',
                    function() {
                        return '<pckg-action :action-id="' . $this->action->pivot->id . '"><template slot="body">';
                    });
                $return .= $this->getBackgroundVideoHtml();

                if ($this->getClass() && $this->getMethod()) {
                    /**
                     * Get subhtml for multi-leveled actions.
                     */
                    $this->getSubHtml();

                    $args = array_merge(router()->get('data'), router()->getResolves());
                    $args = array_merge($args, ['action' => $this, 'content' => $this->getContent()]);
                    $args = array_merge($args, $this->args);

                    measure('Resolving',
                        function() use (&$args) {
                            if (isset($args['settings'])) {
                                $args['settings']->each(function(Setting $setting) use (&$args) {
                                    $setting->pivot->resolve($args);
                                });
                            }

                            foreach ($args['resolved'] as $key => $val) {
                                $args[$key] = $val;
                            }

                            /**
                             * Proper resolve by setting implementation, remove others.
                             */
                            $actionsMorphResolver = $this->action->pivot->settings->keyBy('slug')
                                                                                  ->getKey('pckg.generic.actionsMorph.resolver');
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

                    /**
                     * Get plugin output.
                     */
                    $pluginService = new Plugin();
                    $result = null;
                    try {
                        $result = measure('Making plugin ' . $this->getClass() . ' @ ' . $this->getMethod(),
                            function() use ($pluginService, $args) {
                                return $pluginService->make($this->getClass(), $this->getMethod(), $args, true, false);
                            });
                    } catch (Throwable $e) {
                        if (!prod()) {
                            throw new Exception(exception($e) . ':' . $this->getClass() . ' ' . $this->getMethod());
                        }
                    }

                    /**
                     * Array should be returned directly.
                     */
                    if (is_array($result)) {
                        return $result;
                    }

                    /**
                     * Allow custom template.
                     */
                    if ($result instanceof View\Twig) {
                        /**
                         * Awh, and check for allowed templates. :)
                         */
                        if ($this->action->pivot->template['template']) {
                            /**
                             * In template we store template, list template and item template designs.
                             */
                            $newFile = str_replace(':', '/View/', $this->action->pivot->template['template']);
                            message('Using action template ' . $newFile);
                            $result->setFile($newFile);
                        }

                        // $result->addData('serviceAction', $this);
                    }

                    /**
                     * Parse view to string in all cases.
                     */
                    $result = measure('Parsing to string',
                        function() use ($result) {
                            return (string)$result;
                        });

                    $this->getAction()->pivot->build = $result;
                    if ($innerOnly) {
                        return $result;
                    }

                    /**
                     * Return built output.
                     */
                    $return .= $result;
                }
                $return .= '</template></pckg-action>';

                return $return;
            });
    }

    public function getBackgroundVideoHtml()
    {
        $settings = $this->action->pivot->settings->keyBy('slug');
        $url = $settings->getKey('pckg.generic.pageStructure.bgVideo')->pivot->value ?? null;
        $source = $settings->getKey('pckg.generic.pageStructure.bgVideoSource')->pivot->value ?? null;

        if (!$url || !$source) {
            return;
        }

        $autoplay = $settings->getKey('pckg.generic.pageStructure.bgVideoAutoplay')->pivot->value ?? 'no';
        $display = $settings->getKey('pckg.generic.pageStructure.bgVideoDisplay')->pivot->value ?? 'background';
        $controls = $settings->getKey('pckg.generic.pageStructure.bgVideoControls')->pivot->value ?? 'yes';
        $loop = $settings->getKey('pckg.generic.pageStructure.bgVideoLoop')->pivot->value ?? 'yes';
        $mute = $settings->getKey('pckg.generic.pageStructure.bgVideoMute')->pivot->value ?? null;

        if ($source == 'youtube') {
            if ($display == 'background') {
                $youtubeUrl = 'https://www.youtube.com/embed/' . $url . '?controls=' . ($controls == 'yes' ? 1 : 0) . '&autoplay=' . ($autoplay == 'yes' ? 1 : 0) . '&loop=' . ($loop == 'yes' ? 1 : 0) . ($mute ? '&mute=1' : '') . '&modestbranding=1' . '&playsinline=1' . '&rel=0' . '&showinfo=0' . '&playlist=' . $url;

                return '<div class="video-background">
    <div class="video-foreground">
      <iframe src="' . $youtubeUrl . '" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>';
            } elseif ($display == 'popup') {
                /**
                 * We should add some trigger or link or something? :)
                 */
                $youtubeUrl = 'https://www.youtube.com/watch?v=' . $url;

                return '<a href="' . $youtubeUrl . '" class="popup-iframe"></a>';
            }
        }
    }

    public function getSetting($key = null, $default = null)
    {
        $settings = $this->action->pivot->settings->keyBy('slug');
        if (!$key) {
            return $settings;
        }
        $key = 'pckg.generic.pageStructure.' . $key;

        if ($settings->hasKey($key)) {
            $setting = $settings->getKey($key);
            $value = $setting->pivot->value;

            if ($setting->type == 'array') {
                return $value ? json_decode($value, true) : [];
            }

            return $value;
        }

        return $default;
    }

    /**
     * @return ActionRecord
     */
    public function getAction()
    {
        return $this->action;
    }

    public function jsonSerialize()
    {
        $template = $this->action->pivot->template;

        /**
         * Transform template to original template
         *  - Derive/Offers:offers/list-vSquare -> Derive/Offers:offers/list
         */
        list($before, $after) = explode(
            ':',
            str_replace(
                ['\\', '/Controller/'],
                ['/', ':'],
                $this->action->class
            ) . '/' . $this->action->method
        );
        $classed = $before . ':' . lcfirst($after);


        /**
         * Make sure that vue templates are set.
         */
        $listTemplate = null;
        $itemTemplate = null;
        $templates = config('pckg.generic.templates.' . $this->action->class . '.' . $this->action->method . '.' . $classed, null);
        $listTemplates = config('pckg.generic.templateEngine.list', []);
        $itemTemplates = config('pckg.generic.templateEngine.item', []);
        if (is_array($templates)) {
            $listTemplate = array_keys($templates['list'] ?? $listTemplates)[0] ?? null;
            if (!isset($template['list'])) {
                $template['list'] = $listTemplate;
            }
            $itemTemplate = array_keys($templates['item'] ?? $itemTemplates)[0] ?? null;
            if (!array_key_exists('item', $template) || !$template['item'] || !in_array($template['item'], array_keys($templates['item']))) {
                $template['item'] = $itemTemplate;
            }
        }

        return [
            'id'        => $this->action->pivot->id,
            'parent_id' => $this->action->pivot->parent_id,
            'class'     => $this->action->class,
            'classed'   => $classed,
            'method'    => $this->action->method,
            'template'  => $template,
            'settings'  => $this->action->pivot->settingsArray,
            'content'   => $this->getContent(),
            'build'     => $this->action->pivot->build,
            'order'     => $this->action->pivot->order,
        ];
    }

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

}