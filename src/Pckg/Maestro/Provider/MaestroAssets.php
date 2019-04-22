<?php namespace Pckg\Maestro\Provider;

use Pckg\Framework\Provider;

class MaestroAssets extends Provider
{

    /**
     * @return array
     */
    public function assets()
    {
        return [
            '@' . path('vendor') .'pckg/generic/src/Pckg/Maestro/public/less/maestro_vars.less',
            'less/maestro.less',
        ];
    }

}