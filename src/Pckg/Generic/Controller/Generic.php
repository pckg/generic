<?php namespace Pckg\Generic\Controller;

use Pckg\Database\Query;
use Pckg\Framework\Inter\Entity\Languages;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Framework\View;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\CustomAction;
use Pckg\Manager\Asset as AssetManager;
use Pckg\Manager\Meta as MetaManager;
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
        MetaManager $metaManager,
        SeoManager $seoManager,
        GenericService $genericService
    ) {
        $metaManager->addViewport();
        $metaManager->addContentType();

        $this->genericService = $genericService;
    }

    public function getGenericAction(Route $route)
    {
        $this->genericService->readRoute($route);

        $vars = $this->genericService->getVariables();

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

    public function wrapIntoGeneric($view, $template = 'Pckg/Generic:backend')
    {
        $center = $this->genericService->touchBlock('content');

        /**
         * We add view action to center:0.
         */
        $center->addAction(new CustomAction($view));

        $vars = $this->genericService->getVariables();

        return view($template, $vars);
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
                'languages' => (new Languages())->all(),
            ]
        );
    }

}
