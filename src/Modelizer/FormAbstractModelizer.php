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
 * - Methods to build forms
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class FormAbstractModelizer extends Modelizer
{
	protected function toNextRow(&$lastY, $width = null)
	{
		if ($width === null) {
			$width = $this->data->get('widthInside');
		}

		$x = $this->data->get('insidePadding') + $this->data->px2mm('insideX_Pad');
		$y = $this->pdf->GetY();
		$this->pdf->Line($x, $y, $x + $width, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('black')));
		$y = $lastY + $this->data->px2mm('rowHeight');
		$this->pdf->SetY($y, false);
		$lastY = $y;
		return $y;
	}

	protected function setTitle($title, $color = 'themeColor')
	{
		$y = $this->pdf->GetY();
		$x = $this->data->get('insidePadding') + $this->data->px2mm('insideX_Pad');

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$this->pdf->SetTextColorArray($this->data->getColor($color));
		$this->pdf->SetFont($this->data->get('fontDefault'), 'B', $this->helper->px2pt(17));
		$_w = $this->pdf->GetStringWidth($title);
		$this->pdf->Cell($_w, $this->helper->px2mm(17), $title, 0, 1);

		$y = $this->pdf->GetY() + $this->data->px2mm('yAfterTitle');
		$this->pdf->setY($y, false);
	}

	protected function setPart($key, $title = '', $subtitle = '', $subpart = 0, $color = 'themeColor')
	{
		$x = $this->data->get('insidePadding') + $this->data->px2mm('insideX_Pad');
		$y = $this->pdf->GetY();

		if ($title) {
			$this->setPartTitle($x, $y, $title, $subtitle, $subpart, $color);
			$y = $this->pdf->GetY();
		}

		$this->pdf->SetTextColorArray($this->data->getColor('black'));
		$this->pdf->SetFillColorArray($this->data->getColor('white'));
		$this->pdf->SetDrawColorArray($this->lightColorArray('black', 80));

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$method = 'setForm' . ucfirst($key);
		$this->{$method}($x, $y);
		$y = $this->pdf->GetY() + $this->data->px2mm('yAfterPart');
		$this->pdf->SetY($y);
	}

	protected function setPartTitle($x, $y, $title, $subtitle = '', $subpart = 0, $color = null)
	{
		if ($color === 'null') {
			$color = $this->data->getColor('defaultColor');
		}

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$this->pdf->SetTextColorArray($this->data->getColor($color));
		$this->pdf->SetFont($this->data->get('fontDefault'), $subpart ? '' : 'B', $this->helper->px2pt(12));
		$_w = $this->pdf->GetStringWidth($title);
		$this->pdf->Cell($_w, $this->helper->px2mm(12), $title, 0, $subtitle != '' ? 0 : 1);

		if ($subtitle != '') {
			$x += $_w;
			$x += $this->helper->px2mm(5);
			$y += $this->helper->px2mm(2);
			$this->pdf->setX($x);
			$this->pdf->setY($y, false);

			$this->pdf->SetFont($this->data->get('fontLight'), 'I', $this->helper->px2pt(10));
			$_w = $this->pdf->GetStringWidth($subtitle);
			$this->pdf->Cell($_w, $this->helper->px2mm(9), $subtitle, 0, 1);
		}

		if ($subpart > 1) {
			$y = $this->pdf->GetY();
			$y -= $this->helper->px2mm(1);
			$this->pdf->Line($x, $y, $x + $_w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor($color)));
		}

		$y = $this->pdf->GetY() + $this->data->px2mm('yAfterPartTitle');
		$this->pdf->SetY($y, false);
	}

	protected function setFormField($x, $y, $label, $fieldName, $fieldWidth, $labelWidth = false, $labelBold = false)
	{
		$this->pdf->setX($x);
		$this->pdf->setY($y, false);
		$this->setFormFieldLabel($label, $labelWidth, $labelBold);
		$this->setFormFieldInput($x + $labelWidth, $y, $fieldName, $fieldWidth - $labelWidth);
	}

	protected function setFormFieldFulllength($x, $y, $label, $fieldName, $fieldWidth, $numRows = 1)
	{
		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$lastY = $y;

		$labelWidth = false;
		$this->setFormFieldLabel($label, $labelWidth, false, false);
		$y = $this->toNextRow($lastY);
		$y -= $this->helper->px2mm(3);
		for ($i = 1; $i <= $numRows; $i++) {
			$lastY = $y;
			$this->setFormFieldInput($x, $y, $fieldName . $i, $fieldWidth);
			$y = $this->pdf->GetY();
			if ($numRows > 1) {
				$y -= $this->helper->px2mm(4);
			}
		}
		if ($numRows === 1) {
			$y -= $this->helper->px2mm(2);
		}
		$this->pdf->SetY($y, false);
		$y = $this->toNextRow($lastY);
	}

	protected function setFormFieldLabel($label, &$labelWidth, $labelBold = false, $inline = true)
	{
		$this->pdf->SetTextColorArray($this->data->getColor('black'));
		$this->pdf->SetFont($this->data->get('fontDefault'), $labelBold ? 'B' : '', $this->data->px2pt('labelFsize'));
		if ($labelWidth === false) {
			$labelWidth = $this->pdf->GetStringWidth($label) + $this->helper->px2mm(3);
		}
		$this->pdf->Cell($labelWidth, $this->helper->px2mm($this->data->get('labelFsize')), $label);
		if ($inline === false) {
			$labelWidth = 0;
			$this->pdf->Ln($this->data->px2mm('fieldHeight') - $this->helper->px2mm(1));
		}
	}

	protected function setFormFieldInput($x, $y, $fieldName, $fieldWidth)
	{
		$this->pdf->SetFont($this->data->get('fontDefault'), '', $this->data->px2pt('inputFsize'));
		$this->pdf->SetTextColorArray($this->data->getColor('black'));
		$this->pdf->SetFillColorArray($this->data->getColor('fieldBgColor'));
		$this->pdf->TextField($fieldName, $fieldWidth, $this->data->px2mm('inputHeight'), [], [], $x, $y, false);
		$this->pdf->Ln($this->data->px2mm('fieldHeight'));
	}

	protected function setFormFieldCheckbox($x, $y, $label, $fieldName)
	{
		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$this->pdf->SetFont($this->data->get('fontDefault'), '', $this->data->px2pt('inputFsize'));
		$this->pdf->SetTextColorArray($this->data->getColor('black'));
		$_w = $this->pdf->GetStringWidth($label) + $this->helper->px2mm(3);
		$this->pdf->Cell($_w, $this->data->px2mm('checkboxSize'), $label, 0, 0);

		$this->pdf->SetFillColorArray($this->data->getColor('white'));
		$this->pdf->SetFont($this->data->get('fontDefault'), '', $this->data->px2pt('checkboxFsize'));
		$this->pdf->CheckBox($fieldName, $this->data->px2mm('checkboxSize'), false, [], [], 'OUI', $x + $_w, $y, false);
		$this->pdf->SetFont($this->data->get('fontDefault'), '', $this->data->px2pt('inputFsize'));
	}

	protected function setFormFieldSignature($x, $y, $w, $h, $title)
	{
		$border       = $this->helper->px2mm(1);
		$headerHeight = $this->helper->px2mm(10);
		$insideWidth  = $w - $border * 2;
		$boxWidth     = $insideWidth;
		$boxHeight    = $h - $border * 3 - $headerHeight;

		$this->pdf->SetCellPadding(0);
		$this->pdf->SetDrawColorArray($this->data->getColor('fieldStrokeColor'));
		$this->pdf->SetFillColorArray($this->data->getColor('formBgColor'));
		$this->pdf->SetTextColorArray($this->data->getColor('black'));
		$this->pdf->SetFont($this->data->get('fontLight'), 'I', $this->helper->px2pt(9));

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$this->pdf->Cell($w, $h, '', array(
			'TRBL' => $this->helper->border($this->data->getBorderWidth(), $this->data->getColor('fieldStrokeColor'))
		), 0, '', true);

		$x += $border;
		$y += $border;

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);
		$this->pdf->SetFillColorArray($this->data->getColor('white'));
		$this->pdf->Cell($insideWidth, $headerHeight, 'Cachet de l’entreprise et signature', 0, 1, 'C', true);

		$y += $headerHeight;
		$y += $border;

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);
		$this->pdf->SetFillColorArray($this->data->getColor('fieldBgColor'));
		$this->pdf->Cell($boxWidth, $boxHeight, '', 0, 1, '', true);
	}

	protected function setFormFieldFaitA($x, $y, $w)
	{
		$lastY = $y;
		$labelWidth = false;
		$wFaitA = $w * 0.54;
		$wLe    = $w * 0.44;
		$xLe    = $x + $wFaitA + $w * 0.02; // margin between label and field

		$this->setFormField($x, $y, 'Fait à  ', 'faita', $wFaitA, $labelWidth);
		$this->setFormField($xLe, $y, ', le  ', 'faitle', $wLe, false);
		$y = $this->toNextRow($lastY, $w);
		$this->pdf->setY($y, false);
	}

	protected function setFormFieldCheckboxText($x, $y, $w, $fieldName, $html, $big = true)
	{
		$y += $this->helper->px2mm(4);

		$this->pdf->setX($x);
		$this->pdf->setY($y, false);

		$fsizeCheckbox = $big ? $this->data->px2pt('bigcheckboxFsize') : $this->data->px2pt('checkboxFsize');
		$sizeCheckbox  = $big ? $this->data->px2mm('bigcheckboxSize') : $this->data->px2mm('checkboxSize');
		$textOffset    = $sizeCheckbox + $this->helper->px2mm(2);

		$this->pdf->SetFont($this->data->get('fontDefault'), '', $fsizeCheckbox);
		$this->pdf->CheckBox($fieldName, $sizeCheckbox, false, [], [], 'OUI', $x, $y, false);

		$this->pdf->SetFont($this->data->get('fontDefault'), '', $this->data->px2pt('labelFsize'));
		$this->pdf->writeHtmlCell($w - $textOffset, 0, $x + $textOffset, $y + $this->helper->px2mm(2), $html, 0, 1);
	}
}
