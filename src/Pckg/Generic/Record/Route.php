<?php

namespace Pckg\Generic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Service\Partial\AbstractPartial;

/**
 * Class Route
 *
 * @package Pckg\Generic\Record
 */
class Route extends Record
{

    /**
     * @var
     */
    protected $entity = Routes::class;

    protected $toArray = ['+settings'];

    public function deleteWidely()
    {
        /**
         * Delete actions morphs.
         */
        $this->actionsMorphs->each(function(ActionsMorph $actionsMorph) {
            $actionsMorph->deleteWidely();
        });

        $this->delete();
    }

    public function getLayoutName()
    {
        return $this->layout
            ? $this->layout->slug
            : null;
    }

    public function getRoute($prefix = true)
    {
        return ($prefix ? env()->getUrlPrefix() : '') . $this->route;
    }

    public function hasPermissionToView()
    {
        /**
         * @T00D00 - use permissions on route level!
         *         Currently user has permissions if there are contents defined.
         */
    }

    public function export()
    {
        $route = $this->data();
        $route['settings'] = $this->settings->map(function(Setting $setting) {
            $data = $setting->pivot->data();
            $data['slug'] = $setting->slug;

            return $data;
        })->toArray();

        return [
            'route'   => $route,
            'actions' => $this->actions
                ->map(function(Action $action) {
                    return $action->pivot->export();
                })
                ->tree('parent_id', 'id', 'actions')
                ->all(),
        ];
    }

    public function import($export)
    {
        foreach ($export['route']['settings'] ?? [] as $setting) {
            $setting['setting_id'] = Setting::getOrCreate(['slug' => $setting['slug']])->id;
            $setting['poly_id'] = $this->id;
            unset($setting['id']);
            SettingsMorph::create($setting);
        }

        foreach ($export['actions'] ?? [] as $action) {
            ActionsMorph::import($action, $this);
        }
    }

    public function addPartial($partial)
    {
        $partial = $this->preparePartial($partial);

        return $partial->addToRoute($this);
    }

    public function addHubShare($partial)
    {
        $partial = null;

        return $partial->addToRoute($this);
    }

    /**
     * @param $partial
     *
     * @return object|AbstractPartial
     */
    protected function preparePartial($partial)
    {
        return Reflect::create($partial);
    }

    public function forPageStructure()
    {
        $data = $this->toArray();

        $data['settings'] = $this->settings->keyBy('slug')->map(function(Setting $setting) {
                return $setting->pivot->value;
            });
        $data['resolvers'] = json_decode($data['resolvers'], true);
        $resolved = router()->getResolves();

        if (array_key_exists('route', $resolved)) {
            unset($resolved['route']);
        }
        $data['resolved'] = $resolved;

        return $data;
    }

    public function applySeoSettings()
    {
        $seoManager = seoManager();

        /**
         * Get seo setting and key them by last key.
         */
        $settings = $this->settings->filter(function(Setting $setting) {
            return strpos($setting->slug, 'pckg.generic.pageStructure.seo.') === 0;
        })->keyBy(function(Setting $setting) {
            return str_replace('pckg.generic.pageStructure.seo.', '', $setting->slug);
        })->map(function(Setting $setting) {
            return $setting->pivot->value;
        });

        $settings->each(function($value, $slug) use ($seoManager) {
            if ($value) {
                $seoManager->{'set' . ucfirst($slug)}($value);
            }
        });
    }

    public function cloneRoute($newDetails, &$errors = [])
    {
        /**
         * Check for existing url or slug.
         */
        $existing = (new Routes())->nonDeleted()
                                  ->joinTranslations()
                                  ->whereRaw('routes_i18n.route = ? OR routes.slug = ?', [$newDetails['route'], $newDetails['slug']])
                                  ->one();

        if ($existing) {
            $errors[] = 'Route with same slug or url already exists.';

            return false;
        }

        return (new Routes())->transaction(function() use ($newDetails) {
            $route = $this->saveAs($newDetails);

            /**
             * Simply, export and import. :)
             */
            $route->import($this->export());

            return $route;
        });
    }

}