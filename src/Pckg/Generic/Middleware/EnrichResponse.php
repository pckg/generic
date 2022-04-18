<?php

namespace Pckg\Generic\Middleware;

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
        return $next();
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
            if (!is_array($output)) {
                $output = (string)$output;
                $generic = Reflect::create(Generic::class);
                if (
                    (substr($output, 0, 5)) !== '<html'
                    && strtolower(substr($output, 0, 9)) !== '<!doctype'
                    && strtolower(substr($output, 0, 5)) !== '<?xml'
                ) {
                    $output = $generic->wrapIntoGenericContainer($output, 'Pckg/Generic:frontend');
                }
            }

            /**
             * Parse new output and set it as response.
             */
            $this->response->setOutput($output);
        }

        return $next();
    }
}
