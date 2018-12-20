<?php namespace Pckg\Generic\Controller;

use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\CustomAction;

/**
 * Class Generic
 *
 * @package Pckg\Generic\Controller
 */
class Generic
{

    /**
     * @var GenericService
     */
    protected $genericService;

    /**
     * @var
     */
    protected $route;

    public function __construct(
        GenericService $genericService
    ) {
        $this->genericService = $genericService;
    }

    public function getGenericAction(Route $route)
    {
        measure('Reading route', function() use ($route) {
            $this->genericService->readRoute($route);
        });

        measure('Building actions', function() {
            $this->genericService->build();
        });

        $route->applySeoSettings();
        $auth = auth();

        return measure('Stringifying output', function() use ($route, $auth) {
            $structure = '<component v-for="a in $store.getters.rootElements" :action-id="a.id" :is="\'pckg-\' + a.type" :key="a.id"></component>';

            if ($auth->isLoggedIn() && $auth->isAdmin()) {
                $structure = '<pckg-frontpage-deck v-if="$store.getters.genericRoute"></pckg-frontpage-deck>' .
                    '<template v-if="[\'threesome\', \'device\'].indexOf($store.state.generic.genericMode) >= 0"><pckg-threesome></pckg-threesome></template>' .
                    '<template v-else>' . $structure . '</template>';
            }

            $vars = [
                'content' => $structure,
            ];

            return (string)($route->layout ? view($route->layout->template ?: 'Pckg/Generic:backend', $vars) : $vars);
        });
    }

    public function postGenericAction(Route $route)
    {
        return null;
    }

    public function headGenericAction()
    {
        return null;
    }

    public function optionsGenericAction()
    {
        return null;
    }

    public function wrapIntoGeneric($view, $template = 'Pckg/Generic:frontend')
    {
        message('Wrapping into generic ' . $template);
        $center = $this->genericService->touchBlock('content');

        /**
         * We add view action to center:0.
         */
        $center->addAction(new CustomAction($view));

        $this->genericService->readSystemRoute($template);

        $vars = $this->genericService->getVariables();

        return view($template, $vars);
    }

    public function wrapIntoGenericContainer($view, $template = 'Pckg/Generic:frontend')
    {
        message('Wrapping into container');
        $view = '<div class="container">' . $view . '</div>';

        return $this->wrapIntoGeneric($view, $template);
    }

    public function getContentAction(Action $action)
    {
        return view('contents', [
                                  'contents' => (new Contents())->all(),
                              ]);
    }

    public function getLanguagesAction()
    {
        return view('languages', [
                                   'languages' => localeManager()->getFrontendLanguages(),
                               ]);
    }

}
