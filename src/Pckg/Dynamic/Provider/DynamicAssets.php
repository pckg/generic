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
             * Datatables
             */
            //'js/datatables.js',
            //'css/datatables.css',
            /**
             * Sort
             */
            'css/actions.css',
            'js/actions.js',
            /**
             * Externals
             */
            //'https://use.fontawesome.com/90d4cc6ef0.js',
            //'https://cdn.datatables.net/t/bs/dt-1.10.11/datatables.min.css',
            //'https://cdn.datatables.net/t/bs/dt-1.10.11/datatables.min.js',
            //'https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.24/vue.min.js',
            //'https://cdn.jsdelivr.net/vue.resource/0.9.0/vue-resource.min.js',
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