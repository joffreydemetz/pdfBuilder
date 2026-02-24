<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\BookToc;
use JDZ\Pdf\Helper;
use JDZ\Pdf\Pdf;
use PHPUnit\Framework\TestCase;

class BookTocTest extends TestCase
{
    private BookToc $bookToc;

    protected function setUp(): void
    {
        $this->bookToc = new BookToc();
    }

    // --- inheritance from Toc ---

    public function testExtendsToC(): void
    {
        $this->assertInstanceOf(\JDZ\Pdf\Toc::class, $this->bookToc);
    }

    public function testNewBookTocHasNoMarks(): void
    {
        $this->assertFalse($this->bookToc->hasMarks());
    }

    // --- setHelper ---

    public function testSetHelperReturnsSelf(): void
    {
        $helper = new Helper();
        $result = $this->bookToc->setHelper($helper);
        $this->assertSame($this->bookToc, $result);
    }

    // --- setPdf ---

    public function testSetPdfReturnsSelf(): void
    {
        $pdf = $this->createMock(Pdf::class);
        $result = $this->bookToc->setPdf($pdf);
        $this->assertSame($this->bookToc, $result);
    }

    // --- withPrintToc ---

    public function testWithPrintTocReturnsSelf(): void
    {
        $result = $this->bookToc->withPrintToc(true);
        $this->assertSame($this->bookToc, $result);
    }

    public function testWithPrintTocDefaultsToTrue(): void
    {
        $result = $this->bookToc->withPrintToc();
        $this->assertSame($this->bookToc, $result);
    }

    // --- toPdf ---

    public function testToPdfReturnsSelf(): void
    {
        $helper = new Helper();
        $pdf = $this->createMock(Pdf::class);

        $this->bookToc->setHelper($helper);
        $this->bookToc->setPdf($pdf);

        $result = $this->bookToc->toPdf();
        $this->assertSame($this->bookToc, $result);
    }

    public function testToPdfDoesNothingWhenPrintTocIsFalse(): void
    {
        $helper = $this->createMock(Helper::class);
        $pdf = $this->createMock(Pdf::class);

        $helper->expects($this->never())->method('exportToc');

        $this->bookToc->setHelper($helper);
        $this->bookToc->setPdf($pdf);
        $this->bookToc->withPrintToc(false);

        $this->bookToc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);

        $this->bookToc->toPdf();
    }

    public function testToPdfDoesNothingWhenNoMarks(): void
    {
        $helper = $this->createMock(Helper::class);
        $pdf = $this->createMock(Pdf::class);

        $helper->expects($this->never())->method('exportToc');

        $this->bookToc->setHelper($helper);
        $this->bookToc->setPdf($pdf);
        $this->bookToc->withPrintToc(true);

        $this->bookToc->toPdf();
    }

    public function testToPdfCallsExportTocWhenPrintTocAndHasMarks(): void
    {
        $helper = $this->createMock(Helper::class);
        $pdf = $this->createMock(Pdf::class);

        $helper->expects($this->once())
            ->method('exportToc')
            ->with($this->bookToc, $pdf);

        $this->bookToc->setHelper($helper);
        $this->bookToc->setPdf($pdf);
        $this->bookToc->withPrintToc(true);

        $this->bookToc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);

        $this->bookToc->toPdf();
    }
}
