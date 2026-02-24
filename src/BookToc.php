<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class BookToc extends Toc
{
  protected Helper $helper;
  protected Pdf $pdf;
  protected bool $printToc = false;

  public function setHelper(Helper $helper): static
  {
    $this->helper = $helper;
    return $this;
  }

  public function setPdf(Pdf $pdf): static
  {
    $this->pdf = $pdf;
    return $this;
  }

  public function withPrintToc(bool $printToc = true): static
  {
    $this->printToc = $printToc;
    return $this;
  }

  public function toPdf(): static
  {
    if (true === $this->printToc && $this->hasMarks()) {
      $this->helper->exportToc($this, $this->pdf);
    }

    return $this;
  }
}
