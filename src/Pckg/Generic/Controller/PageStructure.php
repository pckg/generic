<?php namespace Pckg\Generic\Controller;

use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\MorphedBy;
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
use Pckg\Manager\Upload;

class PageStructure
{

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

    public function getContentsAction()
    {
        return [
            'contents' => (new Contents())->joinTranslations()->all(),
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
                $actions->getMiddleEntity()->withContent(function(BelongsTo $content) {
                    $content->joinTranslations();
                });
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
                $actions->getMiddleEntity()->withContent(function(BelongsTo $content) {
                    $content->joinTranslations();
                });
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
        $actionsMorph->delete();

        return response()->respondWithAjaxSuccess();
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
        $actionsMorphs->each(function(ActionsMorph $actionsMorph) use ($orders) {
            $actionsMorph->setAndSave([
                                          'order'     => $orders[$actionsMorph->id]['order'],
                                          'parent_id' => $orders[$actionsMorph->id]['parent'],
                                      ]);
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
            'padding'         => '',
            'margin'          => '',
            'scopes'          => '',
            'scopesArr'       => [],
            'bgColor'         => '',
            'bgImage'         => '',
            'bgSize'          => '',
            'bgAttachment'    => '',
            'bgRepeat'        => '',
            'bgVideo'         => '',
            'bgVideoSource'   => '',
            'bgVideoDisplay'  => '',
            'bgVideoAutoplay' => '',
            'bgVideoControls' => '',
            'bgVideoLoop'     => '',
        ];
    }

    public function getActionsMorphContentAction(ActionsMorph $actionsMorph)
    {
        return response()->respondWithSuccess([
                                                  'content' => $actionsMorph->content(function(BelongsTo $content) {
                                                      $content->joinTranslations();
                                                  }),
                                              ]);
    }

    public function postDuplicateActionsMorphContentAction(ActionsMorph $actionsMorph)
    {
        $content = $actionsMorph->content(function(BelongsTo $content) {
            $content->joinTranslations();
        })->saveAs();

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
                Setting $settingsMorph
            ) {
                return [
                    'slug'  => str_replace('pckg.generic.pageStructure.', '',
                                           $settingsMorph->slug),
                    'value' => $settingsMorph->pivot->value,
                ];
            })->keyBy('slug')->map('value');

        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $val) {
            if ($settings->hasKey($key)) {
                continue;
            }

            $settings->push($val, $key);
        }

        $settings->push(explode(' ', $settings->getKey('class')), 'scopesArr');

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
        $values['class'] .= ' ' . implode(' ', post('settings.scopesArr', []));
        collect($values)->each(function($value, $key) use ($actionsMorph) {
            $actionsMorph->saveSetting('pckg.generic.pageStructure.' . $key, $value);
        });

        return response()->respondWithSuccess();
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
        $actionsMorph->addPartial(post('partial', null));

        return response()->respondWithSuccess();
    }

}