<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\Routes;

/**
 * Class Route
 *
 * @package Pckg\Generic\Record
 */
class Route extends Record
{

    /**
     * @var
     */
    protected $entity = Routes::class;

    public function getLayoutName()
    {
        return $this->layout
            ? $this->layout->slug
            : null;
    }

    public function getRoute($prefix = true)
    {
        return ($prefix ? env()->getUrlPrefix() : '') . $this->getValue('route');
    }

    public function hasPermissionToView()
    {
        /**
         * @T00D00 - use permissions on route level!
         *         Currently user has permissions if there are contents defined.
         */
    }

    public function export()
    {
        return $this->actions
            ->map(function(Action $action) {
                return $action->pivot->export();
            })
            ->tree('parent_id', 'id', 'actions');
    }

    public function import($export)
    {
        foreach ($export as $action) {
            ActionsMorph::import($action);
        }
    }

}