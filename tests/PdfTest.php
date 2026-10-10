<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Data;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Tests\Support\ConcreteModelizer;
use JDZ\Pdf\Tests\Support\SpyPdf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
    private Pdf $pdf;

    protected function setUp(): void
    {
        $this->pdf = new Pdf();
    }

    public function testGetReturnsDefaultWhenNotSet(): void
    {
        $this->assertSame('fallback', $this->pdf->get('nonExistentProp', 'fallback'));
    }

    public function testPageNoWithOffset(): void
    {
        $this->pdf->AddPage();
        $this->pdf->izPageNoOffset = 0;
        $pageWithoutOffset = $this->pdf->PageNo();

        $this->pdf->izPageNoOffset = 2;
        $pageWithOffset = $this->pdf->PageNo();

        $this->assertSame($pageWithoutOffset - 2, $pageWithOffset);
    }

    public function testAddPageSetsIzOnLeftTrue(): void
    {
        $this->pdf->set('izOnLeft', false);
        $this->pdf->AddPage();
        $this->assertTrue($this->pdf->get('izOnLeft'));
    }

    public function testPageHoldsEnoughSpaceSmallHeight(): void
    {
        $this->pdf->AddPage();
        // A very small height should fit on a fresh page
        $this->assertTrue($this->pdf->pageHoldsEnoughSpace(1));
    }

    public static function overflowingHeights(): array
    {
        return [
            'only checks' => [false, 1],
            'breaks to a new page' => [true, 2],
        ];
    }

    #[DataProvider('overflowingHeights')]
    public function testPageHoldsEnoughSpaceIsFalseForAHeightTallerThanThePage(bool $addPage, int $pagesAfter): void
    {
        $this->pdf->AddPage();

        $this->assertFalse($this->pdf->pageHoldsEnoughSpace(1000, '', $addPage));
        $this->assertSame($pagesAfter, $this->pdf->getNumPages());
    }

    public function testSettingAFontRegistersEachOfItsStyles(): void
    {
        $pdf = new SpyPdf();

        $pdf->set('font', (object)['name' => 'helvetica', 'styles' => [null, 'B', 'I']]);

        $this->assertSame([['helvetica', ''], ['helvetica', 'B'], ['helvetica', 'I']], $pdf->argsOf('AddFont'));
        $this->assertNull($pdf->get('font'));
    }

    public function testHeaderAndFooterAreDrawnByTheModel(): void
    {
        $model = new ConcreteModelizer($this->pdf, new Data());
        $this->pdf->set('izModel', $model);

        $this->pdf->AddPage();
        $this->assertSame([1, 0], [$model->headers, $model->footers]);

        $this->pdf->endPage();
        $this->assertSame([1, 1], [$model->headers, $model->footers]);
    }
}
