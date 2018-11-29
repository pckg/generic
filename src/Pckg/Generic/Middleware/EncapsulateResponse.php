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
        if ($this->request->isGet() && !$this->request->isAjax()) {
            $output = $this->response->getOutput();

            if (is_string($output) && (substr($output, 0, 5)) !== '<html' && strtolower(
                                                                                 substr($output, 0, 9)
                                                                             ) != '<!doctype'
            ) {
                $tags = router()->get('tags', []);
                $template = config('pckg.generic.layouts.default', 'Pckg/Generic:frontend');
                $disable = false;
                foreach ($tags as $tag) {
                    if ($tag == EncapsulateResponse::class . '.disable') {
                        $disable = true;
                        break;
                    }
                    if (strpos($tag, 'layout:') !== 0) {
                        continue;
                    }

                    $key = substr($tag, strlen('layout:'));
                    $template = config('pckg.generic.layouts.' . $key, $template);
                }

                if (!$disable) {
                    $output = $template == 'Pckg/Generic:backend'
                                            ? Reflect::create(Generic::class)->wrapIntoGeneric($output, $template)
                                                : Reflect::create(Generic::class)->wrapIntoGenericContainer($output, $template);
                    // $output = Reflect::create(Generic::class)->wrapIntoGeneric($output, $template);
                    $this->response->setOutput($output);
                }
            }
        }

        return $next();
    }

}