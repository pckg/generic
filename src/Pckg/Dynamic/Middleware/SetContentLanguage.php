<?php namespace Pckg\Dynamic\Middleware;

class SetContentLanguage
{

    public function execute(callable $next)
    {
        if (!isset($_SESSION['pckg_dynamic_lang_id'])) {
            $_SESSION['pckg_dynamic_lang_id'] = localeManager()->getDefaultFrontendLanguage()->slug;
        }

        return $next();
    }

}