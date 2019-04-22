<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;

class Html extends AbstractStrategy
{

    protected $responseType = 'text/html';

    protected $extension = 'html';

    public function save()
    {
        $file = path('tmp') . $this->getFilename();
        $data = $this->getData();
        $content = view('Pckg/Dynamic:export/html', [
                                                      'lines'   => $data,
                                                      'headers' => array_keys($data),
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