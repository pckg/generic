<?php

namespace Pckg\Generic\Middleware;

class UnderMaintenance
{

    public function execute(callable $next)
    {
        if (config('maintenance') && !dev()) {
            response()->respond(view('Pckg/Generic:maintenance')->autoparse());
        }

        return $next();
    }
}
