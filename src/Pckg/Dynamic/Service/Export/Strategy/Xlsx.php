<?php namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;
use Pckg\Dynamic\Service\Export\Strategy;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class Xlsx extends AbstractStrategy
{

    protected $responseType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    protected $extension = 'xlsx';

    public function prepare()
    {
        $file = path('tmp') . sha1(microtime());
        $spreadsheet = new Spreadsheet();
        $lines = $this->getData();

        /**
         * Make header
         */
        $i = 1;
        $j = 1;
        foreach ($lines[0] as $key => $val) {
            $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValueByColumnAndRow($j, $i, $key)
                        ->getStyle("$j:$i")
                        ->getFont()
                        ->setBold(true);
            $j++;
        }

        /**
         * Make data
         */
        //dd($lines);
        foreach ($lines as $line) {
            $i++;
            $j = 1;
            foreach ($line as $val) {
                $val = (string)preg_replace("/[^A-Za-z0-9]/s", '', $val);
                $spreadsheet->setActiveSheetIndex(0)
                            ->setCellValueByColumnAndRow($j, $i, $val);
                $j++;
            }
        }

        /**
         * Set column widths
         */
        $sheet = $spreadsheet->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        /**
         * Save file.
         */
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($file);

        /**
         * Implement strategy.
         */
        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }

}