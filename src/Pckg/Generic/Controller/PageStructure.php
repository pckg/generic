<?php namespace Pckg\Generic\Controller;

use Pckg\Database\Relation\MorphedBy;
use Pckg\Generic\Entity\Actions;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Entity\Variables;
use Pckg\Generic\Form\ActionMorph;
use Pckg\Generic\Record\Action;

class PageStructure
{

    public function getPageStructureAction(ActionMorph $actionMorphForm)
    {
        $actionMorphForm->initFields();

        vueManager()->addView('Pckg/Generic:routes/_pageStructure', [
            'actionMorphForm' => $actionMorphForm,
        ]);

        return view('routes\pageStructure');
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

    public function getRouteAction($route)
    {
        return [
            'route' => (new Routes())->where('id', $route)->one(),
        ];
    }

    public function getRouteActionsAction($route)
    {
        $route = (new Routes())->where('id', $route)->one();

        return [
            'routeActions' => $route->actions(function(MorphedBy $actions) {
                $actions->getMiddleEntity()->withAllPermissions();
            })->map(function(Action $action) {
                $array = $action->toArray();
                $array['pivot']['permissions'] = $action->pivot->allPermissions->map('user_group_id');

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

    public function deleteActionsMorphAction($actionsMorph)
    {
        (new ActionsMorphs())->where('id', $actionsMorph)->delete();

        return response()->respondWithAjaxSuccess();
    }

}