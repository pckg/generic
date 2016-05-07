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
            $output = $this->response->getOutput();

            if (is_string($output) && substr($output, 0, 5) !== '<html') {
                $output = Reflect::create(Generic::class)->wrapIntoGeneric($output);
                $this->response->setOutput($output);
            }
        }

        return $next();
    }

}