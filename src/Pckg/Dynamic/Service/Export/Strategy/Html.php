<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;
use Pckg\Dynamic\Service\Export\Strategy;

class Html extends AbstractStrategy
{

    protected $responseType = 'text/html';

    protected $extension = 'html';

    public function prepare()
    {
        $this->setFileContent(
            view(
                'Pckg\Dynamic:export/html',
                [
                    'lines' => $this->getData(),
                ]
            )->autoparse()
        );
    }

}