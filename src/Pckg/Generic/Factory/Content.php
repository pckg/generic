<?php

namespace Pckg\Generic\Factory;

use Faker\Factory;
use Pckg\Generic\Record\Content as ContentRecord;

class Content
{

    const LENGTH_SHORT  = [1, 1];
    const LENGTH_MEDIUM = [3, 5];
    const LENGTH_LONG   = [3, 10];

    public static $files;

    public static function getFakeContent($settings = [])
    {
        $newSettings = $settings;
        $newSettings['levels'] = ($settings['levels'] ?? 1) - 1;
        $newSettings['length'] = $settings['length'] ?? static::LENGTH_MEDIUM;
        $faker = Factory::create();

        if (!static::$files) {
            static::$files = collect(scandir(path('uploads') . 'contents/'))->filter(function ($file) {
                return is_file(path('uploads') . 'contents/' . $file);
            });
        }

        return new ContentRecord([
                                     'id'          => rand(1, PHP_INT_MAX),
                                     'title'       => $faker->words(rand(3, 8), true),
                                     'subtitle'    => $faker->words(rand(5, 20), true),
                                     'description' => $faker->words(rand(10, 30), true),
                                     'content'     => '<p>' . implode('</p><p>', $faker->paragraphs(rand(...($newSettings['length'])))) .
                                         '</p>',
                                     'picture'     => static::$files->random(),
                                     'parent_id'   => null,
                                     'template'    => '',
                                     'contents'    => collect($newSettings['levels'] > 0 ? [
                                         static::getFakeContent($newSettings),
                                         static::getFakeContent($newSettings),
                                         static::getFakeContent($newSettings),
                                     ] : []),
                                 ]);
    }
}
