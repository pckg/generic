<?php

namespace Pckg\Dynamic\Service\Export\Strategy;

class Pdf extends Html
{
    protected $responseType = 'application/pdf';
    protected $extension = 'pdf';
    public function save()
    {
        $finalFilename = $this->getFilename();
        $tempFilename = $finalFilename . '.html';
        $this->setFileName($tempFilename);
    /**
             * Save as html.
             */
        $file = parent::save();
        $domain = server('HTTP_HOST', config('domain'));
        $pdf = \Derive\Basket\Service\Pdf::make('https://' . $domain . '/storage/tmp/' . $tempFilename, path('tmp'), $finalFilename);

        return path('tmp') . $finalFilename;
    }

    public function prepare()
    {
        $file = $this->save();
        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }
}
