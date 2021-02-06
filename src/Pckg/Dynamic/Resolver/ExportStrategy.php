<?php

namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Service\Export;
use Pckg\Framework\Provider\RouteResolver;

class ExportStrategy implements RouteResolver
{

    protected $exportService;

    public function __construct(Export $exportService)
    {
        $this->exportService = $exportService;
    }

    public function resolve($value)
    {
        return $this->exportService->useStrategy($value);
    }

    public function parametrize($record)
    {
        return $record;
    }
}
