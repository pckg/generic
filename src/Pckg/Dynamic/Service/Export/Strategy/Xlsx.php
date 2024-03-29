<?php

namespace Pckg\Dynamic\Service\Export\Strategy;

use Pckg\Dynamic\Service\Export\AbstractStrategy;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class Xlsx extends AbstractStrategy
{
    protected $responseType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    protected $extension = 'xlsx';
    public function save()
    {
        $file = path('tmp') . $this->getFilename();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Comms')
                    ->setLastModifiedBy('Comms System')
                    ->setTitle('Comms Export')
                    ->setSubject('Comms Export')
                    ->setDescription('Comms Export')
                    ->setKeywords('comms export')
                    ->setCategory('Comms Export');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $lines = $this->getData();
    /**
             * Make header
             */
        $i = 1;
        $j = 1;
        foreach ($lines[0] ?? [] as $key => $val) {
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
        foreach ($lines as $line) {
            $i++;
            $j = 1;
            foreach ($line as $val) {
                $spreadsheet->setActiveSheetIndex(0)->setCellValueByColumnAndRow($j, $i, $val);
                $j++;
            }
        }

        /**
         * Set column widths
         */
        $sheet = $spreadsheet->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        if ($lines) {
            $cellIterator->setIterateOnlyExistingCells(true);
        }

        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        /**
         * Save file.
         */
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($file);
        return $file;
    }

    public function prepare()
    {
        $file = $this->save();
/**
         * Implement strategy.
         */
        $this->setFileContent(file_get_contents($file));
        unlink($file);
    }
}
