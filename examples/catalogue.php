<?php

/**
 * Build a Catalogue PDF
 *
 * Multi-page catalogue with cover, intercalaires (category separators), and formation pages.
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Utils\Data as jData;
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Data;
use JDZ\Pdf\Helper;

// ---------------------------------------------------------------------------
// Formation data classes (same as formation.php)
// ---------------------------------------------------------------------------

class FormationProgramme
{
    public function __construct(
        public string $label = '',
        /** @var string[] */
        public array $items = [],
    ) {}
}

class FormationModule
{
    public function __construct(
        public string $title = '',
        public int $nbJours = 0,
    ) {}
}

class FormationSession
{
    public function __construct(
        public string $name = '',
        /** @var string[] */
        public array $dates = [],
    ) {}
}

class FormationData
{
    public int $id = 0;
    public string $title = '';
    public string $intro = '';
    public string $ref = '';
    public string $theme = '';
    public string $pdfTitle = '';
    public string $pictoTitle = '';
    public string $duree = '';
    /** @var string[] */
    public array $horaires = [];
    public string $tarif = '';
    public bool $anpe = false;
    /** @var string[] */
    public array $types = [];
    public bool $cpf = false;
    public bool $isNew = false;
    /** @var string[] */
    public array $competences = [];
    /** @var string[] */
    public array $profil = [];
    /** @var string[] */
    public array $prerequis = [];
    /** @var string[] */
    public array $admission = [];
    /** @var string[] */
    public array $evaluation = [];
    /** @var string[] */
    public array $methodes = [];
    /** @var string[] */
    public array $formateur = [];
    /** @var string[] */
    public array $debouches = [];
    /** @var string[] */
    public array $codes = [];
    /** @var string[] */
    public array $more = [];
    /** @var string[] */
    public array $contenu = [];
    /** @var FormationProgramme[] */
    public array $programme = [];
    /** @var FormationModule[] */
    public array $modules = [];
    /** @var FormationSession[] */
    public array $sessions = [];

    public function setId(int $id): static { $this->id = $id; return $this; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function setIntro(string $intro): static { $this->intro = $intro; return $this; }
    public function setRef(string $ref): static { $this->ref = $ref; return $this; }
    public function setTheme(string $theme): static { $this->theme = $theme; return $this; }
    public function setPdfTitle(string $pdfTitle): static { $this->pdfTitle = $pdfTitle; return $this; }
    public function setPictoTitle(string $pictoTitle): static { $this->pictoTitle = $pictoTitle; return $this; }
    public function setDuree(string $duree): static { $this->duree = $duree; return $this; }
    /** @param string[] $horaires */
    public function setHoraires(array $horaires): static { $this->horaires = $horaires; return $this; }
    public function setTarif(string $tarif): static { $this->tarif = $tarif; return $this; }
    public function setAnpe(bool $anpe): static { $this->anpe = $anpe; return $this; }
    /** @param string[] $types */
    public function setTypes(array $types): static { $this->types = $types; return $this; }
    public function setCpf(bool $cpf): static { $this->cpf = $cpf; return $this; }
    public function setIsNew(bool $isNew): static { $this->isNew = $isNew; return $this; }
    /** @param string[] $competences */
    public function setCompetences(array $competences): static { $this->competences = $competences; return $this; }
    /** @param string[] $profil */
    public function setProfil(array $profil): static { $this->profil = $profil; return $this; }
    /** @param string[] $prerequis */
    public function setPrerequis(array $prerequis): static { $this->prerequis = $prerequis; return $this; }
    /** @param string[] $admission */
    public function setAdmission(array $admission): static { $this->admission = $admission; return $this; }
    /** @param string[] $evaluation */
    public function setEvaluation(array $evaluation): static { $this->evaluation = $evaluation; return $this; }
    /** @param string[] $methodes */
    public function setMethodes(array $methodes): static { $this->methodes = $methodes; return $this; }
    /** @param string[] $formateur */
    public function setFormateur(array $formateur): static { $this->formateur = $formateur; return $this; }
    /** @param string[] $debouches */
    public function setDebouches(array $debouches): static { $this->debouches = $debouches; return $this; }
    /** @param string[] $codes */
    public function setCodes(array $codes): static { $this->codes = $codes; return $this; }
    /** @param string[] $more */
    public function setMore(array $more): static { $this->more = $more; return $this; }
    /** @param string[] $contenu */
    public function setContenu(array $contenu): static { $this->contenu = $contenu; return $this; }
    /** @param FormationProgramme[] $programme */
    public function setProgramme(array $programme): static { $this->programme = $programme; return $this; }
    public function addProgramme(FormationProgramme $programme): static { $this->programme[] = $programme; return $this; }
    /** @param FormationModule[] $modules */
    public function setModules(array $modules): static { $this->modules = $modules; return $this; }
    public function addModule(FormationModule $module): static { $this->modules[] = $module; return $this; }
    /** @param FormationSession[] $sessions */
    public function setSessions(array $sessions): static { $this->sessions = $sessions; return $this; }
    public function addSession(FormationSession $session): static { $this->sessions[] = $session; return $this; }
}

// ---------------------------------------------------------------------------
// Catalogue data classes
// ---------------------------------------------------------------------------

class CataloguePage
{
    public function __construct(
        public string $model = '',
        public array $data = [],
        public ?FormationData $formation = null,
    ) {}
}

class CatalogueData
{
    public string $title = '';
    public string $theme = '';
    /** @var CataloguePage[] */
    public array $pages = [];

    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function setTheme(string $theme): static { $this->theme = $theme; return $this; }
    public function addPage(CataloguePage $page): static { $this->pages[] = $page; return $this; }
}

// ---------------------------------------------------------------------------
// Cover Modelizer
// ---------------------------------------------------------------------------

class CoverModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;

    public function Page(): void
    {
        parent::Page();

        $w = $this->pdf->get('w');
        $h = $this->pdf->get('h');

        // full-page background
        $bgColor = $this->data->getColor('coverBg');
        $this->pdf->SetFillColorArray($bgColor);
        $this->pdf->Rect(0, 0, $w, $h, 'F');

        // decorative accent bar at top
        $accentColor = $this->data->getColor('coverAccent');
        $this->pdf->SetFillColorArray($accentColor);
        $this->pdf->Rect(0, 0, $w, 8, 'F');

        // decorative line
        $lineY = $h * 0.42;
        $lineMargin = 40;
        $this->pdf->SetDrawColorArray([255, 255, 255]);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line($lineMargin, $lineY, $w - $lineMargin, $lineY);

        // title
        $this->pdf->SetTextColorArray([255, 255, 255]);
        $this->pdf->SetFont($this->data->get('fontDark'), 'B', 32);
        $titleY = $lineY - 30;
        $this->pdf->writeHTMLCell($w - $lineMargin * 2, 0, $lineMargin, $titleY, $this->data->toUppercase($this->data->get('coverTitle')), 0, 1, false, true, 'C', true);

        // year
        $this->pdf->SetFont($this->data->get('fontLight'), '', 48);
        $yearY = $lineY + 8;
        $this->pdf->writeHTMLCell($w - $lineMargin * 2, 0, $lineMargin, $yearY, $this->data->get('coverYear'), 0, 1, false, true, 'C', true);

        // subtitle
        $this->pdf->SetFont($this->data->get('fontDefault'), '', 14);
        $subY = $yearY + 30;
        $this->pdf->writeHTMLCell($w - $lineMargin * 2, 0, $lineMargin, $subY, $this->data->get('coverSubtitle'), 0, 1, false, true, 'C', true);

        // bottom accent bar
        $this->pdf->SetFillColorArray($accentColor);
        $this->pdf->Rect(0, $h - 8, $w, 8, 'F');
    }
}

// ---------------------------------------------------------------------------
// Intercalaire Modelizer (category separator)
// ---------------------------------------------------------------------------

class CatalogueIntercalaireModelizer extends Modelizer
{
    protected bool $printHeader = true;
    protected bool $printFooter = true;

    public function Header(): void
    {
        $this->pdf->SetCellPadding(0);
        $this->pdf->SetFillColorArray($this->data->getColor('headerBg'));
        $this->pdf->SetDrawColorArray($this->data->getColor('headerBg'));
        $this->pdf->SetX(0);
        $this->pdf->SetY(0, false);
        $this->pdf->Cell($this->pdf->get('w'), $this->data->px2mm('headerHeight'), '', 0, 0, '', true);

        $this->pdf->SetTextColorArray($this->data->getColor('headerColor'));
        $this->pdf->SetFont($this->data->get('fontDefault'), '', $this->data->px2pt('headerFsize'));

        $headerParts = explode('[BR]', $this->data->get('headerText'));
        $_top = $this->helper->px2mm($this->data->get('headerHeight') - 6 - $this->data->get('headerFsize') * count($headerParts));
        foreach ($headerParts as $headerPart) {
            $this->pdf->SetY($_top, false);
            $_w = $this->pdf->GetStringWidth($headerPart);
            $x = ($this->pdf->get('w') - $_w) / 2;
            $this->pdf->SetX($x);
            $this->pdf->Cell($_w, $this->data->px2mm('headerFsize'), $headerPart, 0, 1, 'C');
            $_top += $this->data->px2mm('headerFsize');
        }

        $_top = $this->helper->px2mm($this->data->get('headerHeight') + 10);
        $this->pdf->Line($x, $_top, $x + $_w, $_top, $this->helper->border($this->data->getBorderWidth(4), $this->data->getColor('headerBg')));
    }

    public function Footer(): void
    {
        $showPageNumber = $this->pdf->get('izPageNoShow');

        $x = $this->data->get('pagePadding');
        $y = $this->pdf->get('h') - $this->pdf->getBreakMargin();
        $y += $this->helper->px2mm(15);

        $footerTextWidth = $this->data->get('pageWidth');
        $footerTextWidth -= $this->helper->px2mm(10);

        $footerText = 'Retrouvez tous nos programmes sur jdz-formation.fr | Conseils et inscriptions contact@jdz-formation.fr - 01 23 45 67 89';
        $this->pdf->SetTextColorArray($this->lightColorArray('black', 20));
        $this->pdf->SetFont($this->data->get('fontDefault'), '', $this->helper->px2pt(8));
        $this->pdf->Multicell($footerTextWidth, $this->helper->px2mm(9), $footerText, 0, 'L', false, 1, $x, $y + $this->helper->px2mm(12));

        if (true === $showPageNumber) {
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->SetFont($this->data->get('fontDefault'), 'B', $this->helper->px2pt(10));
            $this->pdf->SetX($this->data->get('pagePadding') + $footerTextWidth);
            $this->pdf->SetY($y + $this->helper->px2mm(10), false);
            $this->pdf->Cell(0, 0, $this->pdf->PageNo(), 0, 0, 'R');
        }
    }
}

// ---------------------------------------------------------------------------
// Formation Modelizer (same as formation.php)
// ---------------------------------------------------------------------------

class CatalogueFormationModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;
    private bool $contentsOnRight = false;

    public function load(): void
    {
        parent::load();

        $top    = $this->helper->px2mm(20) + $this->helper->px2mm(22);
        $right  = $this->helper->px2mm(30) + $this->helper->px2mm(10);
        $left   = $this->helper->px2mm(45) + $this->helper->px2mm(24);
        $bottom = $this->helper->px2mm(30) + $this->helper->px2mm(10);

        $columnsGap = $this->helper->px2mm(20);

        $pageWidth = $this->pdf->getPageWidth();
        $pageWidth -= $left;
        $pageWidth -= $right;

        $columnsWidth = $pageWidth / 2;
        $rightColOffset = $left + $columnsWidth + $columnsGap;
        $rightColWidth  = $columnsWidth - $columnsGap;

        $this->pdf->SetMargins($left, $top, $right);
        $this->pdf->SetAutoPageBreak(false, $bottom);

        $this->data
            ->set('pageWidth', $pageWidth)
            ->set('columnWidth', $columnsWidth)
            ->set('rightColOffset', $rightColOffset)
            ->set('rightColWidth', $rightColWidth);
    }

    public function Page(): void
    {
        parent::Page();

        $formation = $this->data->get('formation');

        $x = $this->pdf->getX();
        $y = $this->pdf->getY();

        $leftX = $x;
        $topY = $y;

        $sideInfosW = $this->helper->px2mm(126);
        $sideInfosH = $this->helper->px2mm(160);
        $sideInfosX = $leftX + $this->data->get('availableWidth') - $sideInfosW;
        $mainTitleW = $this->data->get('availableWidth') - $sideInfosW - $this->helper->px2mm(16);
        $mainTitleH = $sideInfosH * 0.4;
        $introY     = $topY + $mainTitleH + $this->helper->px2mm(14);
        $footerH    = $this->helper->px2mm(72);
        $footerY    = $topY + $this->data->get('availableHeight') - $footerH;
        $contentsY  = $topY + $sideInfosH + $this->helper->px2mm(20);
        $this->data->set('contentsMaxHeight', $this->data->get('availableHeight') - $contentsY - $footerH);
        $this->data->set('contentsTopOffset', $contentsY);
        $this->data->set('contentsMaxTopOffset', $footerY);

        // picto title
        $pictoTitleDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/' . $formation->pictoTitle . '.jpg', 16, 16);
        $this->pdf->Image($pictoTitleDims['path'], $this->helper->px2mm(15), $topY + $this->helper->px2mm(8), $pictoTitleDims['w'], $pictoTitleDims['h']);

        // title
        $titleArray = $this->explodeTitle($formation->title);
        $titleY = $topY;
        if (1 === count($titleArray)) {
            $titleY += $this->helper->px2mm(19);
        } elseif (2 === count($titleArray)) {
            $titleY += $this->helper->px2mm(10);
        }
        $formation->title = implode("<br/>", $titleArray);

        $this->pdf->SetTextColorArray($this->data->getColor('h1'));
        $this->pdf->SetFont($this->data->get('fontDark'), 'B', 19);
        $this->pdf->setCellHeightRatio(1.09);
        $this->pdf->writeHTMLCell($mainTitleW, 0, $leftX, $titleY, $this->data->toUppercase($formation->title), 0, 1, false, true, '', true);

        // intro
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont($this->data->get('fontMedium'), 'B', 12);
        $this->pdf->setCellHeightRatio(1);
        $this->pdf->MultiCell($mainTitleW, 0, $formation->intro, 0, 'L', false, 1, $leftX, $introY, true, 0, true);
        $this->pdf->setCellHeightRatio(1.25);

        // side infos
        $infosY = $topY;
        $sideInfosXText  = $sideInfosX + $this->helper->px2mm(38);
        $sideInfosXFlags = $sideInfosX + $this->helper->px2mm(9);

        if (true === $formation->cpf) {
            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-cpf.jpg', null, 10);
            $this->pdf->Image($pictoDims['path'], $sideInfosXFlags, $infosY - $this->helper->px2mm(14), $pictoDims['w'], $pictoDims['h']);
            $sideInfosXFlags += $pictoDims['w'] + $this->helper->px2mm(6);
        }

        if (true === $formation->isNew) {
            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-nouveau.jpg', null, 4);
            $this->pdf->Image($pictoDims['path'], $sideInfosXFlags, $infosY - $this->helper->px2mm(7), $pictoDims['w'], $pictoDims['h']);
        }

        $infosY += $this->helper->px2mm(23);
        $this->pdf->SetTextColorArray($this->data->getColor('picto_details'));
        $this->pdf->SetFont($this->data->get('fontDefault'), '', 8);
        $this->pdf->setFontSpacing(0);

        $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-type-full.jpg', null, 7);
        $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
        $infosYTypes = 1 === count($formation->types) ? $infosY + $this->helper->px2mm(5) : $infosY;
        foreach ($formation->types as $formationType) {
            $this->pdf->Text($sideInfosXText, $infosYTypes, $formationType);
            $infosYTypes += $this->helper->px2mm(9);
        }

        $infosY += $this->helper->px2mm(27);
        $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-ref-full.jpg', null, 7);
        $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
        $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(5), $formation->ref);

        if ($formation->duree) {
            $infosY += $this->helper->px2mm(27);
            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-duree-full.jpg', null, 7);
            $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
            $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(5), $formation->duree);
        }

        if ($formation->horaires) {
            $infosY += $this->helper->px2mm(27);
            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-horaires-full.jpg', null, 7);
            $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
            $infosYHoraires = 1 === count($formation->horaires) ? $infosY + $this->helper->px2mm(5) : $infosY;
            foreach ($formation->horaires as $formationHoraire) {
                $this->pdf->Text($sideInfosXText, $infosYHoraires, $formationHoraire);
                $infosYHoraires += $this->helper->px2mm(9);
            }
        }

        if ($formation->tarif) {
            $infosY += $this->helper->px2mm(27);
            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-prix-full.jpg', null, 7);
            $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
            $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(5), $formation->tarif);

            if (true === $formation->anpe) {
                $this->pdf->SetFont($this->data->get('fontDefault'), '', 6);
                $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(14), 'Individuels ou demandeurs');
                $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(20), "d\xE2\x80\x99emploi, consultez-nous.");
            }
        }

        // footer arrows
        $pictoTitleDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/pictos-footer.jpg', null, 30);
        $this->pdf->Image($pictoTitleDims['path'], $this->helper->px2mm(18), $footerY - $this->helper->px2mm(2), $pictoTitleDims['w'], $pictoTitleDims['h']);

        // footer logo
        $pictoTitleDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/logo-footer.jpg', null, 11);
        $this->pdf->Image($pictoTitleDims['path'], $leftX, $footerY + $this->helper->px2mm(2), $pictoTitleDims['w'], $pictoTitleDims['h']);

        $currentFooterY = $footerY + $this->helper->px2mm(44);

        $footerText = "12 rue de la Formation, 75001 Paris - T\xC3\xA9l. : 01 23 45 67 89 - contact@jdz-formation.fr - www.jdz-formation.fr";
        $this->pdf->SetTextColorArray($this->lightColorArray('textColor', 20));
        $this->pdf->SetFont($this->data->get('fontMedium'), 'B', 7);
        $this->pdf->setFontSpacing(0.21);
        $this->pdf->MultiCell($this->data->get('pageWidth'), $this->helper->px2mm(9), $footerText, 0, 'L', false, 1, $leftX, $currentFooterY);
        $this->pdf->setFontSpacing(0);

        $currentFooterY += $this->helper->px2mm(10);

        $footerText = "LOCAUX ACCESSIBLES. PERSONNES EN SITUATION DE HANDICAP, NOUS CONTACTER, CONFIDENTIALIT\xC3\x89 ASSUR\xC3\x89E";
        $this->pdf->SetTextColorArray($this->lightColorArray('textColor', 20));
        $this->pdf->SetFont($this->data->get('fontLight'), '', 6);
        $this->pdf->setFontSpacing(0.11);
        $this->pdf->MultiCell($this->data->get('pageWidth'), $this->helper->px2mm(9), $footerText, 0, 'L', false, 1, $leftX, $currentFooterY);
        $this->pdf->setFontSpacing(0);

        $currentFooterY += $this->helper->px2mm(9);

        $footerText = "SAS capital de 100 000 \xE2\x82\xAC - RCS Paris 123 456 789 - SIRET 123 456 789 00011 \xE2\x80\x93 APE 8559B - TVA FR12 123456789 \xE2\x80\x93 D\xC3\xA9claration d\xE2\x80\x99activit\xC3\xA9 n\xC2\xB0 11 75 00000 75";
        $this->pdf->SetTextColorArray($this->lightColorArray('textColor', 20));
        $this->pdf->SetFont($this->data->get('fontLight'), '', 6);
        $spacing = true === $this->pdf->get('izPageNoShow') ? 0.056 : 0.086;
        $this->pdf->setFontSpacing($spacing);
        $this->pdf->MultiCell($this->data->get('pageWidth'), $this->helper->px2mm(9), $footerText, 0, 'L', false, 1, $leftX, $currentFooterY);
        $this->pdf->setFontSpacing(0);

        $currentFooterY += $this->helper->px2mm(9);

        $footerText = "JDZ Formation \xE2\x80\x93 Organisme de formation professionnelle \xE2\x80\x93 www.jdz-formation.fr";
        $this->pdf->SetTextColorArray($this->lightColorArray('textColor', 20));
        $this->pdf->SetFont($this->data->get('fontLight'), '', 6);
        $this->pdf->setFontSpacing($spacing);
        $this->pdf->MultiCell($this->data->get('pageWidth'), $this->helper->px2mm(9), $footerText, 0, 'L', false, 1, $leftX, $currentFooterY);
        $this->pdf->setFontSpacing(0);

        // page number
        if (true === $this->pdf->get('izPageNoShow')) {
            $pageZoneW = $this->helper->px2mm(40);
            $pageZoneH = $this->helper->px2mm(14);
            $pageZoneX = $leftX + $this->data->get('availableWidth') - $pageZoneW;
            $pageZoneY = $topY + $this->data->get('availableHeight') - $pageZoneH;
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->SetFont($this->data->get('fontDefault'), 'B', $this->helper->px2pt(8));
            $this->pdf->SetX($pageZoneX);
            $this->pdf->SetY($pageZoneY, false);
            $this->pdf->Cell($pageZoneW, $pageZoneH, $this->pdf->PageNo(), 0, 0, 'R', false, '', 0, false, 'T', 'B');
        }

        // LEFT CONTENTS
        $contentW = $this->data->get('columnWidth');
        $contentX = $leftX;
        $contentY = $contentsY;

        $codesTitle = count($formation->codes) > 1 ? 'Codes' : 'Code';
        $this->checkSideAndAddContentBloc($codesTitle, $formation->codes, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Comp\xC3\xA9tences vis\xC3\xA9es", $formation->competences, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Public concern\xC3\xA9", $formation->profil, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Pr\xC3\xA9requis", $formation->prerequis, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Modalit\xC3\xA9s d\xE2\x80\x99admission", $formation->admission, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Modalit\xC3\xA9s d\xE2\x80\x99\xC3\xA9valuation", $formation->evaluation, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("M\xC3\xA9thodes et outils p\xC3\xA9dagogiques", $formation->methodes, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Profil de l\xE2\x80\x99intervenant", $formation->formateur, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("D\xC3\xA9bouch\xC3\xA9s", $formation->debouches, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Informations compl\xC3\xA9mentaires", $formation->more, $contentW, $contentX, $contentY);

        // RIGHT CONTENTS
        $contentW = $this->data->get('rightColWidth');
        $contentX = $this->data->get('rightColOffset');
        if (false === $this->contentsOnRight) {
            $contentY = $this->data->get('contentsTopOffset');
            $this->contentsOnRight = true;
        }

        $this->pdf->setY($contentY, false);

        if ($formation->programme) {
            $this->pdf->setX($contentX);
            $this->pdf->SetTextColorArray($this->data->getColor('h2'));
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 12);
            $this->pdf->Cell($contentW, $this->helper->px2mm(14), $this->data->toUppercase('Programme'), 0, 1, 'L', false, '', 0, false, 'T', 'M');

            $this->pdf->setX($contentX);
            $this->pdf->SetTextColorArray($this->data->getColor('h2'));
            $this->pdf->SetFont($this->data->get('fontDefault'), '', 9);
            $this->pdf->Cell($contentW, $this->helper->px2mm(12), 'Formation personnalisable selon vos besoins', 0, 1, 'L', false, '', 0, false, 'T', 'M');
            $contentY = $this->pdf->getY();
            $contentY += $this->helper->px2mm(5);
            $this->pdf->setY($contentY, false);

            foreach ($formation->programme as $programme) {
                $this->pdf->setX($contentX);
                $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
                $this->pdf->SetFont($this->data->get('fontMedium'), '', 8);
                $this->pdf->MultiCell($contentW, 0, $programme->label, 0, 'L', false, 1, $contentX, $contentY);
                $contentY = $this->pdf->getY();
                $contentY += $this->helper->px2mm(3);
                $this->pdf->setY($contentY, false);

                if ($programme->items) {
                    $this->BulletList($programme->items, $contentX, $contentW);
                    $contentY = $this->pdf->getY();
                }
            }
        } elseif ($formation->modules) {
            $this->pdf->setX($contentX);
            $this->pdf->SetTextColorArray($this->data->getColor('h2'));
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 12);
            $this->pdf->Cell($contentW, $this->helper->px2mm(14), $this->data->toUppercase('Composition du parcours'), 0, 1, 'L', false, '', 0, false, 'T', 'M');

            $contentY += $this->helper->px2mm(19);
            $this->pdf->setY($contentY, false);
            $this->pdf->setX($contentX);

            $moduleStrings = array_map(function (FormationModule $m) {
                $suffix = $m->nbJours > 0 ? ' (' . ($m->nbJours > 1 ? $m->nbJours . ' jours' : '1 jour') . ')' : '';
                return $m->title . $suffix;
            }, $formation->modules);

            $this->BulletList($moduleStrings, $contentX, $this->data->get('rightColWidth'), 8, 12);
            $contentY = $this->pdf->getY();
        } else {
            $this->pdf->setX($contentX);
            $this->checkSideAndAddContentBloc('Composition du parcours', $formation->contenu, $contentW, $contentX, $contentY);
        }

        $contentY += $this->helper->px2mm(8);

        // sessions
        if ($formation->sessions) {
            $this->pdf->setY($contentY, false);

            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-calendrier-full.jpg', null, 7);
            $this->pdf->Image($pictoDims['path'], $contentX - $this->helper->px2mm(10), $contentY, $pictoDims['w'], $pictoDims['h']);
            $calendrierX = $contentX - $this->helper->px2mm(10) + $pictoDims['w'] + $this->helper->px2mm(8);
            $this->pdf->setY($contentY, false);
            $this->pdf->setX($calendrierX);
            $this->pdf->SetTextColorArray($this->data->getColor('picto_horaires'));
            $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 8);
            $this->pdf->Text($calendrierX, $contentY, $this->data->toUppercase('Prochaines sessions'));
            $contentY += $this->helper->px2mm(12);

            foreach ($formation->sessions as $site) {
                if (count($formation->sessions) > 1) {
                    $this->pdf->setY($contentY, false);
                    $this->pdf->setX($calendrierX);
                    $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
                    $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 8);
                    $this->pdf->MultiCell($contentW, 0, $site->name, 0, 'L', false, 1, $calendrierX, $contentY, true, 0, false, false, 0, 'T', false);
                    $contentY = $this->pdf->getY();
                    $contentY += $this->helper->px2mm(2);
                }

                foreach ($site->dates as $session) {
                    $this->pdf->setY($contentY, false);
                    $this->pdf->setX($calendrierX);
                    $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
                    $this->pdf->SetFont($this->data->get('fontDefault'), '', 8);
                    $this->pdf->MultiCell($contentW, 0, $session, 0, 'L', false, 1, $calendrierX, $contentY, true, 0, false, false, 0, 'T', false);
                    $contentY = $this->pdf->getY();
                    $contentY += $this->helper->px2mm(2);
                }
            }
            $this->pdf->setY($contentY, false);
            $this->pdf->setX($contentX);
        }
    }

    private function checkSideAndAddContentBloc(string $title, array $content, int|float &$w, int|float &$x, int|float &$y): void
    {
        if (!$content) { return; }

        $wTry = $w; $xTry = $x; $yTry = 0;

        $this->pdf->startTransaction();
        $this->pdf->SetY(0);
        $this->addContentBloc($title, $content, $wTry, $xTry, $yTry);
        $needsHeight = $this->pdf->GetY();
        $this->pdf->rollbackTransaction(true);
        $this->pdf->commitTransaction();

        if ($y + $needsHeight >= $this->data->get('contentsMaxTopOffset')) {
            $w = $this->data->get('rightColWidth');
            $x = $this->data->get('rightColOffset');
            $y = $this->data->get('contentsTopOffset');
            $this->contentsOnRight = true;
        }

        $this->addContentBloc($title, $content, $w, $x, $y);
    }

    private function addContentBloc(string $title, array $content, int|float &$w, int|float &$x, int|float &$y): void
    {
        $this->pdf->setY($y, false);
        $this->pdf->setX($x);

        if ('' !== $title) {
            $this->pdf->SetTextColorArray($this->data->getColor('h2'));
            $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 9);
            $this->pdf->Cell($w, $this->helper->px2mm(14), $this->data->toUppercase($title), 0, 1, 'L', false, '', 0, false, 'T', 'M');
        }

        $this->BulletList($content, $x, $w);
        $y = $this->pdf->getY();
    }

    private function explodeTitle(string $title, int $lineLength = 23): array
    {
        $mbEncoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');
        $titleWrap = wordwrap($title, $lineLength, '__');
        $titleArray = explode('__', $titleWrap);
        mb_internal_encoding($mbEncoding);

        if (count($titleArray) > 3) { return $this->explodeTitle($title, $lineLength + 3); }
        foreach ($titleArray as $titleLine) {
            if (mb_strlen($titleLine) < 3) { return $this->explodeTitle($title, $lineLength + 2); }
        }
        return $titleArray;
    }

    private function BulletList(array $lis, int|float $x, int|float $w, int $fontsize = 8, int $lineheight = 8): void
    {
        $bulletSize = 3;
        foreach (array_values($lis) as $li) {
            $y = $this->pdf->GetY();
            if (count($lis) > 1) {
                $xOffset = $x - $this->helper->px2mm(10);
                $yOffset = $y + $this->helper->px2mm(ceil((($fontsize - 1) - $bulletSize) / 2));
                $this->pdf->StartTransform();
                $this->pdf->Rotate(45, $xOffset + $this->helper->px2mm($bulletSize), $yOffset + $this->helper->px2mm($bulletSize) / 2);
                $this->pdf->Rect($xOffset, $yOffset, $this->helper->px2mm($bulletSize), $this->helper->px2mm($bulletSize), 'DF', ['width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->data->getColor('bullets')], $this->data->getColor('bullets'));
                $this->pdf->StopTransform();
            }
            $this->pdf->SetFont($this->data->get('fontLight'), '', $fontsize);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->MultiCell($w, 0, $li, 0, 'L', false, 1, $x, $y, true, 0, false, false, 0, 'T', false);
            $this->pdf->Ln($this->helper->px2mm($lineheight - $fontsize));
        }
        $this->pdf->Ln($this->helper->px2mm($lineheight));
    }
}

// ---------------------------------------------------------------------------
// Catalogue Builder
// ---------------------------------------------------------------------------

class CatalogueBuilder extends Builder
{
    protected CatalogueData $catalogueData;

    public function setCatalogueData(CatalogueData $catalogueData): static
    {
        $this->catalogueData = $catalogueData;
        return $this;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle($this->catalogueData->title);
        $pdf->SetSubject('Catalogue Formation');
        $pdf->SetCreator('JDZ Formation');
        $pdf->SetAuthor('JDZ Formation');
        $pdf->SetKeywords('formation, catalogue');
        $pdf->set('izPageNoShow', true);
        $pdf->set('izSourcesPath', $this->sourcesPath);

        $pdf->set('font', (object)['name' => 'montserrat', 'styles' => [null, 'B', 'I', 'BI']]);
        $pdf->set('font', (object)['name' => 'montserratblack', 'styles' => [null, 'I']]);
        $pdf->set('font', (object)['name' => 'montserratmedium', 'styles' => [null, 'B']]);
        $pdf->set('font', (object)['name' => 'montserratlight', 'styles' => [null, 'I']]);

        $helper = new Helper();
        $pagePadding = $helper->px2mm(30);
        $pageWidth = $pdf->getPageWidth() - $pagePadding * 2;

        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }

        \set_time_limit(240);

        foreach ($this->catalogueData->pages as $page) {
            $modelizerData = new Data();
            $modelConfig = $this->loadModelizerConfig($page->model === 'cover' ? 'catalogue' : $page->model);

            if (isset($modelConfig['fontDefault']) && ($fontDefault = $modelConfig['fontDefault'])) {
                $modelConfig['h1Font'] = [$fontDefault, 'B'];
                $modelConfig['h2Font'] = [$fontDefault, 'B'];
                $modelConfig['h3Font'] = [$fontDefault, 'B'];
                $modelConfig['h4Font'] = [$fontDefault, 'B'];
                $modelConfig['h5Font'] = [$fontDefault, 'B'];
                $modelConfig['h6Font'] = [$fontDefault, 'B'];
                $modelConfig['pFont']  = [$fontDefault, ''];
                $modelConfig['liFont'] = [$fontDefault, ''];
            }

            $modelConfig['h1Color'] = $this->catalogueData->theme;
            $modelConfig['h2Color'] = $this->catalogueData->theme;
            $modelConfig['h3Color'] = $this->catalogueData->theme;
            $modelConfig['h4Color'] = $this->catalogueData->theme;
            $modelConfig['h5Color'] = $this->catalogueData->theme;
            $modelConfig['h6Color'] = $this->catalogueData->theme;

            if ($page->model === 'formation' && $page->formation) {
                $modelConfig['h1Color'] = $page->formation->theme;
                $modelConfig['h2Color'] = $page->formation->theme;
                $modelConfig['h3Color'] = $page->formation->theme;
                $modelConfig['h4Color'] = $page->formation->theme;
                $modelConfig['h5Color'] = $page->formation->theme;
                $modelConfig['h6Color'] = $page->formation->theme;
                $modelConfig['h1'] = $page->formation->theme;
                $modelConfig['h2'] = $page->formation->theme;
                $modelConfig['bullets'] = $page->formation->theme;
            }

            $modelConfig['pagePadding'] = $pagePadding;
            $modelConfig['pageWidth'] = $pageWidth;

            if (!empty($page->data['headerBg'])) {
                $modelConfig['colors']['headerBg'] = $page->data['headerBg'];
            }
            if (!empty($page->data['headerColor'])) {
                $modelConfig['colors']['headerColor'] = $page->data['headerColor'];
            }

            $modelizerData->sets($modelConfig);
            $modelizerData->sets($page->data);

            if ($page->formation) {
                $modelizerData->set('formation', $page->formation);
            }

            $modelizer = match ($page->model) {
                'cover' => new CoverModelizer($pdf, $modelizerData),
                'intercalaire' => new CatalogueIntercalaireModelizer($pdf, $modelizerData),
                'formation' => new CatalogueFormationModelizer($pdf, $modelizerData),
                default => throw new \Exception('Unknown page model: ' . $page->model),
            };

            $modelizer->load();
            $modelizer->toPdf();
        }

        $pdf->lastPage();
        $pdf->Output($this->targetPath, 'F');

        if (!file_exists($this->targetPath)) {
            throw new \Exception('Impossible de créer le catalogue.');
        }
    }
}

// ---------------------------------------------------------------------------
// Mock data: 10 formations in 3 categories
// ---------------------------------------------------------------------------

function buildFormation(int $id, string $title, string $ref, string $theme, string $picto, string $intro, string $duree, string $tarif, array $types, array $competences, array $profil, array $programme, array $sessions = [], bool $cpf = false, bool $isNew = false): FormationData
{
    $f = new FormationData();
    $f->setId($id)
        ->setTitle($title)
        ->setIntro($intro)
        ->setRef($ref)
        ->setTheme($theme)
        ->setPdfTitle($title)
        ->setPictoTitle($picto)
        ->setDuree($duree)
        ->setHoraires(['9h00 - 12h30', '14h00 - 17h30'])
        ->setTarif($tarif)
        ->setAnpe(false)
        ->setTypes($types)
        ->setCpf($cpf)
        ->setIsNew($isNew)
        ->setCompetences($competences)
        ->setProfil($profil)
        ->setPrerequis(['Aucun'])
        ->setAdmission(['Entretien de positionnement'])
        ->setEvaluation(['QCM de fin de formation', 'Mise en situation pratique'])
        ->setMethodes(['Apports théoriques', 'Exercices pratiques', 'Études de cas'])
        ->setFormateur(['Formateur expert du domaine'])
        ->setDebouches([])
        ->setCodes([])
        ->setMore([])
        ->setContenu([])
        ->setModules([]);

    foreach ($programme as $p) {
        $f->addProgramme(new FormationProgramme($p[0], $p[1]));
    }
    foreach ($sessions as $s) {
        $f->addSession(new FormationSession($s[0], $s[1]));
    }

    return $f;
}

// ---------------------------------------------------------------------------
// Execution
// ---------------------------------------------------------------------------

require_once __DIR__ . '/autoload.php';

try {
    $data = new jData();
    $data->sets(['code' => 'catalogue-1']);

    $catalogueData = new CatalogueData();
    $catalogueData->setTitle('Catalogue Formations 2026')
        ->setTheme('#01a6ba');

    // Cover page
    $catalogueData->addPage(new CataloguePage('cover', [
        'coverTitle' => 'Catalogue Formations',
        'coverYear' => '2026',
        'coverSubtitle' => 'JDZ Formation',
        'coverBg' => '#007a87',
        'coverAccent' => '#FECB00',
    ]));

    // === Category 1: Marketing & Communication ===
    $catalogueData->addPage(new CataloguePage('intercalaire', [
        'headerText' => 'Marketing[BR]& Communication',
        'headerBg' => '#01a6ba',
        'headerColor' => '#FFFFFF',
    ]));

    $mktTheme = '#01a6ba';

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        1, 'Marketing digital fondamental', 'MKT-001', $mktTheme, 'fleche-stage',
        'Acquérir les fondamentaux du marketing digital pour développer une stratégie efficace.',
        '3 jours (21 heures)', '1 890 € HT', ['Inter-entreprises'],
        ['Définir une stratégie digitale', 'Comprendre le parcours client', 'Mesurer le ROI'],
        ['Responsables marketing', 'Chefs de projet'],
        [
            ['Les fondamentaux', ['Écosystème digital', 'Stratégie multicanal', 'Personas et parcours client']],
            ['Mise en pratique', ['Audit de présence digitale', 'Plan d\'action opérationnel', 'Indicateurs de performance']],
        ],
        [['Paris', ['Du 10 au 12 mars 2026', 'Du 15 au 17 juin 2026']]],
        true, true
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        2, 'Stratégie de contenu et SEO', 'MKT-002', $mktTheme, 'fleche-stage',
        'Maîtriser la rédaction web et le référencement naturel pour gagner en visibilité.',
        '2 jours (14 heures)', '1 490 € HT', ['Inter-entreprises', 'Intra-entreprise'],
        ['Rédiger pour le web', 'Optimiser le référencement naturel', 'Construire une ligne éditoriale'],
        ['Rédacteurs web', 'Responsables communication'],
        [
            ['SEO technique et on-page', ['Audit technique', 'Optimisation des contenus', 'Maillage interne']],
            ['Stratégie éditoriale', ['Calendrier éditorial', 'Formats de contenu', 'Mesure de performance']],
        ],
        [['Paris', ['Du 7 au 8 avril 2026']]]
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        3, 'Réseaux sociaux et community management', 'MKT-003', $mktTheme, 'fleche-stage',
        'Développer et animer une communauté engagée sur les réseaux sociaux.',
        '2 jours (14 heures)', '1 490 € HT', ['Inter-entreprises'],
        ['Définir une stratégie social media', 'Créer du contenu engageant', 'Gérer une communauté'],
        ['Community managers', 'Chargés de communication'],
        [
            ['Stratégie et outils', ['Panorama des réseaux sociaux', 'Choix des plateformes', 'Outils de planification']],
            ['Animation et modération', ['Création de contenu', 'Gestion de crise', 'Reporting et analytics']],
        ],
        [['Paris', ['Du 5 au 6 mai 2026']], ['Lyon', ['Du 22 au 23 septembre 2026']]]
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        4, 'Email marketing et automation', 'MKT-004', $mktTheme, 'fleche-stage',
        'Concevoir des campagnes email performantes et mettre en place des scénarios d\'automation.',
        '2 jours (14 heures)', '1 590 € HT', ['Inter-entreprises'],
        ['Concevoir des emails efficaces', 'Segmenter sa base de données', 'Automatiser les campagnes'],
        ['Responsables marketing', 'Chargés CRM'],
        [
            ['Email marketing', ['Bonnes pratiques', 'Délivrabilité', 'A/B testing']],
            ['Marketing automation', ['Scénarios de nurturing', 'Lead scoring', 'Outils du marché']],
        ],
        [['Paris', ['Du 2 au 3 juin 2026']]],
        true
    )));

    // === Category 2: Développement Web ===
    $catalogueData->addPage(new CataloguePage('intercalaire', [
        'headerText' => 'Développement[BR]Web',
        'headerBg' => '#007a87',
        'headerColor' => '#FFFFFF',
    ]));

    $devTheme = '#007a87';

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        5, 'PHP et Symfony avancé', 'DEV-001', $devTheme, 'fleche-stage',
        'Approfondir ses compétences en PHP 8 et maîtriser le framework Symfony.',
        '5 jours (35 heures)', '2 990 € HT', ['Inter-entreprises', 'Intra-entreprise'],
        ['Maîtriser PHP 8', 'Développer avec Symfony', 'Écrire des tests automatisés'],
        ['Développeurs PHP', 'Développeurs full stack'],
        [
            ['PHP 8 avancé', ['Typage strict et enums', 'Fibers et programmation asynchrone', 'Attributs et named arguments']],
            ['Symfony', ['Architecture et bonnes pratiques', 'Sécurité et authentification', 'API Platform']],
        ],
        [['Paris', ['Du 23 au 27 mars 2026', 'Du 21 au 25 septembre 2026']]],
        false, true
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        6, 'JavaScript et frameworks front-end', 'DEV-002', $devTheme, 'fleche-stage',
        'Maîtriser JavaScript moderne et les principaux frameworks front-end.',
        '4 jours (28 heures)', '2 490 € HT', ['Inter-entreprises'],
        ['Maîtriser ES2024+', 'Développer avec React ou Vue.js', 'Gérer l\'état applicatif'],
        ['Développeurs front-end', 'Intégrateurs web'],
        [
            ['JavaScript moderne', ['ES2024+ et TypeScript', 'Modules et bundlers', 'Programmation fonctionnelle']],
            ['Frameworks', ['React : composants et hooks', 'Vue.js : composition API', 'State management']],
        ],
        [['Paris', ['Du 13 au 16 avril 2026']], ['Lyon', ['Du 19 au 22 octobre 2026']]]
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        7, 'DevOps et déploiement continu', 'DEV-003', $devTheme, 'fleche-stage',
        'Mettre en place une chaîne CI/CD et adopter les pratiques DevOps.',
        '3 jours (21 heures)', '2 190 € HT', ['Inter-entreprises'],
        ['Conteneuriser avec Docker', 'Automatiser les déploiements', 'Surveiller la production'],
        ['Développeurs', 'Administrateurs système'],
        [
            ['Conteneurisation', ['Docker et Docker Compose', 'Registries et images', 'Orchestration avec Kubernetes']],
            ['CI/CD', ['GitLab CI / GitHub Actions', 'Tests automatisés', 'Monitoring et alerting']],
        ],
        [['Paris', ['Du 18 au 20 mai 2026']]]
    )));

    // === Category 3: Management & Leadership ===
    $catalogueData->addPage(new CataloguePage('intercalaire', [
        'headerText' => 'Management[BR]& Leadership',
        'headerBg' => '#ef790e',
        'headerColor' => '#FFFFFF',
    ]));

    $mgtTheme = '#ef790e';

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        8, 'Management d\'équipe', 'MGT-001', $mgtTheme, 'fleche-titre',
        'Développer ses compétences managériales pour animer et motiver son équipe.',
        '3 jours (21 heures)', '1 990 € HT', ['Inter-entreprises', 'Intra-entreprise'],
        ['Adopter une posture managériale', 'Motiver et fédérer', 'Gérer les conflits'],
        ['Managers', 'Chefs d\'équipe', 'Responsables de service'],
        [
            ['Posture et communication', ['Styles de management', 'Écoute active', 'Feedback constructif']],
            ['Animation d\'équipe', ['Délégation efficace', 'Gestion des conflits', 'Conduite de réunion']],
        ],
        [['Paris', ['Du 24 au 26 mars 2026', 'Du 6 au 8 octobre 2026']]]
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        9, 'Gestion de projet agile', 'MGT-002', $mgtTheme, 'fleche-titre',
        'Maîtriser les méthodes agiles pour piloter ses projets avec efficacité.',
        '2 jours (14 heures)', '1 590 € HT', ['Inter-entreprises'],
        ['Comprendre les méthodes agiles', 'Animer un sprint', 'Utiliser les outils agiles'],
        ['Chefs de projet', 'Product owners', 'Scrum masters'],
        [
            ['Fondamentaux agiles', ['Scrum et Kanban', 'Rôles et cérémonies', 'User stories et backlog']],
            ['Mise en pratique', ['Sprint planning', 'Rétrospective', 'Outils collaboratifs']],
        ],
        [['Paris', ['Du 14 au 15 avril 2026']], ['Lyon', ['Du 10 au 11 novembre 2026']]],
        true
    )));

    $catalogueData->addPage(new CataloguePage('formation', [], buildFormation(
        10, 'Leadership et prise de décision', 'MGT-003', $mgtTheme, 'fleche-titre',
        'Renforcer son leadership et améliorer sa capacité de décision en situation complexe.',
        '2 jours (14 heures)', '1 790 € HT', ['Inter-entreprises'],
        ['Développer son leadership', 'Prendre des décisions éclairées', 'Inspirer et mobiliser'],
        ['Dirigeants', 'Managers expérimentés'],
        [
            ['Leadership', ['Intelligence émotionnelle', 'Vision et influence', 'Communication inspirante']],
            ['Prise de décision', ['Analyse multicritères', 'Gestion de l\'incertitude', 'Décision collective']],
        ],
        [['Paris', ['Du 19 au 20 mai 2026']]]
    )));

    $builder = new CatalogueBuilder(
        __DIR__ . '/resources/',
        __DIR__ . '/files/' . $data->get('code') . '.pdf',
        $data
    );
    $builder->setCatalogueData($catalogueData);
    $builder->build();

    echo 'Catalogue generated successfully: files/' . $data->get('code') . '.pdf' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

exit();
