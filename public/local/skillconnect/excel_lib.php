<?php
namespace local_skillconnect\excel;

defined('MOODLE_INTERNAL') || die();

/**
 * Excel import/export service for CLC student records.
 *
 * Handles: template download, preview, import, and error report export.
 */
class clc_import_service {
    private static array $months = [];
    private static array $validclasses = [];

    private static function init(): void {
        if (empty(self::$months)) {
            for ($m = 1; $m <= 12; $m++) {
                self::$months[$m] = userdate(gmmktime(0, 0, 0, $m, 1, 2000), '%B');
            }
            for ($c = 1; $c <= 10; $c++) {
                self::$validclasses['Class ' . $c] = 'Class ' . $c;
            }
            self::$validclasses['other'] = 'Other / Manual Input';
        }
    }

    public static function download_template(string $programkey): void {
        self::init();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Merge and center title.
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Computer Literacy Centers (CLCs) Student List');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Column headers.
        $headers = [
            'School Name', 'Month', 'Year', 'Student Name', "Father's Name",
            'Class', 'Division', 'District', 'Upazila/Thana',
            "Parent/Guardian's Mobile Number", 'Email Address', 'Gender'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '2';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $col++;
        }

        // Sample record.
        $sample = [
            'Sherpur Pilot Girls High School',
            'July',
            '2026',
            'Sample Student',
            'Sample Father',
            'Class 8',
            'Rajshahi',
            'Bogura',
            'Sherpur',
            '01700000000',
            'sample@example.com',
            'Female',
        ];

        $col = 1;
        foreach ($sample as $value) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3';
            if ($col === 10) {
                $sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($cell, $value);
            }
            $col++;
        }

        // Ensure mobile column (J) is text format so leading zeros are preserved.
        $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Auto-width.
        foreach (range('A', 'L') as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        // Download.
        $filename = 'CLC_Student_Template.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function export_error_report(array $errors): void {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Import Error Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        foreach ($errors as $error) {
            $sheet->setCellValue('A' . $row, $error);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);

        $filename = 'CLC_Import_Errors.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function preview(string $filepath, string $programkey): array {
        self::init();

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filepath);
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $objReader->load($filepath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headerRow = $rows[2] ?? [];
        $expectedHeaders = [
            'School Name', 'Month', 'Year', 'Student Name', "Father's Name",
            'Class', 'Division', 'District', 'Upazila/Thana',
            "Parent/Guardian's Mobile Number", 'Email Address', 'Gender'
        ];

        $errors = [];
        $valid = [];
        $duplicates = [];
        $invalid = [];
        $seen = [];

        // Validate headers.
        foreach ($expectedHeaders as $i => $expected) {
            $actual = trim((string)($headerRow[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1)] ?? ''));
            if ($actual !== $expected) {
                $errors[] = "Missing or incorrect header: expected '$expected', got '$actual'";
            }
        }

        // Check for Date column.
        foreach ($headerRow as $col => $header) {
            if ($header && stripos($header, 'date') === 0) {
                $errors[] = 'Date column found (column ' . $col . '). Please remove it.';
            }
        }

        // Process data rows.
        for ($i = 3; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? [];
            $rowNum = $i;

            // Skip sample record.
            if (trim((string)($row['A'] ?? '')) === 'Sherpur Pilot Girls High School'
                && trim((string)($row['D'] ?? '')) === 'Sample Student') {
                continue;
            }

            if (empty(array_filter($row))) {
                continue;
            }

            $record = [
                'row' => $rowNum,
                'school' => trim((string)($row['A'] ?? '')),
                'month' => trim((string)($row['B'] ?? '')),
                'year' => trim((string)($row['C'] ?? '')),
                'name' => trim((string)($row['D'] ?? '')),
                'father_name' => trim((string)($row['E'] ?? '')),
                'class' => trim((string)($row['F'] ?? '')),
                'custom_class' => '',
                'division' => trim((string)($row['G'] ?? '')),
                'district' => trim((string)($row['H'] ?? '')),
                'upazila' => trim((string)($row['I'] ?? '')),
                'mobile' => trim((string)($row['J'] ?? '')),
                'email' => trim((string)($row['K'] ?? '')),
                'gender' => trim((string)($row['L'] ?? '')),
            ];

            $rowErrors = self::validate_record($record, $programkey, $seen);

            if (empty($rowErrors)) {
                $valid[] = $record;
            } else {
                $record['errors'] = $rowErrors;
                $invalid[] = $record;
            }
        }

        return [
            'total' => count($valid) + count($invalid),
            'valid' => $valid,
            'invalid' => $invalid,
            'duplicates' => $duplicates,
            'errors' => $errors,
        ];
    }

    public static function import(array $records, string $programkey): array {
        global $DB;

        self::init();

        $inserted = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        $transaction = $DB->start_delegated_transaction();

        try {
            foreach ($records as $record) {
                $month = (int)array_search($record['month'], self::$months, true);
                if ($month === false) {
                    $month = (int)$record['month'];
                }

                $year = (int)$record['year'];
                $class = $record['class'];
                $customclass = $record['custom_class'] ?? '';

                $save = (object)[
                    'program' => $programkey,
                    'name' => trim($record['name']),
                    'father_name' => trim($record['father_name']),
                    'school' => trim($record['school']),
                    'class' => $class,
                    'custom_class' => $customclass,
                    'division' => trim($record['division']),
                    'district' => trim($record['district']),
                    'upazila' => trim($record['upazila']),
                    'mobile' => trim($record['mobile']),
                    'email' => trim($record['email']),
                    'gender' => trim($record['gender']),
                    'month' => $month,
                    'year' => $year,
                    'timecreated' => mktime(0, 0, 0, $month, 1, $year),
                ];

                try {
                    $DB->insert_record('local_sc_program_participants', $save, false);
                    $inserted++;
                } catch (\dml_exception $e) {
                    $failed++;
                    $errors[] = "Row {$record['row']}: " . $e->getMessage();
                }
            }

            $DB->commit_delegated_transaction($transaction);
        } catch (\Exception $e) {
            $DB->rollback_delegated_transaction($transaction);
            $failed = count($records);
            $errors[] = 'Transaction failed: ' . $e->getMessage();
        }

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private static function validate_record(array $record, string $programkey, array &$seen): array {
        $errors = [];

        // Required fields.
        if (trim($record['name']) === '') {
            $errors[] = 'Student name is empty.';
        }
        if (trim($record['school']) === '') {
            $errors[] = 'School name is empty.';
        }
        if (trim($record['division']) === '') {
            $errors[] = 'Division is empty.';
        }
        if (trim($record['district']) === '') {
            $errors[] = 'District is empty.';
        }
        if (trim($record['upazila']) === '') {
            $errors[] = 'Upazila/Thana is empty.';
        }

        // Month validation.
        $month = array_search($record['month'], self::$months, true);
        if ($month === false && !in_array($record['month'], range(1, 12), true)) {
            $errors[] = "Invalid month: {$record['month']}";
        }

        // Year validation.
        if (!preg_match('/^\d{4}$/', $record['year'])) {
            $errors[] = "Invalid year: {$record['year']}";
        }

        // Class validation.
        if (!in_array($record['class'], array_keys(self::$validclasses), true)) {
            $errors[] = "Invalid class: {$record['class']}";
        }

        // Mobile validation.
        $mobile = preg_replace('/[\s\-\(\)]/', '', $record['mobile']);
        if (strlen($mobile) === 10 && preg_match('/^[1-9][0-9]{9}$/', $mobile)) {
            $mobile = '0' . $mobile;
        }
        if (!preg_match('/^01[3-9][0-9]{8}$/', $mobile)) {
            $errors[] = "Invalid mobile number: {$record['mobile']}";
        }

        // Email validation.
        if (!empty($record['email']) && !validate_email($record['email'])) {
            $errors[] = "Invalid email: {$record['email']}";
        }

        // Gender validation.
        if (!in_array($record['gender'], ['Male', 'Female', 'Other'], true)) {
            $errors[] = "Invalid gender: {$record['gender']}";
        }

        // Duplicate check.
        $dupkey = strtolower(trim($record['school'] . '|' . $record['month'] . '|' . $record['year'] . '|' . $record['name'] . '|' . $record['father_name'] . '|' . $record['mobile'] . '|' . $record['class']));
        if (isset($seen[$dupkey])) {
            $errors[] = 'Duplicate entry detected.';
        } else {
            $seen[$dupkey] = true;
        }

        return $errors;
    }
}
