<?php namespace Pckg\Generic\Middleware;

use Pckg\Concept\Reflect;
use Pckg\Framework\Request;
use Pckg\Framework\Response;
use Pckg\Generic\Controller\Generic;

class EnrichResponse
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
        if ($this->request->isGet() && !$this->request->isAjax()) {
            /**
             * This is used as main content.
             */
            $output = $this->response->getOutput();

            /**
             * Let's get all other variables.
             *
             * @T00D00
             */
            $generic = Reflect::create(Generic::class);

            /**
             * Parse new output and set it as response.
             */
            $this->response->setOutput($output);
        }

        return $next();
    }

}