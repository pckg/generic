<?php namespace Pckg\Generic\Controller;

use Pckg\Generic\Record\Content as ContentRecord;

class Content
{

    public function getSimpleAction(ContentRecord $content = null)
    {
        /**
         * Get content, set it to proper view.
         */
        return view('content\simple', [
            'content' => $content,
        ]);
    }

    public function getListedAction(ContentRecord $content = null)
    {
        /**
         * Get content, set it to proper view, also set subcontents.
         */
        return view('content\simple', [
            'content' => $content,
        ]);
    }

    public function getTreeAction(ContentRecord $content = null)
    {
        /**
         * Get content, set it to proper view, also set it as tree.
         */
        return view('content\tree', [
            'content' => $content,
        ]);
    }

}