<?php namespace Pckg\Dynamic\Middleware;

class SetContentLanguage
{

    public function execute(callable $next)
    {
        if (!session()->pckg_dynamic_lang_id) {
            session()->pckg_dynamic_lang_id = config('pckg.locale.language');
        }

        return $next();
    }

}