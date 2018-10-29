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
     * @param      $class
     * @param      $method
     * @param null $order
     */
    public function __construct(ActionRecord $action, Route $route = null, $resolved = [])
    {
        $this->args = [
            'content'  => $action->pivot->content,
            'settings' => $action->pivot->settings,
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
        return $this->action->pivot->type;
    }

    /**
     * @return mixed|null|Content
     */
    public function getContent()
    {
        return $this->args['content'] ?? null;
    }

    public function getTree()
    {
        if (!$this->action) {
            return;
        }

        $defaults = $this->jsonSerialize();

        $tree = array_merge([
            'title'    => $this->action->title,
            'morph'    => $this->action->pivot->morph,
            'type'     => $this->getType(),
            'actions'  => [],
            'slug'     => $this->action->slug,
        ], $defaults);

        foreach ($this->action->getChildren as $action) {
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

                $return = measure('Building pre-wrap',
                    function() {
                        return '<div class="' . $this->action->htmlClass . '" style="' . $this->action->htmlStyle . '" data-action-id="' . $this->action->pivot->id . '"' . ' id="' . $this->action->pivot->type . '-' . $this->action->pivot->id . '">';
                    });
                $return .= $this->getBackgroundVideoHtml();
                if (in_array($this->getType(), ['wrapper', 'container', 'row', 'column'])) {
                    $return .= $this->getSubHtml() . '</div>';

                    return $return;
                }

                if ($this->getClass() && $this->getMethod()) {
                    $args = array_merge(router()->get('data'), router()->getResolves());
                    $args = array_merge($args, ['action' => $this]);
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
                            $result->setFile(str_replace(':', '/View/', $this->action->pivot->template['template']));
                        }

                        $result->addData('serviceAction', $this);
                    }

                    /**
                     * Parse view to string in all cases.
                     */
                    $result = measure('Parsing to string',
                        function() use ($result) {
                            return (string)$result;
                        });

                    if ($innerOnly) {
                        return $result;
                    }

                    /**
                     * Return built output.
                     */
                    $return .= $result;
                }
                $return .= '</div>';

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
        list($before, $after) = explode(':',
                                        str_replace(['\\', '/Controller/'],
                                                    ['/', ':'],
                                                    $this->action->class) . '/' . $this->action->method);
        $classed = $before . ':' . lcfirst($after);


        /**
         * Make sure that vue templates are set.
         */
        $listTemplate = null;
        $itemTemplate = null;
        $templates = config('pckg.generic.templates.' . $this->action->class . '.' . $this->action->method . '.' . $classed, null);
        if (is_array($templates)) {
            $listTemplate = array_keys($templates['list'] ?? [])[0] ?? null;
            $itemTemplate = array_keys($templates['item'] ?? [])[0] ?? null;
            $template['list'] = $listTemplate;
            $template['item'] = $itemTemplate;
        }

        return [
            'id'           => $this->action->pivot->id,
            'class'        => $this->action->class,
            'classed'      => $classed,
            'method'       => $this->action->method,
            'template'     => $template,
            'settings'     => $this->getSetting()->keyBy(function(Setting $setting) {
                return str_replace('pckg.generic.pageStructure.', '', $setting->slug);
            })->map(function(Setting $setting) {
                return $setting->pivot->getFinalValueAttribute();
            }),
            'content'      => $this->getContent(),
        ];
    }

}