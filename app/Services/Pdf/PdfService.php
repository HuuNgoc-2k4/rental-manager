<?php

namespace App\Services\Pdf;

use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Render HTML → PDF bằng mPDF (giống cms-manager). Font FreeSerif có sẵn trong mPDF: kiểu Times, đủ dấu tiếng Việt.
 */
class PdfService
{
    /** Font-key của mPDF; Word / trình duyệt không hiểu key này nên view nhận tên font qua biến. */
    public const FONT = 'freeserif';

    /**
     * @param string $html
     * @param string $title
     * @return string nhị phân PDF
     */
    public function render(string $html, string $title): string
    {
        $tempDir = storage_path('app/mpdf-tmp');
        File::ensureDirectoryExists($tempDir);

        // Lề theo thể thức văn bản hành chính: trái 30mm, phải 20mm, trên/dưới 20mm.
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 30,
            'margin_right' => 20,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'default_font' => self::FONT,
            'shrink_tables_to_fit' => 0,
            'tempDir' => $tempDir
        ]);
        $mpdf->SetTitle($title);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
