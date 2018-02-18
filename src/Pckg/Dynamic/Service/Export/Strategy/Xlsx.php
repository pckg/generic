<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;
use Pckg\Dynamic\Service\Export\Strategy;
use PHPExcel;
use PHPExcel_IOFactory;

class Xlsx extends AbstractStrategy
{

    protected $responseType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    protected $extension = 'xlsx';

    public function prepare()
    {
        $file = path('tmp') . sha1(microtime());
        $objPHPExcel = new PHPExcel();
        $lines = $this->getData();

        /**
         * Make header
         */
        $i = 1;
        $j = 0;
        foreach ($lines[0] as $key => $val) {
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValueByColumnAndRow($j, $i, $key)
                        ->getStyle("$j:$i")
                        ->getFont()
                        ->setBold(true);
            $j++;
        }

        /**
         * Make data
         */
        foreach ($lines as $line) {
            $i++;
            $j = 0;
            foreach ($line as $val) {
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValueByColumnAndRow($j, $i, $val);
                $j++;
            }
        }

        /**
         * Set column widths
         */
        $sheet = $objPHPExcel->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        /**
         * Save file.
         */
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file);

        /**
         * Implement strategy.
         */
        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }

}