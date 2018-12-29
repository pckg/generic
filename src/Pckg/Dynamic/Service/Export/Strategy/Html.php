<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;

class Html extends AbstractStrategy
{

    protected $responseType = 'text/html';

    protected $extension = 'html';

    public function save()
    {
        $file = path('tmp') . sha1(microtime()) . '.' . $this->extension;
        $content = view('Pckg/Dynamic:export/html', [
                                                      'lines'   => $this->getData(),
                                                      'headers' => $this->headers,
                                                  ])->autoparse();
        file_put_contents($file, $content);

        return $file;
    }

    public function prepare()
    {
        $file = $this->save();

        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }

}