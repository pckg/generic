<?php namespace Pckg\Generic\Controller;

use Pckg\Database\Relation\MorphedBy;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Generic\Entity\Actions;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Entity\Variables;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Content;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Record\Setting;
use Pckg\Generic\Record\SettingsMorph;
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

        foreach (['structures', 'pages', 'footers'] as $type) {
            $data[$type] = collect(config('pckg.generic.' . $type))->map(function($partial) {

                return (new $partial)->forJson();
            });
        }

        foreach (['partials'] as $type) {
            $data[$type] = collect(config('pckg.generic.' . $type))->map(function($partials) {

                return collect($partials)->map(
                    function($partial) {
                        return (new $partial)->forJson();
                    }
                );
            });
        }

        $data['routes'] = (new Routes())->joinTranslations()->nonDeleted()->all();

        return $data;
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
        $actionsMorph->grantPermissionTo('read', post('read'));

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
                                                         'class'  => PageStructure::class,
                                                         'method' => 'wrapper',
                                                     ])->id;
        } elseif ($data['type'] == 'container') {
            // pckg-generic-pageStructure-container
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-container',
                                                         'class'  => PageStructure::class,
                                                         'method' => 'container',
                                                     ])->id;
        } elseif ($data['type'] == 'row') {
            // pckg-generic-pageStructure-row
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-row',
                                                         'class'  => PageStructure::class,
                                                         'method' => 'row',
                                                     ])->id;
        } elseif ($data['type'] == 'column') {
            // pckg-generic-pageStructure-column
            $data['action_id'] = Action::getOrCreate([
                                                         'slug'   => 'pckg-generic-pageStructure-column',
                                                         'class'  => PageStructure::class,
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

    public function getActionsMorphContentAction(ActionsMorph $actionsMorph)
    {
        $content = $actionsMorph->content;
        if ($content) {
            $content->withContents();
        }

        return response()->respondWithSuccess([
                                                  'content' => $content,
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
        $settings = $actionsMorph->getSettingsArray();

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
        $values = array_merge($actionsMorph->getDefaultSettings(), post('settings'));
        $values = only($values, array_keys($actionsMorph->getDefaultSettings()));
        unset($values['bgImage']);

        /**
         * Add scopes.
         */
        $values['class'] .= ' ' . implode(' ', post('settings.scopes', []))
                            . ' ' . implode(' ', post('settings.width', []))
                            . ' ' . implode(' ', post('settings.offset', []))
                            . ' ' . post('settings.container', '');
        $values['class'] = (new Stringify($values['class']))->explodeToCollection(' ')
                                                            ->unique()
                                                            ->removeEmpty()
                                                            ->implode(' ');

        $actions = config('pckg.generic.actions', []);
        $separate = [
            'scopes',
            'width',
            'offset',
            'container',
            'wrapperLockHide',
            'wrapperLockShow',
            'wrapperLockSystem',
        ];
        $separateTypes = [];

        foreach ($actions as $actionKey => $actionSettings) {
            foreach ($actionSettings['settings'] ?? [] as $settingKey => $settingType) {
                $separate[] = $settingKey;
                $separateTypes[$settingType][] = $settingKey;
            }
        }

        collect($values)->removeKeys($separate)->each(function($value, $key) use ($actionsMorph) {
            $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, $value);
        });

        /**
         * Other settings, available for plugins.
         */
        foreach ($separateTypes as $type => $keys) {
            $values = only(post('settings'), $keys);

            collect($values)->each(function($value, $key) use ($actionsMorph, $type) {
                if ($type == 'array') {
                    $value = json_encode($value);
                } else {
                    $type = null;
                }
                $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, $value, $type);
            });
        }

        if ($actionsMorph->morph_id == Layouts::class) {
            $values = only(post('settings'), ['wrapperLockHide', 'wrapperLockShow', 'wrapperLockSystem']);
            collect($values)->each(function($value, $key) use ($actionsMorph) {
                $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, json_encode($value), 'array');
            });
        }

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
        $genericService->readRoute($route, false);

        return [
            'tree' => $genericService->getRouteTree(),
        ];
    }

    public function postRouteSeoAction(Route $route)
    {
        $data = only(post('seo'), ['title', 'description', 'keywords', 'image']);
        foreach ($data as $key => $val) {
            SettingsMorph::makeItHappen('pckg.generic.pageStructure.seo.' . $key, $val, Routes::class, $route->id);
        }

        return response()->respondWithSuccess();
    }

    public function postCloneRouteAction(Route $route)
    {
        $errors = [];
        $newRoute = $route->cloneRoute(post('route'), $errors);

        if ($newRoute) {
            return [
                'success' => true,
                'route'   => $newRoute,
            ];
        }

        return [
            'success'  => false,
            'messages' => $errors,
        ];
    }

}