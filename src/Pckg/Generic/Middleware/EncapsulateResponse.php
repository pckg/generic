<?php namespace Pckg\Generic\Middleware;

use Pckg\Concept\Reflect;
use Pckg\Framework\Request;
use Pckg\Framework\Response;
use Pckg\Generic\Controller\Generic;

class EncapsulateResponse
{

    protected $response;

    protected $request;

    public function __construct(Response $response, Request $request)
    {
        $this->response = $response;
        $this->request = $request;
    }

    public function execute(callable $next)
    {
        if ($this->request->isGet()) {
            $viewData = (string)$this->response->getViewData();

            if (substr($viewData, 0, 5) !== '<html') {
                $viewData = Reflect::create(Generic::class)->wrapIntoGeneric($viewData);
                $this->response->setViewData($viewData);
            }
        }

        return $next();
    }

}