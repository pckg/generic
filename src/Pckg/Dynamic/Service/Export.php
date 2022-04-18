<?php

namespace Pckg\Dynamic\Service;

use Exception;
use Pckg\Dynamic\Service\Export\Strategy;
use Pckg\Dynamic\Service\Export\Strategy\Csv;
use Pckg\Dynamic\Service\Export\Strategy\Docx;
use Pckg\Dynamic\Service\Export\Strategy\Html;
use Pckg\Dynamic\Service\Export\Strategy\Xlsx;

class Export
{
    protected $strategy;
    protected $strategies = [
        'txt'  => Strategy\Txt::class,
        'csv'  => Csv::class,
        'html' => Html::class,
        'xlsx' => Xlsx::class,
        'docx' => Docx::class,
        'pdf'  => Strategy\Pdf::class,
    ];
/**
     * @param $strategy
     *
     * @return Strategy
     * @throws Exception
     */
    public function useStrategy($strategy)
    {
        if (!isset($this->strategies[$strategy])) {
            throw new Exception('Export strategy \'' . $strategy . '\' does not exist!');
        }

        $strategy = $this->strategies[$strategy];
        $this->strategy = new $strategy();
        return $this->strategy;
    }
}
