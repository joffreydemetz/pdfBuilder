<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Data;
use JDZ\Pdf\Helper;
use JDZ\Pdf\Tests\Support\ConcreteModelizer;
use JDZ\Pdf\Tests\Support\PagedModelizer;
use JDZ\Pdf\Tests\Support\SpyPdf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ModelizerTest extends TestCase
{
    private SpyPdf $pdf;
    private Data $data;
    private Helper $helper;

    protected function setUp(): void
    {
        $this->pdf = new SpyPdf();
        $this->data = new Data();
        $this->helper = new Helper();

        $this->data->sets([
            'pagePadding' => 10,   // mm
            'marginTop' => 20,     // px
            'marginBottom' => 15,  // px
            'borderWidth' => 1,
            'pageNbFont' => 'courier',
            'pageNbSize' => 8,
            'colors' => [
                'black' => '#000000',
                'white' => '#FFFFFF',
                'themeColor' => '#336699',
                'pageNbColor' => '#336699',
            ],
            'fonts' => [
                'default' => 'helvetica',
            ],
        ]);
    }

    private function createModelizer(bool $printHeaderAndFooter = false): ConcreteModelizer
    {
        return new ConcreteModelizer($this->pdf, $this->data, $printHeaderAndFooter);
    }

    public function testDefaultName(): void
    {
        $modelizer = $this->createModelizer();
        $this->assertSame('blank', $modelizer->getName());
    }

    // --- liToDash ---

    public function testLiToDashConvertsListItems(): void
    {
        $modelizer = $this->createModelizer();

        $this->assertSame(
            '<p> - Item 1</p><p> - Item 2</p>',
            $modelizer->exposeLiToDash("<ul>\n  <li>Item 1</li>\n  <li>Item 2</li></ul>")
        );
    }

    public function testLiToDashEmptyString(): void
    {
        $modelizer = $this->createModelizer();
        $this->assertSame('', $modelizer->exposeLiToDash(''));
    }

    public function testLiToDashNoListItems(): void
    {
        $modelizer = $this->createModelizer();
        $result = $modelizer->exposeLiToDash('<p>Hello</p>');
        $this->assertSame('<p>Hello</p>', $result);
    }

    // --- lightColorArray ---

    public function testLightColorArrayLightensACopyOfTheStoredColor(): void
    {
        $modelizer = $this->createModelizer();

        $this->assertSame([85, 170, 255], $modelizer->exposeLightColorArray('themeColor', 50));
        $this->assertSame([51, 102, 153], $this->data->getColor('themeColor'));
    }

    // --- load ---

    public function testLoadAppliesTheConfiguredMarginsAndLineWidth(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->load();

        $margins = $this->pdf->getMargins();
        $this->assertSame(
            [10, 10, $this->helper->px2mm(20), $this->helper->px2mm(15)],
            [$margins['left'], $margins['right'], $margins['top'], $margins['bottom']]
        );
        $this->assertTrue($this->pdf->getAutoPageBreak());
        $this->assertSame(['T' => 0, 'R' => 0, 'B' => 0, 'L' => 0], $this->pdf->getCellPaddings());
        $this->assertSame(0.1, $this->pdf->GetLineWidth());
        $this->assertSame($this->pdf->getPageWidth() - 20, $this->data->get('pageWidth'));
    }

    public static function headerAndFooterSettings(): array
    {
        return [
            'printed' => [true, [1, 1]],
            'not printed' => [false, [0, 0]],
        ];
    }

    #[DataProvider('headerAndFooterSettings')]
    public function testToPdfDrawsItsOwnHeaderAndFooterWhenLoadedToPrintThem(bool $print, array $drawn): void
    {
        $modelizer = $this->createModelizer($print);
        $modelizer->load();

        $modelizer->toPdf();

        $this->assertSame($modelizer, $this->pdf->izModel);
        $this->assertSame($drawn, [$modelizer->headers, $modelizer->footers]);
    }

    // --- toPdf ---

    public function testToPdfWithoutPlaceholdersAddsAndClosesOnePage(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->load();
        $this->pdf->calls = [];

        $modelizer->toPdf();

        $this->assertSame(['AddPage', 'endPage'], $this->pdf->methods());
        $this->assertSame(1, $this->pdf->getNumPages());
    }

    public function testToPdfRecordsTheAreaLeftInsideTheMargins(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->load();

        $modelizer->toPdf();

        $pageWidth = $this->pdf->getPageWidth() - 20;
        $this->assertSame($pageWidth, $this->data->get('availableWidth'));
        $this->assertSame(
            $this->pdf->getPageHeight() - $this->helper->px2mm(20) - $this->helper->px2mm(15),
            $this->data->get('availableHeight')
        );
        $this->assertSame($pageWidth / 2, $this->data->get('offsetLeft'));
    }

    public function testToPdfFillsThePageNumberPlaceholderOfEveryPage(): void
    {
        $modelizer = new PagedModelizer($this->pdf, $this->data, 3, 'Page %page% of %total%');
        $modelizer->load();
        $this->pdf->calls = [];

        $modelizer->toPdf();

        $this->assertSame([[1], [2], [3]], $this->pdf->argsOf('setPage'));
        $this->assertSame([
            [40, 5, 'Page 1 of 3', 0, 0, 'R', true, '', 0, false, 'T', 'M'],
            [40, 5, 'Page 2 of 3', 0, 0, 'R', true, '', 0, false, 'T', 'M'],
            [40, 5, 'Page 3 of 3', 0, 0, 'R', true, '', 0, false, 'T', 'M'],
        ], $this->pdf->argsOf('Cell'));
        $this->assertSame(array_fill(0, 3, [[51, 102, 153]]), $this->pdf->argsOf('setTextColorArray'));
        $this->assertSame(array_fill(0, 3, ['courier', '', 8.0]), $this->pdf->argsOf('setFont'));
        $this->assertSame(array_fill(0, 3, [150]), $this->pdf->argsOf('setX'));
        $this->assertSame(array_fill(0, 3, [280, false]), $this->pdf->argsOf('setY'));
        $this->assertSame('lastPage', $this->pdf->methods()[count($this->pdf->calls) - 1]);
        $this->assertSame(3, $this->pdf->getPage());
    }

    /** 1.1.2: pageNbSize is a font size, read as a float (7.5px stays 7.5pt). */
    public function testToPdfReadsAFractionalPageNumberSize(): void
    {
        $this->data->sets(['pageNbSize' => '7.5']);
        $modelizer = new PagedModelizer($this->pdf, $this->data, 1, '%page%');
        $modelizer->load();
        $this->pdf->calls = [];

        $modelizer->toPdf();

        $this->assertSame([['courier', '', 7.5]], $this->pdf->argsOf('setFont'));
    }

    // --- setPdfMargins / setPdfPagebreak ---

    public static function marginOverrides(): array
    {
        $configured = (new Helper())->px2mm(20);

        return [
            'no override keeps the configured margin' => [null, $configured],
            'a smaller override is ignored' => [5, $configured],
            'a larger override wins' => [30, 30],
        ];
    }

    #[DataProvider('marginOverrides')]
    public function testSetPdfMarginsKeepsTheLargerTopMargin(?int $top, int|float $expectedTop): void
    {
        $this->pdf->AddPage();

        $this->createModelizer()->exposeSetPdfMargins($top);

        $margins = $this->pdf->getMargins();
        $this->assertSame([10, $expectedTop, 10], [$margins['left'], $margins['top'], $margins['right']]);
    }

    public static function pagebreakOverrides(): array
    {
        $configured = (new Helper())->px2mm(15);

        return [
            'no override keeps the configured margin' => [null, $configured],
            'a smaller override is ignored' => [2, $configured],
            'a larger override wins' => [25, 25],
        ];
    }

    #[DataProvider('pagebreakOverrides')]
    public function testSetPdfPagebreakKeepsTheLargerBottomMargin(?int $bottom, int|float $expectedBottom): void
    {
        $this->pdf->AddPage();
        $this->pdf->SetAutoPageBreak(false);

        $this->createModelizer()->exposeSetPdfPagebreak($bottom);

        $this->assertTrue($this->pdf->getAutoPageBreak());
        $this->assertSame($expectedBottom, $this->pdf->getBreakMargin());
    }

    /**
     * Without the margins step there was no bottom margin for the page break: an
     * "Undefined variable" warning and a null break margin. The PDF keeps its own.
     */
    public function testAutoPageBreakWithoutTheMarginsStepKeepsThePdfBreakMargin(): void
    {
        $modelizer = new class($this->pdf, $this->data) extends ConcreteModelizer {
            protected bool $loadWithPdfMargins = false;
        };
        $breakMargin = $this->pdf->getBreakMargin();

        $modelizer->load();

        $this->assertTrue($this->pdf->getAutoPageBreak());
        $this->assertSame($breakMargin, $this->pdf->getBreakMargin());
    }

    /**
     * The pattern was <li[^>]?>: an <li> with attributes was left as is.
     */
    public function testLiToDashConvertsListItemsWithAttributes(): void
    {
        $this->assertSame(
            '<p> - Item 1</p><p> - Item 2</p>',
            $this->createModelizer()->exposeLiToDash('<ul><li class="a">Item 1</li><li id="b" style="color:red">Item 2</li></ul>')
        );
    }
}
