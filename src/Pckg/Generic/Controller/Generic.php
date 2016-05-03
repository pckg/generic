<?php

namespace Pckg\Generic\Controller;

use Pckg\Database\Query;
use Pckg\Framework\Inter\Entity\Languages;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Framework\View;
use Pckg\Manager\Asset as AssetManager;
use Pckg\Manager\Meta as MetaManager;
use Pckg\Manager\Seo as SeoManager;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Record\Route;
use Pckg\Generic\Service\Generic as GenericService;
use Pckg\Generic\Service\Generic\Action;
use Pckg\Generic\Service\Generic\CustomAction;

/**
 * Class Generic
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
        AssetManager $assetManager,
        MetaManager $metaManager,
        SeoManager $seoManager,
        GenericService $genericService
    ) {
        $assetManager->executeCore();
        $metaManager->addViewport();
        $metaManager->addContentType();

        $this->genericService = $genericService;
    }

    public function getGenericAction(Route $route)
    {
        $this->genericService->readRoute($route);

        $vars = $this->genericService->getVariables();

        return $route->layout
            ? view($route->layout->template ?: 'Pckg\Generic:generic', $vars)
            : $vars;
    }

    public function wrapIntoGeneric($view)
    {
        $center = $this->genericService->touchBlock('content');

        /**
         * We add view action to center:0.
         */
        $center->addAction(new CustomAction($view));

        $vars = $this->genericService->getVariables();

        return view('Pckg\Generic:generic', $vars);
    }

    public function getContentAction(Action $action)
    {
        return view('contents', [
            'contents' => (new Contents())->all(),
        ]);
    }

    public function getLanguagesAction(Languages $languages)
    {
        return view('languages', [
            'languages' => $languages->joinTranslations()->all(),
        ]);
    }

}
