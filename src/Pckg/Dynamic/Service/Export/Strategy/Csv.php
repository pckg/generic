<?php

namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;

class Csv extends AbstractStrategy
{

    protected $responseType = 'text/csv';
    protected $extension = 'csv';
    public function save()
    {
        $file = path('tmp') . $this->getFilename();
        $fp = fopen($file, 'w');
    /**
             * Add header.
             */
        if ($headers = $this->getHeaders()) {
            fputcsv($fp, array_keys($headers));
        }

        /**
         * Add data.
         */
        foreach ($this->getData() as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
        return $file;
    }

    public function prepare()
    {
        $file = $this->save();
        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }
}
