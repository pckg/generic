<?php namespace Pckg\Generic\Factory;

use Faker\Factory;
use Pckg\Generic\Record\Content as ContentRecord;

class Content
{

    public static function getFakeContent($settings = [])
    {
        $newSettings = $settings;
        $newSettings['levels'] = ($settings['levels'] ?? 1) - 1;
        $faker = Factory::create();

        return new ContentRecord([
                                     'id'          => 1,
                                     'title'       => $faker->words(rand(3, 8), true),
                                     'subtitle'    => $faker->words(rand(5, 20), true),
                                     'description' => $faker->words(rand(10, 30), true),
                                     'content'     => '<p>' . implode('</p><p>', $faker->paragraphs(rand(3, 10))) . '</p>',
                                     'picture'     => '473246.jpg',
                                     'parent_id'   => null,
                                     'template'    => '',
                                     'contents'    => collect($newSettings['levels'] > 0 ? [
                                         static::getFakeContent(),
                                         static::getFakeContent(),
                                         static::getFakeContent(),
                                     ] : []),
                                 ]);
    }

}