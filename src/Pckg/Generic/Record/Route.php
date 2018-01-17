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
        return ($prefix ? env()->getUrlPrefix() : '') . $this->getValue('route');
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
        $partial->addToRoute($this);
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

}