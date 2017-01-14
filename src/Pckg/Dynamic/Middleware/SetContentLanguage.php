<?php namespace Pckg\Dynamic\Middleware;

class SetContentLanguage
{

    public function execute(callable $next)
    {
        if (!session()->pckg_dynamic_lang_id) {
            session()->pckg_dynamic_lang_id = 'en';
        }

        return $next();
    }

}