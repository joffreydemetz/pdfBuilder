<?php

namespace JDZ\Pdf\Tests\Support;

use JDZ\Pdf\Data;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Pdf;

/**
 * Draws $pages pages and leaves $perPage page-number placeholders on each
 * one, for Modelizer::toPdf() to fill once the page count is known.
 */
class PagedModelizer extends Modelizer
{
    public function __construct(Pdf $pdf, Data $data, private int $pages, private string $format, private int $perPage = 1)
    {
        parent::__construct($pdf, $data);
    }

    public function Page(): void
    {
        for ($i = 1; $i <= $this->pages; $i++) {
            if ($i > 1) {
                $this->pdf->AddPage();
            }

            for ($p = 0; $p < $this->perPage; $p++) {
                $this->pagePlaceholders[] = (object)[
                    'page' => $this->pdf->getPage(),
                    'format' => $this->format,
                    'x' => 150,
                    'y' => 280 - 10 * $p,
                    'w' => 40,
                    'h' => 5,
                ];
            }
        }
    }
}
