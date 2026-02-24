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
 * - Full page image
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class ImageModelizer extends Modelizer
{
	public function Page()
	{
		$bMargin = $this->pdf->getBreakMargin();
		$auto_page_break = $this->pdf->getAutoPageBreak();
		$img_file = $this->data->get('incImage');

		list($width_orig, $height_orig) = \getimagesize($img_file);
		$ratio_orig = $width_orig / $height_orig;

		$imgWidth  = $this->pdf->get('w');
		$imgHeight = $imgWidth / $ratio_orig;

		$this->pdf->SetAutoPageBreak(false, 0);
		$this->pdf->Image($img_file, 0, 0, $imgWidth, $imgHeight);
		$this->pdf->SetAutoPageBreak($auto_page_break, $bMargin);
		$this->pdf->setPageMark();
	}
}
