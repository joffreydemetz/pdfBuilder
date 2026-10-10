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

    public function testToPdfDoesNothingWhenPrintTocIsFalse(): void
    {
        $helper = $this->createMock(Helper::class);
        $helper->expects($this->never())->method('exportToc');

        $this->bookToc->setHelper($helper);
        $this->bookToc->setPdf($this->createStub(Pdf::class));
        $this->bookToc->withPrintToc(false);

        $this->bookToc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);

        $this->bookToc->toPdf();
    }

    public function testToPdfDoesNothingWhenNoMarks(): void
    {
        $helper = $this->createMock(Helper::class);
        $helper->expects($this->never())->method('exportToc');

        $this->bookToc->setHelper($helper);
        $this->bookToc->setPdf($this->createStub(Pdf::class));
        $this->bookToc->withPrintToc(true);

        $this->bookToc->toPdf();
    }

    public function testToPdfCallsExportTocWhenPrintTocAndHasMarks(): void
    {
        $helper = $this->createMock(Helper::class);
        $pdf = $this->createStub(Pdf::class);

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
