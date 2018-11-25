<?php namespace Pckg\Generic\Controller;

use Pckg\Generic\Record\Content as ContentRecord;
use Pckg\Generic\Record\Setting;
use Pckg\Generic\Service\Generic\Action;

class Content
{

    public function getSimpleAction(Action $action)
    {
        /**
         * Get content, set it to proper view.
         */
        return view(
            'Pckg/Generic:content/simple',
            [
                'action'  => $action,
            ]
        );
    }

    public function getListedAction(ContentRecord $content = null)
    {
        /**
         * Get content, set it to proper view, also set subcontents.
         */
        return view(
            'content\simple',
            [
                'content' => $content,
            ]
        );
    }

    public function getTreeAction(ContentRecord $content = null)
    {
        /**
         * Get content, set it to proper view, also set it as tree.
         */
        return view(
            'content\tree',
            [
                'content' => $content,
            ]
        );
    }

    /**
     * @param ContentRecord|null $content
     * @param Action             $action
     * @param                    $settings
     *
     * @return \Pckg\Framework\View\Twig
     * @deprecated
     */
    public function getTemplateAction(ContentRecord $content = null, Action $action, $settings)
    {
        return view(
            $action->getAction()->settings ? $action->getAction()->settings->first(
                function(Setting $item) {
                    return $item->slug == 'pckg-generic-content-template';
                }
            )->pivot->value : 'Pckg/Generic:content/simple',
            [
                'action' => $action,
            ]
        );
    }

}