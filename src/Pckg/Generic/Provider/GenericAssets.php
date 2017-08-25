<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;

class GenericAssets extends Provider
{

    public function assets()
    {
        return [
            '@' . path('vendor') . 'pckg/helpers-less/vars.less',
            '@' . path('vendor') . 'pckg/helpers-less/animation.less',
            '@' . path('vendor') . 'pckg/helpers-less/clear.less',
            '@' . path('vendor') . 'pckg/helpers-less/common.less',
            '@' . path('vendor') . 'pckg/helpers-less/font.less',
            '@' . path('vendor') . 'pckg/helpers-less/list.less',
            '@' . path('vendor') . 'pckg/helpers-less/margin.less',
            '@' . path('vendor') . 'pckg/helpers-less/objects.less',
            '@' . path('vendor') . 'pckg/helpers-less/padding.less',
            'main'      => [
                'less/generic.less',
            ],
            'footer'    => [
                'vue/pckg-generic-app.js',
            ],
            'libraries' => [
                'vue/pckg-generic-app-top.js',
            ],
        ];
    }

}