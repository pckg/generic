<?php namespace Pckg\Generic\Controller;

use Pckg\Database\Relation\MorphedBy;
use Pckg\Framework\Controller;
use Pckg\Generic\Entity\Actions;
use Pckg\Generic\Entity\Variables;
use Pckg\Generic\Form\ActionMorph;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Route;

class Routes extends Controller
{

    public function getPageStructureAction(
        Route $route,
        ActionMorph $actionMorphForm,
        ActionsMorph $actionsMorph
    )
    {
        $variables = (new Variables())->all();

        /**
         * We also need to include layout variables, if any.
         */
        if ($layout = $route->layout) {
            $layout->actions;
        }

        $actionMorphForm->initFields();
        $actionMorphForm->populateFromRecord($actionsMorph);

        $routeActions = $route->actions(
            function(MorphedBy $actionsMorphs) {
                $actionsMorphs->getMiddleEntity()->withVariable();
            }
        )->groupBy(
            function(Action $action) {
                return $action->pivot->variable->slug;
            }
        );

        $layoutActions = $route->layout->actions(
            function(MorphedBy $actionsMorphs) {
                $actionsMorphs->getMiddleEntity()->withVariable();
            }
        )->groupBy(
            function(Action $action) {
                return $action->pivot->variable->slug;
            }
        );

        return view(
            'routes\pageStructure',
            [
                'route'           => $route, // route we're editing
                'variables'       => $variables, // available variables
                'layout'          => $layout,
                'actionMorphForm' => $actionMorphForm,
                'actionsMorph'    => $actionsMorph,
                'actions'         => (new Actions())->all(),
                'routeActions'    => $routeActions,
                'layoutActions'   => $layoutActions,
            ]
        );
    }

    public function postPageStructureAction(
        Route $route,
        ActionMorph $actionMorphForm,
        ActionsMorph $actionsMorph
    )
    {
        return $_POST;
    }

}