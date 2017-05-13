<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;

class Html extends AbstractStrategy
{

    protected $responseType = 'text/html';

    protected $extension = 'html';

    public function prepare()
    {
        $this->setFileContent(
            view(
                'Pckg/Dynamic:export/html',
                [
                    'lines'   => $this->getData(),
                    'headers' => $this->headers,
                ]
            )->autoparse()
        );
    }

}