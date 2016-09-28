<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;
use Pckg\Dynamic\Service\Export\Strategy;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class Docx extends AbstractStrategy
{

    protected $responseType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    protected $extension = 'docx';

    public function prepare()
    {
        $file = path('tmp') . sha1(microtime());
        $lines = $this->getData();

        $phpWord = new PhpWord();
        $section = $phpWord->addSection(['orientation' => 'landscape']);

        /**
         * Make header
         */
        $table = $section->addTable(['width' => 100 * 50]);
        $i = 0;
        $j = 0;
        $table->addRow();
        foreach ($lines[0] as $key => $val) {
            $table->addCell(1750)->addText($key);
            $j++;
        }

        /**
         * Make data
         */
        foreach ($lines as $line) {
            $i++;
            $j = 0;
            $table->addRow();
            foreach ($line as $val) {
                $table->addCell(1750)->addText($val);
                $j++;
            }
        }

        /**
         * Save file.
         */
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($file);

        /**
         * Implement strategy.
         */
        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }

}