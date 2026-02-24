<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

use JDZ\Pdf\Modelizer;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * FPDI bridge
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Pdf extends Fpdi
{
	public int $izPageNoOffset = 0;
	public bool $izPageNoShow;
	public bool $izOnLeft;
	public ?Modelizer $izModel = null;
	public string $izSourcesPath;
	public string $tocFont = 'helvetica';

	public function __construct($orientation = PDF_PAGE_ORIENTATION, $unit = PDF_UNIT, $format = PDF_PAGE_FORMAT, $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
	{
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

		$this->SetCreator('Callisto Framework');
		$this->SetAuthor('iZis a Website');
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$this->setImageScale(1.25);
		$this->setJPEGQuality(100);

		$this->setLanguageArray([
			'a_meta_charset' => 'UTF-8',
			'a_meta_dir' => 'ltr',
			'a_meta_language' => 'en',
			'w_page' => 'Page',
		]);
	}

	public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
	{
		parent::AddPage($orientation, $format, $keepmargins, $tocpage);

		$this->set('izOnLeft', true);
	}

	public function PageNo()
	{
		return parent::PageNo() - $this->izPageNoOffset;
	}

	public function pageHoldsEnoughSpace($h = 0, $y = '', $addpage = true): bool
	{
		return $this->checkPageBreak($h, $y, $addpage) ? false : true;
	}

	public function Header()
	{
		if (null !== $this->izModel) {
			$this->izModel->Header();
		}
	}

	public function Footer()
	{
		if (null !== $this->izModel) {
			$this->izModel->Footer();
		}
	}

	public function get(string $k, $default = null)
	{
		return isset($this->{$k}) ? $this->{$k} : $default;
	}

	public function set(string $k, $v = null)
	{
		if ('font' === $k) {
			foreach ($v->styles as $style) {
				if ($style === null) {
					$style = '';
				}

				$this->AddFont($v->name, $style);
			}
		} else {
			$this->{$k} = $v;
		}

		return $this;
	}
}
