<?php namespace Pckg\Dynamic\Middleware;

use Pckg\Dynamic\Provider\DynamicAssets;
use Pckg\Manager\Asset;

class RegisterDynamicAssets
{

    protected $provider;

    public function __construct(DynamicAssets $provider)
    {
        $this->provider = $provider;
    }

    public function handle($view)
    {
        if (strpos($view, 'generic')) {
            $this->provider->register();
        }

        return $this;
    }

}