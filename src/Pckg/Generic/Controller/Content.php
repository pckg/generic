<?php

namespace Pckg\Generic\Controller;

use CommsCenter\Pagebuilder\Record\Content as ContentRecord;
use Pckg\Generic\Record\Setting;
use Pckg\Generic\Service\Generic\Action;

class Content
{
    public function getSimpleAction(Action $action)
    {
        return $action->toVue('pckg-generic-content-simple');
    }

    public function getTreeAction(Action $action)
    {
        return $action->toView('Pckg/Generic:content/tree');
    }

    /**
     * @param ContentRecord|null $content
     * @param Action             $action
     * @param                    $settings
     *
     * @return \Pckg\Framework\View\Twig
     * @deprecated
     */
    public function getTemplateAction(Action $action, $settings)
    {
        return view(
            $action->getAction()->settings ? $action->getAction()->settings->first(
                function (Setting $item) {
                    return $item->slug == 'pckg-generic-content-template';
                }
            )->pivot->value : 'Pckg/Generic:content/simple',
            [
                'action' => $action,
            ]
        );
    }
}
