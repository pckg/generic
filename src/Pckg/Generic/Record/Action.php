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
        $keyedBySlug = $this->pivot->settings->keyBy('slug');
        if ($this->pivot->type == 'container' && $keyedBySlug->hasKey('pckg.generic.pageStructure.container')
        ) {
            $typeSuffix = '-fluid';
        }

        if ($keyedBySlug->hasKey('pckg.generic.pageStructure.class')) {
            $typeSuffix .= ' ' . $keyedBySlug['pckg.generic.pageStructure.class']->pivot->value;
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
            'pckg.generic.pageStructure.bgColor'      => 'background-color',
            'pckg.generic.pageStructure.bgAttachment' => 'background-attachment',
            'pckg.generic.pageStructure.bgImage'      => 'background-image',
            'pckg.generic.pageStructure.margin'       => 'margin',
            'pckg.generic.pageStructure.padding'      => 'padding',
        ];

        $settings = $this->pivot->settings;
        $styles = [];
        foreach ($settings as $setting) {
            if (!array_key_exists($setting->slug, $mapper)) {
                continue;
            }

            if ($setting->slug == 'pckg.generic.pageStructure.bgImage') {
                $value = $mapper[$setting->slug] . ': url(' . cdn('/storage/uploads/' . config('app') . '/' .
                                                                  $setting->pivot->value) . ')';
            } else {
                $value = $mapper[$setting->slug] . ': ' . $setting->pivot->value;
            }
            $styles[] = $value;
        }

        return implode('; ', $styles);
    }

}