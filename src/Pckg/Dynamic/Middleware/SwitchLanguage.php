<?php namespace Pckg\Dynamic\Middleware;

class SwitchLanguage
{

    public function execute(callable $next)
    {
        if (request()->isPost() && post()->switch_language) {
            $_SESSION['pckg_dynamic_lang_id'] = post()->language_id;
            redirect();
        }

        return $next();
    }

}