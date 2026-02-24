<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

use JDZ\Pdf\Pdf;
use JDZ\Pdf\Book;
use JDZ\Pdf\Data;
use JDZ\Pdf\Helper;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Modelizer
{
  protected Pdf $pdf;
  protected Data $data;
  protected Helper $helper;

  protected string $name = 'blank';
  protected bool $loadWithPdfMargins = true;
  protected bool $autoPageBreak = true;
  protected bool $printHeader = false;
  protected bool $printFooter = false;
  protected array $pagePlaceholders = [];

  public function __construct(Pdf $pdf, Data $data)
  {
    $this->pdf = $pdf;
    $this->data = $data;
    $this->helper = new Helper();
  }

  public function load(): void
  {
    if (true === $this->loadWithPdfMargins) {
      $basePadding = $this->data->get('pagePadding');
      $PDF_MARGIN_TOP = $this->data->px2mm('marginTop', $basePadding);
      $PDF_MARGIN_BOTTOM = $this->data->px2mm('marginBottom', $basePadding);

      $this->pdf->SetMargins($basePadding, $PDF_MARGIN_TOP, $basePadding);
    }

    if (true === $this->autoPageBreak) {
      $this->pdf->SetAutoPageBreak(true, $PDF_MARGIN_BOTTOM);
    }

    $this->pdf->setPrintHeader($this->printHeader);
    $this->pdf->setPrintFooter($this->printFooter);
    $this->pdf->SetCellPadding(0);
    $this->pdf->SetLineWidth($this->data->getBorderWidth(0.1));

    $this->pdf->setHtmlVSpace([
      'p' => [
        0 => ['h' => 0, 'n' => 0],
        1 => ['h' => 0, 'n' => 0],
      ]
    ]);

    $pageWidth = $this->pdf->getPageWidth();
    $pageWidth -= $this->data->get('pagePadding');
    $pageWidth -= $this->data->get('pagePadding');

    $this->data->set('pageWidth', $pageWidth);
  }

  public function toPdf()
  {
    $this->loadPageDimensions();

    $this->pdf->set('izModel', $this);

    $this->pdf->AddPage();
    $this->Page();
    $this->pdf->endPage();

    if ($this->pagePlaceholders) {
      $total = count(array_values($this->pagePlaceholders));

      foreach ($this->pagePlaceholders as $placeholder) {
        $this->pdf->setPage($placeholder->page);

        $placeholder->text = $placeholder->format;
        $placeholder->text = str_replace('%page%', $placeholder->page, $placeholder->text);
        $placeholder->text = str_replace('%total%', $total, $placeholder->text);

        $this->pdf->SetTextColorArray($this->data->getColor('pageNbColor'));
        $this->pdf->SetFont($this->data->get('pageNbFont'), '', $this->helper->px2pt($this->data->get('pageNbSize')));
        $this->pdf->setX($placeholder->x);
        $this->pdf->setY($placeholder->y, false);
        $this->pdf->Cell($placeholder->w, $placeholder->h, $placeholder->text, 0, 0, 'R', true, '', 0, false, 'T', 'M');
      }

      $this->pdf->lastPage();
    }
  }

  public function Header() {}

  public function Footer() {}

  public function Page() {}

  protected function setPdfMargins($top = null): void
  {
    $basePadding = $this->data->get('pagePadding');
    $topMargin = $this->data->px2mm('marginTop', $basePadding);

    if ($top) {
      $topMargin = max($topMargin, $top);
    }

    $this->pdf->SetMargins($basePadding, $topMargin, $basePadding);
  }

  protected function setPdfPagebreak($bottom = null)
  {
    $bottomMargin = $this->data->px2mm('marginBottom', $this->data->get('pagePadding'));

    if ($bottom) {
      $bottomMargin = max($bottomMargin, $bottom);
    }

    $this->pdf->SetAutoPageBreak(true, $bottomMargin);
  }

  protected function loadPageDimensions()
  {
    $margins = $this->pdf->getMargins();

    $availableWidth  = $this->data->get('pageWidth');
    $availableHeight = $this->pdf->get('h') - $margins['top'] - $margins['bottom'];
    $offsetLeft      = $availableWidth / 2;

    $this->data->set('availableWidth', $availableWidth);
    $this->data->set('availableHeight', $availableHeight);
    $this->data->set('offsetLeft', $offsetLeft);
  }

  protected function lightColorArray(string $key, int $value)
  {
    $color = $this->data->getColorObject($key);
    $color->lighten($value);
    return $color->toArray();
  }

  protected function liToDash(string $content): string
  {
    if ($content) {
      $content = mb_ereg_replace('\s*<li[^>]?>\s*', "<p> - ", $content);
      $content = str_replace('</li>', '</p>', $content);
      $content = preg_replace("/<\/?ul>/", "", $content);
    }

    return $content;
  }
}
