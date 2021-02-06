<?php

namespace Pckg\Maestro\Provider;

use Pckg\Framework\Provider;

class MaestroAssets extends Provider
{

    /**
     * @return array
     */
    public function assets()
    {
        return [
            'less/maestro.less',
        ];
    }
}
