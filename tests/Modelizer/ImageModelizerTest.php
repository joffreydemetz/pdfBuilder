<?php

namespace JDZ\Pdf\Tests\Modelizer;

use JDZ\Pdf\Data;
use JDZ\Pdf\Helper;
use JDZ\Pdf\Modelizer\ImageModelizer;
use JDZ\Pdf\Tests\Support\SpyPdf;
use PHPUnit\Framework\TestCase;

class ImageModelizerTest extends TestCase
{
    private const IMAGE = __DIR__ . '/../fixtures/images/landscape-200x100.png';

    public function testToPdfDrawsTheImageFullWidthFromTheTopLeftCorner(): void
    {
        $pdf = new SpyPdf();
        $data = new Data();
        $data->sets([
            'pagePadding' => 10,
            'marginTop' => 0,
            'marginBottom' => 15,
            'borderWidth' => 1,
            'incImage' => self::IMAGE,
        ]);
        $modelizer = new ImageModelizer($pdf, $data);
        $modelizer->load();
        $pdf->calls = [];

        $modelizer->toPdf();

        $pageWidth = $pdf->getPageWidth();
        $breakMargin = (new Helper())->px2mm(15);
        $this->assertSame([
            ['AddPage', []],
            // page breaks are off while the image is drawn, so it cannot spill on a new page
            ['setAutoPageBreak', [false, 0]],
            // 200x100 image: full page width, half as high
            ['Image', [self::IMAGE, 0, 0, $pageWidth, $pageWidth / 2]],
            ['setAutoPageBreak', [true, $breakMargin]],
            ['setPageMark', []],
            ['endPage', []],
        ], $pdf->calls);
        $this->assertSame(1, $pdf->getNumPages());
    }
}
