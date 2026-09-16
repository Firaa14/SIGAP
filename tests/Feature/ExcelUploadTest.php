<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ExcelUploadService;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleExcelFile(): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DATA');

        $headers = ['NO WO', 'DESCRIPTION', 'WORKTYPE', 'STATUS', 'ASSETNUM', 'REPORTDATE'];
        foreach ($headers as $colIndex => $header) {
            $sheet->getCell([$colIndex + 1, 1])->setValue($header);
        }

        $sheet->getCell([1, 2])->setValue('WO001');
        $sheet->getCell([2, 2])->setValue('Test Maintenance WO');
        $sheet->getCell([3, 2])->setValue('PAM');
        $sheet->getCell([4, 2])->setValue('APPR');
        $sheet->getCell([5, 2])->setValue('BSGR010078');

        $excelDate = ExcelDate::PHPToExcel(new DateTime('2026-07-08'));
        $sheet->getCell([6, 2])->setValue($excelDate);
        $sheet->getStyle('F2')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);

        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'sigap_test_').'.xlsx';
        $writer->save($tempPath);

        return $tempPath;
    }

    public function test_service_can_read_excel_with_date_serial_without_error(): void
    {
        $filePath = $this->createSampleExcelFile();

        try {
            $service = app(ExcelUploadService::class);
            $result = $service->read($filePath);

            $this->assertEmpty($result['missing_required']);
            $this->assertCount(1, $result['rows']);
            $this->assertSame('WO001', $result['rows'][0]['NO WO']);
            $this->assertSame('BSGR010078', $result['rows'][0]['ASSETNUM']);
            $this->assertSame('2026-07-08', $result['rows'][0]['REPORTDATE']);
        } finally {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    public function test_preview_upload_processes_excel_file_successfully(): void
    {
        $filePath = $this->createSampleExcelFile();

        try {
            $user = User::first() ?? User::factory()->create();

            $uploadedFile = new UploadedFile(
                $filePath,
                'test_wo.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            );

            $response = $this->actingAs($user)->post(route('upload.preview'), [
                'excel_file' => $uploadedFile,
            ]);

            $response->assertStatus(200);
            $response->assertViewIs('upload.index');
            $response->assertViewHas('showPreview', true);
            $response->assertViewMissing('uploadError');
        } finally {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}
