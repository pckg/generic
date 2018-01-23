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
class Action
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
    public function __construct(ActionRecord $action, Route $route, $resolved = [])
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

        $tree = [
            'id'       => $this->action->pivot->id,
            'title'    => $this->action->title,
            'morph'    => $this->action->pivot->morph,
            'type'     => $this->getType(),
            'class'    => $this->getClass(),
            'method'   => $this->getMethod(),
            'actions'  => [],
            'slug'     => $this->action->slug,
            'template' => $this->action->pivot->template,
        ];

        foreach ($this->action->getChildren as $action) {
            $genericAction = new Action($action, $this->args['route'], $this->args['resolved']);
            $tree['actions'][] = $genericAction->getTree();
        }

        return $tree;
    }

    public function getSubHtml()
    {
        $html = [];
        foreach ($this->action->getChildren as $action) {
            $genericAction = new Action($action, $this->args['route'], $this->args['resolved']);

            $html[] = $genericAction->getHtml();
        }

        return implode($html);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHtml()
    {
        return measure('Generic action ' . $this->getType() . ' #' . $this->action->pivot->id, function() {

            $return = '<div class="' . $this->action->htmlClass . '" style="' . $this->action->htmlStyle .
                      '" data-action-id="' . $this->action->pivot->id . '"'
                      . ' id="' . $this->action->pivot->type . '-' . $this->action->pivot->id . '">';
            $return .= $this->getBackgroundVideoHtml();
            if (in_array($this->getType(), ['wrapper', 'container', 'row', 'column'])) {
                $return .= $this->getSubHtml() . '</div>';

                return $return;
            }

            if ($this->getClass() && $this->getMethod()) {
                $data = array_merge(['action' => $this], router()->get('data'));
                $data = array_merge(['action' => $this], router()->getResolves());
                $args = array_merge($this->args, $data);

                if (isset($args['settings'])) {
                    /**
                     * We need to resolve dependencies. ;-)
                     */
                    $args['settings']->each(
                        function(Setting $setting) use (&$args) {
                            $setting->pivot->resolve($args);
                        }
                    );
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

                /**
                 * Get plugin output.
                 */
                $pluginService = new Plugin();
                $result = null;
                try {
                    $result = $pluginService->make($this->getClass(), $this->getMethod(), $args, true, false);
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
                if ($result instanceof View\Twig && $this->action->pivot->template) {
                    $result->setFile(str_replace(':', '/View/', $this->action->pivot->template));
                }

                /**
                 * Parse view to string in all cases.
                 */
                startMeasure('Parsing to string');
                $result = (string)$result;
                stopMeasure('Parsing to string');

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

        if ($source == 'youtube') {
            if ($display == 'background') {
                $youtubeUrl = 'https://www.youtube.com/embed/'
                              . $url . '?controls='
                              . ($controls == 'yes' ? 1 : 0)
                              . '&autoplay='
                              . ($autoplay == 'yes' ? 1 : 0) . '&loop='
                              . ($loop == 'yes' ? 1 : 0)
                              . '&modestbranding=1'
                              . '&playsinline=1'
                              . '&rel=0'
                              . '&showinfo=0'
                              . '&playlist=' . $url;

                return '<div class="video-background">
    <div class="video-foreground">
      <iframe src="' . $youtubeUrl . '" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>';
            } else if ($display == 'popup') {
                /**
                 * We should add some trigger or link or something? :)
                 */
                $youtubeUrl = 'https://www.youtube.com/watch?v=' . $url;

                return '<a href="' . $youtubeUrl . '" class="popup-iframe"></a>';
            }
        }
    }

    public function getSetting($key = null)
    {
        if ($key == 'content') {
            return true;
        }

        if ($key == 'heading') {
            return 'h2';
        }

        if ($key == 'contentWidth') {
            return 'col-xs-12';
        }

        $key = 'pckg.generic.pageStructure.' . $key;
        $settings = $this->action->pivot->settings->keyBy('slug');

        if ($settings->hasKey($key)) {
            $setting = $settings->getKey($key);
            $value = $setting->pivot->value;

            if ($setting->type == 'array') {
                return json_decode($value, true);
            }

            return $value;
        }
    }

}