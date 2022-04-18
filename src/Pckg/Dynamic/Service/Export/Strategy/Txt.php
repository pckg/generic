<?php

namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;

class Txt extends Csv
{
    protected $responseType = 'text/csv';
    protected $extension = 'txt';
}
