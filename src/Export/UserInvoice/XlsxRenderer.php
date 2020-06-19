<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Export\UserInvoice;

use App\Export\Base\XlsxRenderer as BaseXlsxRenderer;
use App\Export\ExportItemInterface;
use App\Repository\Query\TimesheetQuery;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;

final class XlsxRenderer extends BaseXlsxRenderer
{

    /**
     * @param [] $exportItems
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function renderUserInvoice(array $exportItems): Response
    {
        $spreadsheet = $this->fromArrayToSpreadsheetUserInvoice($exportItems);
        $filename = $this->saveSpreadsheet($spreadsheet);

        return $this->getFileResponse($filename, 'kimai-export-user-invoice' . $this->getFileExtension());
    }

    /**
     * @param ExportItemInterface[] $exportItems
     * @param TimesheetQuery $query
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function fromArrayToSpreadsheetUserInvoice(array $exportItems): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = ["Year", "Month", "User", "Consumed hours", "Consumed days", "Total rate", "Fees"];
        $column=1;
        $row=1;
        foreach ($columns as $nameColumn) {
            $sheet->setCellValueByColumnAndRow($column, $row, $nameColumn);
            $column++;
        }

         foreach ($exportItems as $year => $yearStat) {
            foreach ($yearStat->getMonths() as $month => $monthStat) {
                foreach ($monthStat->getUsers() as $user => $userStat) {
                    $row++;
                    $column=1;
                    $sheet->setCellValueByColumnAndRow($column, $row, $yearStat->getYear()); $column++;
                    $sheet->setCellValueByColumnAndRow($column, $row, $monthStat->getMonth()); $column++;
                    $sheet->setCellValueByColumnAndRow($column, $row, $userStat->getUserId()); $column++;
                    $this->setDuration($sheet, $column, $row, $userStat->getTotalDuration());$column++;
                    $sheet->setCellValueByColumnAndRow($column, $row, round($userStat->getTotalDuration() / 3600 / 8, 2)); $column++;
                    $this->setRate($sheet, $column, $row, $userStat->getTotalRate(), 'EUR');$column++;
                    $this->setRate($sheet, $column, $row, $userStat->getFees(),'EUR');
                 }
            }
        }

        return $spreadsheet;
    }
}
