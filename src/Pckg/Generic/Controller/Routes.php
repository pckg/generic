<?php namespace Pckg\Generic\Controller;

use Pckg\Dynamic\Record\Record;
use Pckg\Framework\Controller;
use Pckg\Generic\Entity\Routes as RoutesEntity;
use Pckg\Generic\Entity\Variables;
use Pckg\Generic\Form\ActionMorph;
use Pckg\Generic\Record\ActionsMorph;

class Routes extends Controller
{

    public function getPageStructureAction(
        Record $dynamicRecord,
        ActionMorph $actionMorphForm,
        ActionsMorph $actionsMorph
    )
    {
        $route = (new RoutesEntity())->withActions()->where('id', $dynamicRecord->id)->one();
        $variables = (new Variables())->all();

        /**
         * We also need to include layout variables, if any.
         */
        if ($layout = $route->layout) {
            $layout->actions;
        }

        $actionMorphForm->initFields();
        $actionMorphForm->populateFromRecord($actionsMorph);

        return view('routes\pageStructure', [
            'route'           => $route,
            'variables'       => $variables,
            'layout'          => $layout,
            'actionMorphForm' => $actionMorphForm,
            'actionsMorph'    => $actionsMorph,
        ]);
    }

}