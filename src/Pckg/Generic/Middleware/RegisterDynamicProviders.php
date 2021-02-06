<?php

namespace Pckg\Generic\Middleware;

use Pckg\Framework\Provider;

class RegisterDynamicProviders extends Provider
{

    public function handle()
    {
        $this->register();
    }

    public function execute(callable $next)
    {
        $this->handle();

        return $next();
    }

    public function providers()
    {
        $providers = $this->getDynamicProviders();

        return $providers;
    }

    protected function getDynamicProviders()
    {
        return collect(config('pckg.generic.modules'))->filter(function ($module) {
            return $module['active'] && ($module['provider'] ?? null);
        })->map('provider')->rekey()->all();
    }
}
