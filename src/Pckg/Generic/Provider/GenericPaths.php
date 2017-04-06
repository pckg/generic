<?php namespace Pckg\Generic\Provider;

use Pckg\Framework\Provider;

class GenericPaths extends Provider
{

    public function paths()
    {
        $paths = $this->getViewPaths();
        $paths[] = str_replace('Generic', 'Maestro', $paths[0]);

        return $paths;
    }

}