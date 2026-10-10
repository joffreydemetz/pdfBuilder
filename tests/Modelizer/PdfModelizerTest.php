<?php

namespace JDZ\Pdf\Tests\Modelizer;

use JDZ\Pdf\Data;
use JDZ\Pdf\Modelizer\PdfModelizer;
use JDZ\Pdf\Tests\Support\SpyPdf;
use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfModelizerTest extends TestCase
{
    /** a single 100 x 50 mm landscape page */
    private const INCLUDED_PDF = __DIR__ . '/../fixtures/one-page.pdf';

    private string $outputDir;
    private SpyPdf $pdf;
    private PdfModelizer $modelizer;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/jdz-pdfbuilder-' . bin2hex(random_bytes(4));
        mkdir($this->outputDir);

        $this->pdf = new SpyPdf();
        $data = new Data();
        $data->sets([
            'pagePadding' => 10,
            'marginTop' => 0,
            'marginBottom' => 0,
            'borderWidth' => 1,
            'incPdf' => self::INCLUDED_PDF,
        ]);
        $this->modelizer = new PdfModelizer($this->pdf, $data);
        $this->modelizer->load();
        $this->pdf->calls = [];
    }

    protected function tearDown(): void
    {
        foreach (glob($this->outputDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->outputDir);
    }

    public function testToPdfPlacesTheFirstPageOfTheIncludedPdf(): void
    {
        $this->modelizer->toPdf();

        $this->assertSame(
            ['AddPage', 'setSourceFile', 'importPage', 'useTemplate', 'setPageMark', 'endPage'],
            $this->pdf->methods()
        );
        $this->assertSame([[self::INCLUDED_PDF]], $this->pdf->argsOf('setSourceFile'));
        $this->assertSame([[1, 'MediaBox']], $this->pdf->argsOf('importPage'));

        $template = $this->pdf->argsOf('useTemplate');
        $this->assertCount(1, $template);
        $size = $this->pdf->getTemplateSize($template[0][0]);
        $this->assertEqualsWithDelta([100, 50], [$size['width'], $size['height']], 1e-6);
    }

    public function testTheWrittenDocumentHoldsOnePageOfTheHostFormat(): void
    {
        $this->modelizer->toPdf();
        $file = $this->outputDir . '/out.pdf';

        $this->pdf->Output($file, 'F');

        // read from memory: a reader opened on the path would keep the file locked on Windows
        $reader = new Fpdi();
        $this->assertSame(1, $reader->setSourceFile(StreamReader::createByString(file_get_contents($file))));
        $size = $reader->getTemplateSize($reader->importPage(1, 'MediaBox'));
        $this->assertEqualsWithDelta([210, 297], [$size['width'], $size['height']], 0.01);
    }
}
