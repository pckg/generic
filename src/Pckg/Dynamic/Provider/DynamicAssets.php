<?php namespace Pckg\Dynamic\Provider;

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
             * Magnific JS
             */
            'js/magnific.js',
            'css/magnific.css',
            /**
             * Sort
             */
            'css/actions.css',
            'js/actions.js',
            /**
             * Help popover
             */
            'js/bootstrap.js',
            'less/bootstrap.less',
            /**
             * Vue.js
             */
            'footer' => [
                'js/vue.js',
            ],
        ];
    }

}