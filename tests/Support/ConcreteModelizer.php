<?php

namespace JDZ\Pdf\Tests\Support;

use JDZ\Pdf\Data;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Pdf;

/**
 * Modelizer that counts the headers/footers it is asked to draw and exposes the
 * protected helpers.
 */
class ConcreteModelizer extends Modelizer
{
    public int $headers = 0;
    public int $footers = 0;

    public function __construct(Pdf $pdf, Data $data, bool $printHeaderAndFooter = false)
    {
        parent::__construct($pdf, $data);

        $this->printHeader = $printHeaderAndFooter;
        $this->printFooter = $printHeaderAndFooter;
    }

    public function Header(): void
    {
        $this->headers++;
    }

    public function Footer(): void
    {
        $this->footers++;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function exposeLiToDash(string $content): string
    {
        return $this->liToDash($content);
    }

    public function exposeLightColorArray(string $key, int $value): array
    {
        return $this->lightColorArray($key, $value);
    }

    public function exposeSetPdfMargins($top = null): void
    {
        $this->setPdfMargins($top);
    }

    public function exposeSetPdfPagebreak($bottom = null): void
    {
        $this->setPdfPagebreak($bottom);
    }
}
