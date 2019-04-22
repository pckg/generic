<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Framework\Request;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Entity\Actions;

/**
 * Class Action
 *
 * @package Pckg\Generic\Record
 */
class Action extends Record
{

    /**
     * @var
     */
    protected $entity = Actions::class;

    protected $toArray = ['pivot'];

    public function build($args = [])
    {
        return measure('Making plugin ' . $this->class . ' @ ' . $this->method, function() use ($args) {
            $pluginService = new Plugin();

            return $pluginService->make($this->class, $this->method, $args, Request::GET, false);
        });
    }

    public function checkDeprecation()
    {
        $deprecations = config('deprecation.actions', []);
        $deprecationsTemplates = config('deprecation.templates', []);
        $deprecationsMethods = config('deprecation.methods', []);

        /**
         * Method / class
         */
        if (isset($deprecationsMethods[$this->class . '@' . $this->method])) {
            list($c, $m) = explode('@', $deprecationsMethods[$this->class . '@' . $this->method]);
            $this->class = $c;
            $this->method = $m;
        }

        /**
         * We need to properly change template.
         */
        $template = $this->pivot->template;
        if (isset($template['template']) && !isset($template['item']) && isset($deprecationsTemplates[$template['template']])) {
            $deprecation = $deprecationsTemplates[$template['template']];
            $t = $deprecationsTemplates[$template['template']];
            $t2 = array_key_exists('template', $t) ? $t['template'] : $template['template'];

            message('Deprecating template ' . $template['template'] . ' to ' . json_encode($t));
            $this->pivot->template = json_encode($deprecationsTemplates[$template['template']]);
        }

        /**
         * And action.
         */
        if (isset($deprecations[$this->slug])) {
            message('Deprecating action ' . $this->slug . ' to ' . $deprecations[$this->slug] . ' ' . json_encode($template));
            $newAction = (new Actions())->where('slug', $deprecations[$this->slug])->one();
            if (!$newAction) {
                $this->class = config('pckg.generic.actions.' . $deprecations[$this->slug] . '.class');
                $this->method = config('pckg.generic.actions.' . $deprecations[$this->slug] . '.method');
                $this->slug = $deprecations[$this->slug];
                $this->pivot->action = $this;
                return $this;

            } else {
                $newAction->pivot = $this->pivot;
                $newAction->pivot->action_id = $newAction->id;
                $newAction->pivot->action = $newAction;
            }

            return $newAction;
        }

        return $this;
    }

}