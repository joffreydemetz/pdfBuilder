<?php

namespace JDZ\Pdf\Tests\Modelizer;

use JDZ\Pdf\Data;
use JDZ\Pdf\Helper;
use JDZ\Pdf\Tests\Support\FormModelizerProbe;
use JDZ\Pdf\Tests\Support\SpyPdf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FormAbstractModelizerTest extends TestCase
{
    private const THEME = [51, 102, 153];
    private const BLACK = [0, 0, 0];
    private const WHITE = [255, 255, 255];
    private const FIELD_BG = [235, 238, 248];
    private const FIELD_STROKE = [228, 99, 37];
    private const FORM_BG = [245, 245, 245];
    private const DELTA = 1e-9;

    private SpyPdf $pdf;
    private Helper $helper;
    private FormModelizerProbe $form;
    /** left edge of the form content: insidePadding (mm) + insideX_Pad (px) */
    private float $x0;

    protected function setUp(): void
    {
        $this->pdf = new SpyPdf();
        $this->pdf->AddPage();
        $this->helper = new Helper();

        $data = new Data();
        $data->sets([
            'insidePadding' => 10,
            'insideX_Pad' => 5,
            'widthInside' => 120,
            'rowHeight' => 20,
            'yAfterTitle' => 3,
            'yAfterPart' => 5,
            'yAfterPartTitle' => 3,
            'labelFsize' => 9,
            'inputFsize' => 8,
            'fieldHeight' => 18,
            'inputHeight' => 14,
            'checkboxSize' => 10,
            'checkboxFsize' => 7,
            'bigcheckboxSize' => 14,
            'bigcheckboxFsize' => 12,
            'fontDefault' => 'helvetica',
            'fontLight' => 'times',
            'borderWidth' => 1,
            'colors' => [
                'black' => '#000000',
                'white' => '#FFFFFF',
                'themeColor' => '#336699',
                'fieldBgColor' => self::FIELD_BG,
                'fieldStrokeColor' => self::FIELD_STROKE,
                'formBgColor' => self::FORM_BG,
            ],
        ]);

        $this->form = new FormModelizerProbe($this->pdf, $data);
        $this->x0 = 10 + $this->mm(5);
    }

    private function mm(int|float $px): float
    {
        return $this->helper->px2mm($px);
    }

    /** starts the drawing at $y with an empty call log */
    private function startAt(int|float $y): void
    {
        $this->pdf->SetY($y);
        $this->pdf->calls = [];
    }

    private function hairline(array $color): array
    {
        return $this->helper->border(0.1, $color);
    }

    // --- toNextRow ---

    public static function rowWidths(): array
    {
        return [
            'default: widthInside' => [null, 120],
            'given width' => [60, 60],
        ];
    }

    #[DataProvider('rowWidths')]
    public function testToNextRowRulesTheCurrentLineAndMovesOneRowBelowTheLastOne(?int $width, int $ruled): void
    {
        $this->startAt(50);
        $lastY = 40.0;

        $y = $this->form->exposeToNextRow($lastY, $width);

        $next = 40.0 + $this->mm(20);
        $this->assertSame([$next, $next, $next], [$y, $lastY, $this->pdf->GetY()]);
        $this->assertSame(
            [[$this->x0, 50.0, $this->x0 + $ruled, 50.0, $this->hairline(self::BLACK)]],
            $this->pdf->argsOf('Line')
        );
    }

    // --- setTitle ---

    public function testSetTitleWritesABoldThemedTitleThenSkipsYAfterTitle(): void
    {
        $this->startAt(30);

        $this->form->call('setTitle', 'Inscription');

        $this->assertSame([[$this->x0]], $this->pdf->argsOf('setX'));
        $this->assertSame([[self::THEME]], $this->pdf->argsOf('setTextColorArray'));
        $this->assertSame([['helvetica', 'B', 17.0]], $this->pdf->argsOf('setFont'));
        $this->assertSame(
            [[$this->pdf->GetStringWidth('Inscription', 'helvetica', 'B', 17), $this->mm(17), 'Inscription', 0, 1]],
            $this->pdf->argsOf('Cell')
        );
        $this->assertEqualsWithDelta(30 + $this->pdf->getLastH() + $this->mm(3), $this->pdf->GetY(), self::DELTA);
    }

    // --- setPartTitle ---

    public function testSetPartTitleWritesABoldTitleOnItsOwnLine(): void
    {
        $this->startAt(40);

        $this->form->call('setPartTitle', $this->x0, 40, 'Stagiaire', '', 0, 'themeColor');

        $this->assertSame([['helvetica', 'B', 12.0]], $this->pdf->argsOf('setFont'));
        $this->assertSame(
            [[$this->pdf->GetStringWidth('Stagiaire', 'helvetica', 'B', 12), $this->mm(12), 'Stagiaire', 0, 1]],
            $this->pdf->argsOf('Cell')
        );
        $this->assertSame([], $this->pdf->argsOf('Line'));
        $this->assertEqualsWithDelta(40 + $this->pdf->getLastH() + $this->mm(3), $this->pdf->GetY(), self::DELTA);
    }

    public function testSetPartTitleOfASubPartAddsAnItalicSubtitleAndUnderlinesIt(): void
    {
        $this->startAt(40);

        $this->form->call('setPartTitle', $this->x0, 40, 'Stagiaire', 'à remplir', 2, 'themeColor');

        $titleWidth = $this->pdf->GetStringWidth('Stagiaire', 'helvetica', '', 12);
        $subtitleWidth = $this->pdf->GetStringWidth('à remplir', 'times', 'I', 10);
        $subtitleX = $this->x0 + $titleWidth + $this->mm(5);

        $this->assertSame([['helvetica', '', 12.0], ['times', 'I', 10.0]], $this->pdf->argsOf('setFont'));
        $this->assertSame([
            [$titleWidth, $this->mm(12), 'Stagiaire', 0, 0],
            [$subtitleWidth, $this->mm(9), 'à remplir', 0, 1],
        ], $this->pdf->argsOf('Cell'));
        $this->assertSame([[$this->x0], [$subtitleX]], $this->pdf->argsOf('setX'));

        $line = $this->pdf->argsOf('Line');
        $this->assertCount(1, $line);
        [$x1, $y1, $x2, $y2, $style] = $line[0];
        $this->assertSame([$subtitleX, $subtitleX + $subtitleWidth], [$x1, $x2]);
        $this->assertSame($y1, $y2);
        $this->assertEqualsWithDelta(40 + $this->mm(2) + $this->pdf->getLastH() - $this->mm(1), $y1, self::DELTA);
        $this->assertSame($this->hairline(self::THEME), $style);
    }

    // --- setPart ---

    public function testSetPartWithATitleDrawsItThenTheMatchingSetFormBodyBelow(): void
    {
        $this->startAt(60);

        $this->form->call('setPart', 'sample', 'Entreprise');

        $this->assertSame('Entreprise', $this->pdf->argsOf('Cell')[0][2]);
        $this->assertCount(1, $this->form->sampleParts);
        [$x, $y] = $this->form->sampleParts[0];
        $this->assertSame($this->x0, $x);
        $this->assertGreaterThan(60, $y);
        $this->assertSame([self::THEME, self::BLACK], array_merge(...$this->pdf->argsOf('setTextColorArray')));
        $this->assertSame([[self::WHITE]], $this->pdf->argsOf('setFillColorArray'));
        $this->assertSame([[[204, 204, 204]]], $this->pdf->argsOf('setDrawColorArray'), 'black lightened by 80');
        // the sample body is a 10mm cell
        $this->assertEqualsWithDelta($y + 10 + $this->mm(5), $this->pdf->GetY(), self::DELTA);
    }

    public function testSetPartWithoutATitleStartsTheBodyAtTheCurrentLine(): void
    {
        $this->startAt(60);

        $this->form->call('setPart', 'sample');

        $this->assertSame([[$this->x0, 60.0]], $this->form->sampleParts);
        $this->assertSame([[50, 10, 'sample body', 0, 1]], $this->pdf->argsOf('Cell'));
        $this->assertEqualsWithDelta(60 + 10 + $this->mm(5), $this->pdf->GetY(), self::DELTA);
    }

    // --- setFormField ---

    public function testSetFormFieldPutsTheInputRightAfterItsMeasuredLabel(): void
    {
        $this->startAt(80);

        $this->form->call('setFormField', $this->x0, 80, 'Nom', 'nom', 100);

        $labelWidth = $this->pdf->GetStringWidth('Nom', 'helvetica', '', 9) + $this->mm(3);
        $this->assertSame([['helvetica', '', 9.0], ['helvetica', '', 8.0]], $this->pdf->argsOf('setFont'));
        $this->assertSame([[$labelWidth, $this->mm(9), 'Nom']], $this->pdf->argsOf('Cell'));
        $this->assertSame([[self::FIELD_BG]], $this->pdf->argsOf('setFillColorArray'));
        $this->assertSame(
            [['nom', 100 - $labelWidth, $this->mm(14), [], [], $this->x0 + $labelWidth, 80, false]],
            $this->pdf->argsOf('TextField')
        );
        $this->assertSame([[$this->mm(18)]], $this->pdf->argsOf('Ln'));
    }

    public function testSetFormFieldWithAFixedBoldLabel(): void
    {
        $this->startAt(80);

        $this->form->call('setFormField', $this->x0, 80, 'Email', 'email', 100, 30, true);

        $this->assertSame(['helvetica', 'B', 9.0], $this->pdf->argsOf('setFont')[0]);
        $this->assertSame([[30, $this->mm(9), 'Email']], $this->pdf->argsOf('Cell'));
        $this->assertSame(
            [['email', 70, $this->mm(14), [], [], $this->x0 + 30, 80, false]],
            $this->pdf->argsOf('TextField')
        );
    }

    // --- setFormFieldFulllength ---

    public static function rowCounts(): array
    {
        return [
            'one row' => [1, ['notes1']],
            'three rows' => [3, ['notes1', 'notes2', 'notes3']],
        ];
    }

    #[DataProvider('rowCounts')]
    public function testSetFormFieldFulllengthStacksNumberedFullWidthInputsUnderTheLabel(int $rows, array $names): void
    {
        $this->startAt(100);

        $this->form->call('setFormFieldFulllength', $this->x0, 100, 'Remarques', 'notes', 150, $rows);

        $this->assertSame('Remarques', $this->pdf->argsOf('Cell')[0][2]);
        $inputs = $this->pdf->argsOf('TextField');
        $this->assertSame($names, array_column($inputs, 0));
        $this->assertSame(array_fill(0, $rows, 150), array_column($inputs, 1));
        $this->assertSame(array_fill(0, $rows, $this->x0), array_column($inputs, 5));

        // first input one row (less 3px) below the label, each next one lower down
        $ys = array_column($inputs, 6);
        $this->assertEqualsWithDelta(100 + $this->mm(20) - $this->mm(3), $ys[0], self::DELTA);
        for ($i = 1; $i < $rows; $i++) {
            $this->assertGreaterThan($ys[$i - 1], $ys[$i]);
        }
        // a rule under the label and one under the inputs, then one row below the last input
        $this->assertCount(2, $this->pdf->argsOf('Line'));
        $this->assertEqualsWithDelta(end($ys) + $this->mm(20), $this->pdf->GetY(), self::DELTA);
    }

    // --- setFormFieldCheckbox ---

    public function testSetFormFieldCheckboxPutsTheBoxAfterItsLabel(): void
    {
        $this->startAt(120);

        $this->form->call('setFormFieldCheckbox', $this->x0, 120, 'J’accepte', 'cgv');

        $labelWidth = $this->pdf->GetStringWidth('J’accepte', 'helvetica', '', 8) + $this->mm(3);
        $this->assertSame(
            [['helvetica', '', 8.0], ['helvetica', '', 7.0], ['helvetica', '', 8.0]],
            $this->pdf->argsOf('setFont')
        );
        $this->assertSame([[$labelWidth, $this->mm(10), 'J’accepte', 0, 0]], $this->pdf->argsOf('Cell'));
        $this->assertSame([[self::WHITE]], $this->pdf->argsOf('setFillColorArray'));
        $this->assertSame(
            [['cgv', $this->mm(10), false, [], [], 'OUI', $this->x0 + $labelWidth, 120, false]],
            $this->pdf->argsOf('CheckBox')
        );
    }

    // --- setFormFieldCheckboxText ---

    public static function checkboxSizes(): array
    {
        return [
            'big box' => [true, 14, 12.0],
            'small box' => [false, 10, 7.0],
        ];
    }

    #[DataProvider('checkboxSizes')]
    public function testSetFormFieldCheckboxTextWritesTheHtmlBesideTheBox(bool $big, int $sizePx, float $fontSize): void
    {
        $this->startAt(140);

        $this->form->call('setFormFieldCheckboxText', $this->x0, 140, 150, 'optin', '<p>Oui</p>', $big);

        $y = 140 + $this->mm(4);
        $size = $this->mm($sizePx);
        $offset = $size + $this->mm(2);
        $this->assertSame([['helvetica', '', $fontSize], ['helvetica', '', 9.0]], $this->pdf->argsOf('setFont'));
        $this->assertSame(
            [['optin', $size, false, [], [], 'OUI', $this->x0, $y, false]],
            $this->pdf->argsOf('CheckBox')
        );
        $this->assertSame(
            [[150 - $offset, 0, $this->x0 + $offset, $y + $this->mm(2), '<p>Oui</p>', 0, 1]],
            $this->pdf->argsOf('writeHTMLCell')
        );
    }

    // --- setFormFieldSignature ---

    public function testSetFormFieldSignatureDrawsAFramedHeaderAndAnEmptyBox(): void
    {
        $this->startAt(150);
        // the header text is the package's default title (see the report on the ignored $title)
        $title = 'Cachet de l’entreprise et signature';

        $this->form->call('setFormFieldSignature', $this->x0, 150, 80, 40, $title);

        $border = $this->mm(1);
        $header = $this->mm(10);
        $inside = 80 - $border * 2;
        $this->assertSame([[self::FIELD_STROKE]], $this->pdf->argsOf('setDrawColorArray'));
        $this->assertSame([[self::FORM_BG], [self::WHITE], [self::FIELD_BG]], $this->pdf->argsOf('setFillColorArray'));
        $this->assertSame([['times', 'I', 9.0]], $this->pdf->argsOf('setFont'));
        $this->assertSame([
            [80, 40, '', ['TRBL' => $this->helper->border(1, self::FIELD_STROKE)], 0, '', true],
            [$inside, $header, $title, 0, 1, 'C', true],
            [$inside, 40 - $border * 3 - $header, '', 0, 1, '', true],
        ], $this->pdf->argsOf('Cell'));
        $this->assertSame(
            [[$this->x0], [$this->x0 + $border], [$this->x0 + $border]],
            $this->pdf->argsOf('setX')
        );
        $this->assertSame(
            [[150, false], [150 + $border, false], [150 + $border + $header + $border, false]],
            $this->pdf->argsOf('setY')
        );
    }

    // --- setFormFieldFaitA ---

    public function testSetFormFieldFaitASplitsTheWidthBetweenPlaceAndDate(): void
    {
        $this->startAt(170);

        $this->form->call('setFormFieldFaitA', $this->x0, 170, 150);

        $inputs = $this->pdf->argsOf('TextField');
        $this->assertSame(['faita', 'faitle'], array_column($inputs, 0));
        // "Fait à" fills 54% of the width, ", le" starts at 56% and runs to the end
        $this->assertSame(['Fait à  ', ', le  '], array_column($this->pdf->argsOf('Cell'), 2));
        $this->assertEqualsWithDelta([$this->x0, $this->x0 + 84], array_merge(...$this->pdf->argsOf('setX')), self::DELTA);
        $this->assertEqualsWithDelta($this->x0 + 81, $inputs[0][5] + $inputs[0][1], self::DELTA);
        $this->assertEqualsWithDelta($this->x0 + 150, $inputs[1][5] + $inputs[1][1], self::DELTA);
        $this->assertSame([170, 170], array_column($inputs, 6));

        $line = $this->pdf->argsOf('Line');
        $this->assertCount(1, $line);
        $this->assertSame([$this->x0, $this->x0 + 150], [$line[0][0], $line[0][2]]);
        $this->assertEqualsWithDelta(170 + $this->mm(20), $this->pdf->GetY(), self::DELTA);
    }
}
