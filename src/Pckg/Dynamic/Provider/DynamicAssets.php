<?php namespace Pckg\Dynamic\Provider;

use Pckg\Charts\Provider\Charts;
use Pckg\Framework\Provider;

class DynamicAssets extends Provider
{

    public function registered()
    {
        vueManager()->addComponent(
            [
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-dynamic-paginator',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-alert',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-modal',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-datetime',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-order',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-boolean',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-editor',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-dropzone',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-select',
            ]
        );

        assetManager()->addAssets(
            [
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-dynamic-paginator.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-maestro-table.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-bootstrap-alert.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-bootstrap-modal.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-datetime.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-order.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-boolean.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-editor.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-htmlbuilder-dropzone.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-htmlbuilder-select.js',
            ],
            'vue'
        );
    }

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

    public function providers()
    {
        return [
            Charts::class,
        ];
    }

}