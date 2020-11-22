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
use Pckg\Generic\Service\Generic;
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
     * @param $key
     * @param $value
     * @return mixed
     */
    public function pushMetadata($key, $value)
    {
        return resolve(Generic::class)->pushMetadata($this->getAction()->pivot->id, $key, $value);
    }

    /**
     * @param string $template
     *
     * @return View\Twig
     */
    public function toView(string $template, $data = [])
    {
        return view($template, array_merge(['action' => $this], $data));
    }

    /**
     * @param string $component
     * @param array  $props
     *
     * @return string
     */
    public function toVue(string $component, $props = [])
    {
        /**
         * @var $generic Generic
         */
        $generic = resolve(Generic::class);
        $mergedProps = [];
        $action = $this->getAction();
        foreach ($props as $prop => $value) {
            if (is_numeric($value) || (is_string($prop) && substr($prop, 0, 1) === ':')) {
                $mergedProps[] = ' ' . $prop . '="' . $value . '"';
            } else {
                $mergedProps[] = ' :' . $prop . '="' . $generic->pushMetadata($action->pivot->id, $prop, $value) . '"';
            }
        }

        return '<' . $component .
            ($action->pivot ? ' :action-id="' . $action->pivot->id . '"' : '')
            . implode(' ', $mergedProps)
            . '></' . $component . '>';
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

        return $this->jsonSerialize();
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

                    $result = $this->build();

                    /**
                     * Return built output.
                     */
                    $return .= $result;
                }
                $return .= '</template></pckg-action>';

                return $return;
            });
    }

    public function buildAndJsonSerialize()
    {
        $this->build();

        return $this->jsonSerialize();
    }

    public function build($args = [])
    {
        $args = array_merge($args, ['action' => $this]);

        $this->getAction()->pivot->resolveSettings($args);

        $build = $this->getAction()->pivot->buildHtml($args);

        $this->getAction()->pivot->build = $build;
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
                $youtubeUrl = 'https://www.youtube-nocookie.com/embed/' . $url . '?controls=' . ($controls == 'yes' ? 1 : 0) . '&autoplay=' . ($autoplay == 'yes' ? 1 : 0) . '&loop=' . ($loop == 'yes' ? 1 : 0) . ($mute ? '&mute=1' : '') . '&modestbranding=1' . '&playsinline=1' . '&rel=0' . '&showinfo=0' . '&playlist=' . $url;

                return '<div class="video-background">
    <div class="video-foreground">
      <iframe src="' . $youtubeUrl . '" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>';
            } elseif ($display == 'popup') {
                /**
                 * We should add some trigger or link or something? :)
                 */
                $youtubeUrl = 'https://www.youtube-nocookie.com/watch?v=' . $url;

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
        return $this->getAction()->pivot->jsonSerialize();
    }

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

}