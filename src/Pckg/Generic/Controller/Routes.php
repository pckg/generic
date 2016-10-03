<?php namespace Pckg\Generic\Controller;

use Pckg\Framework\Controller;
use Pckg\Generic\Entity\Variables;
use Pckg\Generic\Form\ActionMorph;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Route;

class Routes extends Controller
{

    public function getPageStructureAction(
        Route $route,
        ActionMorph $actionMorphForm,
        ActionsMorph $actionsMorph
    ) {
        $variables = (new Variables())->all();

        /**
         * We also need to include layout variables, if any.
         */
        if ($layout = $route->layout) {
            $layout->actions;
        }

        $actionMorphForm->initFields();
        $actionMorphForm->populateFromRecord($actionsMorph);

        return view(
            'routes\pageStructure',
            [
                'route'           => $route,
                'variables'       => $variables,
                'layout'          => $layout,
                'actionMorphForm' => $actionMorphForm,
                'actionsMorph'    => $actionsMorph,
            ]
        );
    }

    public function postPageStructureAction(
        Route $route,
        ActionMorph $actionMorphForm,
        ActionsMorph $actionsMorph
    ) {
        return $_POST;
    }

}