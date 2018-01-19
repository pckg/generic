<?php namespace Pckg\Generic\Controller;

use Pckg\Database\Relation\MorphedBy;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Generic\Action\Content\Form\Simple;
use Pckg\Generic\Entity\Actions;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\ListItems;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Entity\Variables;
use Pckg\Generic\Form\ActionMorph;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Content;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Record\Setting;
use Pckg\Generic\Service\Generic;
use Pckg\Manager\Upload;
use Pckg\Stringify;

class PageStructure
{

    public function getInitialFetchAction()
    {
        $data = [
            'availableScopes'                => config('pckg.generic.scopes'),
            'availableContainers'            => config('pckg.generic.editor.containers'),
            'availableBackgroundSizes'       => config('pckg.generic.editor.bgSizes'),
            'availableBackgroundRepeats'     => config('pckg.generic.editor.bgRepeats'),
            'availableBackgroundAttachments' => config('pckg.generic.editor.bgAttachments'),
            'availableBackgroundPositions'   => config('pckg.generic.editor.bgPositions'),
            'templates'                      => config('pckg.generic.templates'),
        ];

        foreach (['partials', 'structures', 'pages'] as $type) {
            $data[$type] = collect(config('pckg.generic.' . $type))->map(function($partial) {
                return (new $partial)->forJson();
            });
        }

        return $data;
    }

    public function getPageStructureAction(ActionMorph $actionMorphForm, Simple $simpleContentForm)
    {
        $actionMorphForm->initFields();

        vueManager()->addView('Pckg/Generic:routes/_pageStructure', [
            'actionMorphForm'   => $actionMorphForm,
            'simpleContentForm' => $simpleContentForm,
            'backgrounds'       => (new ListItems())->where('list_id', 'actionsMorphs.backgrounds')->all(),
            'widths'            => (new ListItems())->where('list_id', 'actionsMorphs.widths')->all(),
        ]);

        return view('routes/pageStructure');
    }

    public function getRoutesAction()
    {
        return [
            'routes' => (new Routes())
                ->all()
                ->transform(['id', 'route', 'title', 'slug', 'layout_id']),
        ];
    }

    public function getVariablesAction()
    {
        return [
            'variables' => (new Variables())->all(),
        ];
    }

    public function getActionsAction()
    {
        return [
            'actions' => (new Actions())->all(),
        ];
    }

    public function getContentsAction(Contents $contents)
    {
        if (get('search')) {
            $dynamicService = resolve(Dynamic::class);
            $dynamicService->getFilterService()->filterByGet($contents);
        }

        return [
            'contents' => $contents->all(),
        ];
    }

    public function getRouteAction($route)
    {
        return [
            'route' => (new Routes())->where('id', $route)->one(),
        ];
    }

    public function getActionsMorphsForRouteAction(Route $route)
    {
        return [
            'actionsMorphs' => $route->actions(function(MorphedBy $actions) {
                $actions->getMiddleEntity()->withAllPermissions();
                $actions->getMiddleEntity()->withContent();
            })->sortBy(function(Action $action) {
                return $action->pivot->order;
            })
                                     ->map(function(Action $action) {
                                         $array = $action->toArray();
                                         $array['pivot']['permissions'] = $action->pivot->allPermissions->map('user_group_id');
                                         $array['pivot']['content'] = $action->pivot->content;

                                         return $array;
                                     }),
        ];
    }

    public function getRouteExportAction($route)
    {
        $route = (new Routes())->where('id', $route)->one();

        return [
            'export' => $route->export(),
        ];
    }

    public function postRouteImportAction($route)
    {
        $route = (new Routes())->where('id', $route)->one();

        $route->import(json_decode(post('export'), true));

        return [
            'ok',
        ];
    }

    public function getRouteActionsAction($route)
    {
        $route = (new Routes())->where('id', $route)->one();

        return [
            'routeActions' => $route->actions(function(MorphedBy $actions) {
                $actions->getMiddleEntity()->withAllPermissions();
                $actions->getMiddleEntity()->withContent();
            })
                                    ->sortBy(function(Action $action) {
                                        return $action->pivot->order;
                                    })
                                    ->map(function(Action $action) {
                                        $array = $action->toArray();
                                        $array['pivot']['permissions'] = $action->pivot->allPermissions->map('user_group_id');
                                        $array['pivot']['content'] = $action->pivot->content;

                                        return $array;
                                    }),
        ];
    }

    public function getLayoutActionsAction($layout)
    {
        $layout = (new Layouts())->where('id', $layout)->one();

        return [
            'layoutActions' => $layout->actions,
        ];
    }

    public function postActionsMorphPermissionsAction($actionsMorph)
    {
        $actionsMorph = (new ActionsMorphs())->where('id', $actionsMorph)->one();

        /**
         * Delete current permissions.
         */
        (new ActionsMorphs())->usePermissionableTable()->where('id', $actionsMorph->id)->delete();

        /**
         * Add new permissions.
         */
        foreach (post('read') as $userGroup) {
            $actionsMorph->grantPermissionTo('read', $userGroup);
        }

        return response()->respondWithAjaxSuccess();
    }

    public function deleteActionsMorphAction(ActionsMorph $actionsMorph)
    {
        $actionsMorph->deleteWidely();

        return response()->respondWithAjaxSuccess();
    }

    public function deleteRouteAction(Route $route)
    {
        $route->deleteWidely();

        return response()->respondWithAjaxSuccess();
    }

    public function postToggleActionsMorphLockAction(ActionsMorph $actionsMorph)
    {
        if ($actionsMorph->morph_id == Routes::class) {
            $actionsMorph->lockToLayout();
        } else {
            $route = Route::gets(['id' => post('route')]);
            $actionsMorph->lockToRoute($route);
        }

        return [
            'actionsMorph' => $actionsMorph,
        ];
    }

    public function postAddActionsMorphAction()
    {
        /**
         * Collect data.
         */
        $data = post(['action_id', 'poly_id' => 'route_id', 'variable_id', 'content_id', 'parent_id', 'type']);
        $data['morph_id'] = Routes::class;

        /**
         * Container, row and column actions.
         */
        if ($data['type'] == 'wrapper') {
            // pckg-generic-pageStructure-wrapper
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-wrapper',
                                                         'class'  => 'Pckg\Generic\Controller\PageStructure',
                                                         'method' => 'wrapper',
                                                     ])->id;
        } elseif ($data['type'] == 'container') {
            // pckg-generic-pageStructure-container
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-container',
                                                         'class'  => 'Pckg\Generic\Controller\PageStructure',
                                                         'method' => 'container',
                                                     ])->id;
        } elseif ($data['type'] == 'row') {
            // pckg-generic-pageStructure-row
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-row',
                                                         'class'  => 'Pckg\Generic\Controller\PageStructure',
                                                         'method' => 'row',
                                                     ])->id;
        } elseif ($data['type'] == 'column') {
            // pckg-generic-pageStructure-column
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-column',
                                                         'class'  => 'Pckg\Generic\Controller\PageStructure',
                                                         'method' => 'column',
                                                     ])->id;
        }

        /**
         * Create record.
         */
        $actionsMorph = ActionsMorph::create($data);

        /**
         * Fetch action.
         */
        $action = (new Actions())->where('id', $data['action_id'])->one();
        $action->pivot = $actionsMorph;

        return response()->respondWithSuccess([
                                                  'action' => $action,
                                              ]);
    }

    public function postActionsMorphAction(ActionsMorph $actionsMorph)
    {
        /**
         * Collect posted data.
         */
        $data = [];
        foreach (['order', 'container', 'background', 'template', 'width', 'content_id'] as $key) {
            if (post()->has($key)) {
                $data[$key] = post($key, null);
            }
        }

        /**
         * Save only if something was received.
         */
        if ($data) {
            $actionsMorph->setAndSave($data);
        }

        return response()->respondWithSuccess();
    }

    public function postOrdersActionsMorphAction()
    {
        $orders = post('orders', []);
        $actionsMorphs = (new ActionsMorphs())->where('id', array_keys($orders))->all();
        $root = $actionsMorphs->first(function(ActionsMorph $actionsMorph) {
            return $actionsMorph->variable_id;
        });
        $routeId = $root ? $root->poly_id : null;
        $actionsMorphs->each(function(ActionsMorph $actionsMorph) use ($orders, $routeId) {
            $update = [
                'order'     => $orders[$actionsMorph->id]['order'],
                'parent_id' => $orders[$actionsMorph->id]['parent'],
            ];
            if (!$update['parent_id'] && !$actionsMorph->variable_id) {
                $update['variable_id'] = 1;
                if (!$actionsMorph->poly_id) {
                    $update['poly_id'] = $routeId;
                }
            }
            $actionsMorph->setAndSave($update);
        });

        return response()->respondWithSuccess();
    }

    protected function getDefaultSettings()
    {
        /**
         * Add defaults.
         *
         * @T00D00
         * This should be refactored to plugins. ;-)
         */
        return [
            'class'           => '',
            'style'           => '',
            'width'           => [], // column
            'offset'          => [], // column
            'bgColor'         => '',
            'bgImage'         => '',
            'bgSize'          => '',
            'bgAttachment'    => '',
            'bgRepeat'        => '',
            'bgPosition'      => '',
            'bgVideo'         => '',
            'bgVideoSource'   => '',
            'bgVideoDisplay'  => '',
            'bgVideoAutoplay' => '',
            'bgVideoControls' => '',
            'bgVideoLoop'     => '',
        ];
    }

    protected function getPluginSettings()
    {
        /**
         * Add defaults.
         *
         * @T00D00
         * This should be refactored to plugins. ;-)
         */
        return [
            'sourceOffers'    => [],
            'sourcePackets'   => [],
            'sourceGalleries' => [],
        ];
    }

    public function getActionsMorphContentAction(ActionsMorph $actionsMorph)
    {
        return response()->respondWithSuccess([
                                                  'content' => $actionsMorph->content,
                                              ]);
    }

    public function postDuplicateActionsMorphContentAction(ActionsMorph $actionsMorph)
    {
        $content = $actionsMorph->content->saveAs();

        $actionsMorph->setAndSave(['content_id' => $content->id]);

        return response()->respondWithSuccess([
                                                  'content' => $content,
                                              ]);
    }

    public function postCreateActionsMorphContentAction(ActionsMorph $actionsMorph)
    {
        $content = Content::create();

        $actionsMorph->setAndSave(['content_id' => $content->id]);

        return response()->respondWithSuccess([
                                                  'content' => $content,
                                              ]);
    }

    public function getActionsMorphSettingsAction(ActionsMorph $actionsMorph)
    {
        $settings = $actionsMorph->settings
            ->map(function(
                Setting $setting
            ) {
                return [
                    'slug'  => str_replace('pckg.generic.pageStructure.', '',
                                           $setting->slug),
                    'value' => $setting->type == 'array'
                        ? (json_decode($setting->pivot->value, true) ??
                           [])
                        : $setting->pivot->value,
                ];
            })->keyBy('slug')->map('value');

        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        $pluginDefaults = $this->getPluginSettings();
        foreach ($pluginDefaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        $allClasses = (new Stringify($settings->getKey('class')))->explodeToCollection(' ')
                                                                 ->unique()
                                                                 ->removeEmpty()
                                                                 ->all();
        $scopeClasses = [];
        $otherClasses = [];
        $widthClasses = [];
        $offsetClasses = [];
        foreach ($allClasses as $class) {
            $found = false;
            foreach (config('pckg.generic.scopes') as $title => $scopes) {
                if (in_array($title, ['Padding', 'Margin'])) {
                    foreach ($scopes as $scps) {
                        if (array_key_exists($class, $scps)) {
                            $scopeClasses[] = $class;
                            $found = true;
                            break 2;
                        }
                    }
                } else {
                    if (array_key_exists($class, $scopes)) {
                        $scopeClasses[] = $class;
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                if (strpos($class, 'col-') === 0 && !strpos($class, '-pull-') && !strpos($class, '-push-')) {
                    if (strpos($class, '-offset-')) {
                        $offsetClasses[] = $class;
                    } else {
                        $widthClasses[] = $class;
                    }
                } else {
                    $otherClasses[] = $class;
                }
            }
        }

        $settings->push($scopeClasses, 'scopes');
        $settings->push($offsetClasses, 'offset');
        $settings->push($widthClasses, 'width');
        $settings->push(implode(' ', $otherClasses), 'class');

        /**
         * Add path before image.
         */
        $settings->push(media($settings->getKey('bgImage'), null, true, path('app_uploads')) ?? '', 'bgImage');

        return response()->respondWithSuccess([
                                                  'settings' => $settings,
                                              ]);
    }

    public function postActionsMorphSettingsAction(ActionsMorph $actionsMorph)
    {
        /**
         * Add defaults.
         *
         * @T00D00
         * This should be refactored to plugins. ;-)
         */
        $values = array_merge($this->getDefaultSettings(), post('settings'));
        $values = only($values, array_keys($this->getDefaultSettings()));
        unset($values['bgImage']);

        /**
         * Add scopes.
         */
        $values['class'] .= ' ' . implode(' ', post('settings.scopes', []))
                            . ' ' . implode(' ', post('settings.width', []))
                            . ' ' . implode(' ', post('settings.offset', []));
        $values['class'] = (new Stringify($values['class']))->explodeToCollection(' ')
                                                            ->unique()
                                                            ->removeEmpty()
                                                            ->implode(' ');
        collect($values)->removeKeys(['scopes', 'width', 'offset'])->each(function($value, $key) use ($actionsMorph) {
            $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, $value);
        });

        /**
         * Other settings, available for plugin.
         *
         * @T00D00
         */
        $values = only(post('settings'), ['dataScope', 'viewStyle']);
        collect($values)->each(function($value, $key) use ($actionsMorph) {
            $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, $value);
        });

        $values = only(post('settings'), ['sourceOffers', 'sourceGalleries', 'sourcePackets']);
        collect($values)->each(function($value, $key) use ($actionsMorph) {
            $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, json_encode($value), 'array');
        });

        /**
         * Set template.
         */
        $actionsMorph->setAndSave(['template' => post('template')]);

        return response()->respondWithSuccess();
    }

    public function deleteActionsMorphBackgroundImageAction(ActionsMorph $actionsMorph)
    {
        $actionsMorph->saveSetting('pckg.generic.pageStructure.bgImage', null);

        return response()->respondWithSuccess([
                                                  'success' => true,
                                              ]);
    }

    public function postActionsMorphBackgroundImageAction(ActionsMorph $actionsMorph)
    {
        $upload = new Upload('file');
        $success = $upload->validateUpload();

        if ($success !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $dir = path('app_uploads');
        $upload->save($dir);
        $filename = $upload->getUploadedFilename();

        $actionsMorph->saveSetting('pckg.generic.pageStructure.bgImage', $filename);

        return response()->respondWithSuccess([
                                                  'success' => true,
                                                  'url'     => img($filename, null, true, $dir),
                                              ]);
    }

    public function postContentAction(Content $content)
    {
        $content->setAndSave(['content' => post('content.content', null)]);

        return response()->respondWithSuccess(['content' => $content]);
    }

    public function postActionsMorphAddPartialAction(ActionsMorph $actionsMorph)
    {
        $partial = $actionsMorph->addPartial(post('partial', null));

        return response()->respondWithSuccess(['actionsMorph' => $partial]);
    }

    public function postActionsMorphAddRoutePartialAction(Route $route)
    {
        $route->addPartial(post('partial', null));

        return response()->respondWithSuccess();
    }

    public function getRouteTreeAction(Route $route, Generic $genericService)
    {
        $genericService->readRoute($route);

        return [
            'tree' => $genericService->getRouteTree(),
        ];
    }

}