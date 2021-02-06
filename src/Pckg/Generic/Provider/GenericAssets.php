<?php

namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;

class GenericAssets extends Provider
{

    public function assets()
    {
        return [
            '@' . path('vendor') . 'pckg/helpers-less/clear.less',
            '@' . path('vendor') . 'pckg/helpers-less/vars.less',
            '@' . path('vendor') . 'pckg/helpers-less/animation.less',
            '@' . path('vendor') . 'pckg/helpers-less/common.less',
            '@' . path('vendor') . 'pckg/helpers-less/font.less',
            '@' . path('vendor') . 'pckg/helpers-less/list.less',
            '@' . path('vendor') . 'pckg/helpers-less/margin.less',
            '@' . path('vendor') . 'pckg/helpers-less/padding.less',
            path('vendor') . 'pckg/helpers-js/idify.jquery.js',
            path('vendor') . 'pckg/helpers-js/vha.jquery.js',
            path('vendor') . 'pckg/helpers-js/vhax.jquery.js',
            'main'      => [
                'less/generic.less',
            ],
            'footer'    => [
                // 100 => 'vue/pckg-generic-app.js',
            ],
            'libraries' => [
                // 'vue/pckg-generic-app-top.js',
            ],
        ];
    }
}
