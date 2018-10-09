<?php namespace Pckg\Dynamic\Provider;

use Pckg\Charts\Provider\Charts;
use Pckg\Framework\Provider;

class DynamicAssets extends Provider
{

    public function registered()
    {
        vueManager()->addComponent(
            [
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-geo',
            ]
        );
    }

    /**
     * @return array
     */
    public function assets()
    {
        return [
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
        ];
    }

    public function providers()
    {
        return [
            Charts::class,
        ];
    }

}