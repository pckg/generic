<?php namespace Pckg\Dynamic\Provider;

use Pckg\Charts\Provider\Charts;
use Pckg\Framework\Provider;

class DynamicAssets extends Provider
{

    /**
     * @return array
     */
    public function assets()
    {
        return [
            /**
             * Sort
             */
            // 'css/actions.css',
            // 'js/actions.js',
            /**
             * Help popover
             */
            'js/dynamic.js',
            'less/dynamic.less',
        ];
    }

    public function providers()
    {
        return [
            //Charts::class,
        ];
    }

}