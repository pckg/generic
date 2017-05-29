<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\Actions;

/**
 * Class Action
 *
 * @package Pckg\Generic\Record
 */
class Action extends Record
{

    /**
     * @var
     */
    protected $entity = Actions::class;

    protected $toArray = ['pivot'];

    public function getHtmlClassAttribute()
    {
        $typeSuffix = '';
        if ($this->pivot->type == 'container' &&
            $this->pivot->settings->keyBy('slug')->hasKey('pckg.generic.pageStructure.container')
        ) {
            $typeSuffix = '-fluid';
        }
        $mainClass = $this->pivot->type . $typeSuffix . ' ' . $this->pivot->type . '-' . $this->pivot->id;
        $mapper = [
            'pckg.generic.pageStructure.bgSize'     => 'bg-size',
            'pckg.generic.pageStructure.bgRepeat'   => 'bg-repeat',
            'pckg.generic.pageStructure.bgPosition' => 'bg-position',
        ];

        $settings = $this->pivot->settings;
        foreach ($settings as $setting) {
            if (!array_key_exists($setting->slug, $mapper)) {
                continue;
            }

            $mainClass .= ' ' . $mapper[$setting->slug] . '-' . $setting->pivot->value;
        }

        return $mainClass;
    }

    public function getHtmlStyleAttribute()
    {
        $mapper = [
            'pckg.generic.pageStructure.bgColor' => 'background-color',
            'pckg.generic.pageStructure.bgImage' => 'background-image',
            'pckg.generic.pageStructure.margin'  => 'margin',
            'pckg.generic.pageStructure.padding' => 'padding',
        ];

        $settings = $this->pivot->settings;
        $styles = [];
        foreach ($settings as $setting) {
            if (!array_key_exists($setting->slug, $mapper)) {
                continue;
            }

            $value = $mapper[$setting->slug] . ': ' . $setting->pivot->value;
            if ($setting->slug == 'pckg.generic.pageStructure.bgImage') {
                $value = 'url(/storage/uploads/' . config('app') . '/' . $value . ')';
            }
            $styles[] = $value;
        }

        return implode('; ', $styles);
    }

}