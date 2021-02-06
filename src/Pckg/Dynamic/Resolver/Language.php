<?php

namespace Pckg\Dynamic\Resolver;

use Pckg\Framework\Provider\RouteResolver;

class Language implements RouteResolver
{

    public function resolve($value)
    {
        $language = localeManager()->getLanguages()
                                   ->first(function (\Pckg\Locale\Record\Language $language) use ($value) {

                                       return $language->slug == $value;
                                   });
        if (!$language) {
            throw new \Exception('Language ' . $value . ' cannot be resolved');
        }

        return $language;
    }

    public function parametrize($record)
    {
        return $record->id;
    }
}
