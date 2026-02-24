<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf\Modelizer;

use JDZ\Pdf\Modelizer;

/**
 * - PDF file includer
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class PdfModelizer extends Modelizer
{
  public function Page()
  {
    $pdf_file = $this->data->get('incPdf');

    $this->pdf->setSourceFile($pdf_file);
    $tplIdx = $this->pdf->importPage(1, 'MediaBox');
    $this->pdf->useTemplate($tplIdx);
    $this->pdf->setPageMark();
  }
}
