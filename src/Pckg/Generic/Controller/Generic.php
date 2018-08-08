<?php namespace Pckg\Generic\Controller;

use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\CustomAction;
use Pckg\Locale\Entity\Languages;
use Pckg\Locale\Record\Language;
use Pckg\Manager\Asset as AssetManager;
use Pckg\Manager\Seo as SeoManager;

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
        $this->genericService->readRoute($route);

        $vars = $this->genericService->getVariables();

        $route->applySeoSettings();

        return $route->layout
            ? view($route->layout->template ?: 'Pckg/Generic:backend', $vars)
            : $vars;
    }

    public function postGenericAction(Route $route)
    {
        $this->genericService->readRoute($route);

        $vars = $this->genericService->getVariables();

        return $vars;
    }

    public function headGenericAction()
    {
        return null;
    }

    public function wrapIntoGeneric($view, $template = 'Pckg/Generic:backend')
    {
        message('Wrapping into generic');
        $center = $this->genericService->touchBlock('content');

        /**
         * We add view action to center:0.
         */
        $center->addAction(new CustomAction($view));

        $this->genericService->readSystemRoute($template);

        $vars = $this->genericService->getVariables();

        return view($template, $vars);
    }

    public function wrapIntoGenericContainer($view, $template = 'Pckg/Generic:backend')
    {
        message('Wrapping into container');
        $view = '<div class="container">' . $view . '</div>';

        return $this->wrapIntoGeneric($view, $template);
    }

    public function getContentAction(Action $action)
    {
        return view(
            'contents',
            [
                'contents' => (new Contents())->all(),
            ]
        );
    }

    public function getLanguagesAction()
    {
        return view(
            'languages',
            [
                'languages' => localeManager()->getFrontendLanguages(),
            ]
        );
    }

}
