<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;

class GenericAssets extends Provider
{

    public function assets()
    {
        return [
            'footer'    => [
                'vue/pckg-generic-app.js',
            ],
            'libraries' => [
                'vue/pckg-generic-app-top.js',
            ],
        ];
    }

}