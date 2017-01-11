<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;
use Pckg\Dynamic\Service\Export\Strategy;

class Csv extends AbstractStrategy
{

    protected $responseType = 'text/csv';

    protected $extension = 'csv';

    public function prepare()
    {
        $file = path('tmp') . sha1(microtime());

        $fp = fopen($file, 'w');

        /**
         * Add header.
         */
        if ($this->getData()) {
            fputcsv($fp, array_keys($this->getData()[0]));
        }

        /**
         * Add data.
         */
        foreach ($this->getData() as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
        $this->setFileContent(file_get_contents($file));
        unlink($file);

    }

}