<?php

/**
 * Build a Formation PDF
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Utils\Data as jData;
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Data;

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

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setIntro(string $intro): static
    {
        $this->intro = $intro;
        return $this;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;
        return $this;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function setPdfTitle(string $pdfTitle): static
    {
        $this->pdfTitle = $pdfTitle;
        return $this;
    }

    public function setPictoTitle(string $pictoTitle): static
    {
        $this->pictoTitle = $pictoTitle;
        return $this;
    }

    public function setDuree(string $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    /**
     * @param string[] $horaires
     */
    public function setHoraires(array $horaires): static
    {
        $this->horaires = $horaires;
        return $this;
    }

    public function setTarif(string $tarif): static
    {
        $this->tarif = $tarif;
        return $this;
    }

    public function setAnpe(bool $anpe): static
    {
        $this->anpe = $anpe;
        return $this;
    }

    /**
     * @param string[] $types
     */
    public function setTypes(array $types): static
    {
        $this->types = $types;
        return $this;
    }

    public function setCpf(bool $cpf): static
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function setIsNew(bool $isNew): static
    {
        $this->isNew = $isNew;
        return $this;
    }

    /**
     * @param string[] $competences
     */
    public function setCompetences(array $competences): static
    {
        $this->competences = $competences;
        return $this;
    }

    /**
     * @param string[] $profil
     */
    public function setProfil(array $profil): static
    {
        $this->profil = $profil;
        return $this;
    }

    /**
     * @param string[] $prerequis
     */
    public function setPrerequis(array $prerequis): static
    {
        $this->prerequis = $prerequis;
        return $this;
    }

    /**
     * @param string[] $admission
     */
    public function setAdmission(array $admission): static
    {
        $this->admission = $admission;
        return $this;
    }

    /**
     * @param string[] $evaluation
     */
    public function setEvaluation(array $evaluation): static
    {
        $this->evaluation = $evaluation;
        return $this;
    }

    /**
     * @param string[] $methodes
     */
    public function setMethodes(array $methodes): static
    {
        $this->methodes = $methodes;
        return $this;
    }

    /**
     * @param string[] $formateur
     */
    public function setFormateur(array $formateur): static
    {
        $this->formateur = $formateur;
        return $this;
    }

    /**
     * @param string[] $debouches
     */
    public function setDebouches(array $debouches): static
    {
        $this->debouches = $debouches;
        return $this;
    }

    /**
     * @param string[] $codes
     */
    public function setCodes(array $codes): static
    {
        $this->codes = $codes;
        return $this;
    }

    /**
     * @param string[] $more
     */
    public function setMore(array $more): static
    {
        $this->more = $more;
        return $this;
    }

    /**
     * @param string[] $contenu
     */
    public function setContenu(array $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    /**
     * @param FormationProgramme[] $programme
     */
    public function setProgramme(array $programme): static
    {
        $this->programme = $programme;
        return $this;
    }

    public function addProgramme(FormationProgramme $programme): static
    {
        $this->programme[] = $programme;
        return $this;
    }

    /**
     * @param FormationModule[] $modules
     */
    public function setModules(array $modules): static
    {
        $this->modules = $modules;
        return $this;
    }

    public function addModule(FormationModule $module): static
    {
        $this->modules[] = $module;
        return $this;
    }

    /**
     * @param FormationSession[] $sessions
     */
    public function setSessions(array $sessions): static
    {
        $this->sessions = $sessions;
        return $this;
    }

    public function addSession(FormationSession $session): static
    {
        $this->sessions[] = $session;
        return $this;
    }
}

class FormationBuilder extends Builder
{
    protected FormationData $formationData;

    public function setFormationData(FormationData $formationData): static
    {
        $this->formationData = $formationData;
        return $this;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle($this->formationData->pdfTitle);
        $pdf->SetSubject('Fiche formation');
        $pdf->SetCreator('JDZ Formation');
        $pdf->SetAuthor('JDZ Formation');
        $pdf->SetKeywords('jdz, formation, catalogue');
        $pdf->set('izPageNoShow', false);
        $pdf->set('izSourcesPath', $this->sourcesPath);

        $pdf->set('font', (object)[
            'name' => 'montserrat',
            'styles' => [null, 'B', 'I', 'BI'],
        ]);

        $pdf->set('font', (object)[
            'name' => 'montserratblack',
            'styles' => [null, 'I'],
        ]);

        $pdf->set('font', (object)[
            'name' => 'montserratmedium',
            'styles' => [null, 'B'],
        ]);

        $pdf->set('font', (object)[
            'name' => 'montserratlight',
            'styles' => [null, 'I'],
        ]);

        $modelizerData = new Data();
        $modelizerData->sets($this->loadModelizerConfig('formation'));

        $fontDefault = $modelizerData->get('fontDefault');

        $modelizerData->set('h1Font', [$fontDefault, 'B']);
        $modelizerData->set('h2Font', [$fontDefault, 'B']);
        $modelizerData->set('h3Font', [$fontDefault, 'B']);
        $modelizerData->set('h4Font', [$fontDefault, 'B']);
        $modelizerData->set('h5Font', [$fontDefault, 'B']);
        $modelizerData->set('h6Font', [$fontDefault, 'B']);
        $modelizerData->set('pFont', [$fontDefault, '']);
        $modelizerData->set('liFont', [$fontDefault, '']);

        $modelizerData->set('h1Color', $this->formationData->theme);
        $modelizerData->set('h2Color', $this->formationData->theme);
        $modelizerData->set('h3Color', $this->formationData->theme);
        $modelizerData->set('h4Color', $this->formationData->theme);
        $modelizerData->set('h5Color', $this->formationData->theme);
        $modelizerData->set('h6Color', $this->formationData->theme);

        $modelizerData->set('h1', $this->formationData->theme);
        $modelizerData->set('h2', $this->formationData->theme);
        $modelizerData->set('bullets', $this->formationData->theme);

        $modelizerData->set('formation', $this->formationData);

        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }

        \set_time_limit(240);

        $modelizer = new FormationModelizer($pdf, $modelizerData);
        $modelizer->load();
        $modelizer->toPdf();

        $pdf->Output($this->targetPath, 'F');

        if (!file_exists($this->targetPath)) {
            throw new \Exception('Impossible de créer la fiche.');
        }
    }
}

class FormationModelizer extends Modelizer
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

        $bulletContenOffset = $this->helper->px2mm(12);
        $textLeftOffset = $this->helper->px2mm(30);
        $bulletLeftOffset = $textLeftOffset - $bulletContenOffset;

        $sideInfosW = $this->helper->px2mm(126);
        $sideInfosH = $this->helper->px2mm(160);
        $sideInfosX = $leftX + $this->data->get('availableWidth') - $sideInfosW;
        $mainTitleW = $this->data->get('availableWidth') - $sideInfosW - $this->helper->px2mm(16);
        $mainTitleH = $sideInfosH * 0.4;
        $introY     = $topY + $mainTitleH + $this->helper->px2mm(14);
        $footerH    = $this->helper->px2mm(72);
        $footerY    = $topY + $this->data->get('availableHeight') - $footerH;
        $contentsY  = $topY + $sideInfosH + $this->helper->px2mm(20);
        $contentsMaxH  = $this->data->get('availableHeight') - $contentsY - $footerH;
        $this->data->set('contentsMaxHeight', $contentsMaxH);
        $this->data->set('contentsTopOffset', $contentsY);
        $this->data->set('contentsMaxTopOffset', $footerY);

        $footerLogosH = 8;
        $footerLogosM = $this->helper->px2mm(9);
        $footerLogosW = $footerLogosM;
        $footerLogosX = $leftX + $this->data->get('availableWidth') - $footerLogosW;
        $footerLogosY = $footerY + $this->helper->px2mm(10);

        // picto title
        $pictoTitleDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/' . $formation->pictoTitle . '.jpg', 16, 16);
        $this->pdf->Image($pictoTitleDims['path'], $this->helper->px2mm(15), $topY + $this->helper->px2mm(8), $pictoTitleDims['w'], $pictoTitleDims['h']);

        // title
        $titleArray = $this->explodeTitle($formation->title);

        $titleX = $leftX;
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

        // types
        $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-type-full.jpg', null, 7);
        $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
        $infosYTypes = 1 === count($formation->types) ? $infosY + $this->helper->px2mm(5) : $infosY;
        foreach ($formation->types as $formationType) {
            $this->pdf->Text($sideInfosXText, $infosYTypes, $formationType);
            $infosYTypes += $this->helper->px2mm(9);
        }

        // ref
        $infosY += $this->helper->px2mm(27);
        $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-ref-full.jpg', null, 7);
        $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
        $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(5), $formation->ref);

        // duree
        if ($formation->duree) {
            $infosY += $this->helper->px2mm(27);
            $pictoDims = $this->helper->imageSize($this->pdf->get('izSourcesPath') . 'images/picto-duree-full.jpg', null, 7);
            $this->pdf->Image($pictoDims['path'], $sideInfosX, $infosY, $pictoDims['w'], $pictoDims['h']);
            $this->pdf->Text($sideInfosXText, $infosY + $this->helper->px2mm(5), $formation->duree);
        }

        // horaires
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

        // tarif
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

        // footer texts
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
        if (true === $this->pdf->get('izPageNoShow')) {
            $this->pdf->setFontSpacing(0.056);
        } else {
            $this->pdf->setFontSpacing(0.086);
        }
        $this->pdf->MultiCell($this->data->get('pageWidth'), $this->helper->px2mm(9), $footerText, 0, 'L', false, 1, $leftX, $currentFooterY);
        $this->pdf->setFontSpacing(0);

        $currentFooterY += $this->helper->px2mm(9);

        $footerText = "JDZ Formation \xE2\x80\x93 Organisme de formation professionnelle \xE2\x80\x93 www.jdz-formation.fr";
        $this->pdf->SetTextColorArray($this->lightColorArray('textColor', 20));
        $this->pdf->SetFont($this->data->get('fontLight'), '', 6);
        if (true === $this->pdf->get('izPageNoShow')) {
            $this->pdf->setFontSpacing(0.056);
        } else {
            $this->pdf->setFontSpacing(0.086);
        }
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
        $this->checkSideAndAddContentBloc('Compétences visées', $formation->competences, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc('Public concerné', $formation->profil, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc('Prérequis', $formation->prerequis, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Modalités d\xE2\x80\x99admission", $formation->admission, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Modalités d\xE2\x80\x99évaluation", $formation->evaluation, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc('Méthodes et outils pédagogiques', $formation->methodes, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc("Profil de l\xE2\x80\x99intervenant", $formation->formateur, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc('Débouchés', $formation->debouches, $contentW, $contentX, $contentY);
        $this->checkSideAndAddContentBloc('Informations complémentaires', $formation->more, $contentW, $contentX, $contentY);

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
                $suffix = $m->nbJours > 0
                    ? ' (' . ($m->nbJours > 1 ? $m->nbJours . ' jours' : '1 jour') . ')'
                    : '';
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
        if (!$content) {
            return;
        }

        $wTry = $w;
        $xTry = $x;
        $yTry = 0;

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

        if (count($titleArray) > 3) {
            return $this->explodeTitle($title, $lineLength + 3);
        }

        foreach ($titleArray as $titleLine) {
            if (mb_strlen($titleLine) < 3) {
                return $this->explodeTitle($title, $lineLength + 2);
            }
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

require_once __DIR__ . '/autoload.php';

try {
    $data = new jData();
    $data->sets([
        'code' => 'formation-1',
    ]);

    $formationData = new FormationData();

    $formationData
        ->setId(42)
        ->setTitle('Maîtriser les fondamentaux du marketing digital')
        ->setIntro('Une formation complète pour acquérir les compétences essentielles du marketing digital et développer une stratégie efficace.')
        ->setRef('MKT-2026-001')
        ->setTheme('#01a6ba')
        ->setPdfTitle('Formation Marketing Digital')
        ->setPictoTitle('fleche-stage')
        ->setDuree('5 jours (35 heures)')
        ->setHoraires(['9h00 - 12h30', '14h00 - 17h30'])
        ->setTarif('2 490 € HT')
        ->setAnpe(true)
        ->setTypes(['Inter-entreprises', 'Intra-entreprise'])
        ->setCpf(true)
        ->setIsNew(true)
        ->setCompetences([
            'Définir une stratégie de marketing digital',
            'Maîtriser les outils de référencement (SEO/SEA)',
            'Gérer des campagnes sur les réseaux sociaux',
            'Analyser les performances avec Google Analytics',
        ])
        ->setProfil([
            'Responsables marketing et communication',
            'Chefs de projet digital',
            'Entrepreneurs souhaitant développer leur présence en ligne',
        ])
        ->setPrerequis([
            'Connaissances de base en marketing',
            'Pratique courante d\'Internet et des outils bureautiques',
        ])
        ->setAdmission([
            'Entretien préalable avec le responsable pédagogique',
            'Questionnaire de positionnement',
        ])
        ->setEvaluation([
            'QCM en fin de chaque module',
            'Étude de cas pratique en fin de formation',
            'Attestation de compétences délivrée en fin de parcours',
        ])
        ->setMethodes([
            'Apports théoriques et méthodologiques',
            'Exercices pratiques sur des cas réels',
            'Travaux en sous-groupes',
            'Accès à une plateforme e-learning pendant 3 mois',
        ])
        ->setFormateur([
            'Expert en marketing digital avec 10 ans d\'expérience',
            'Consultant et formateur certifié',
        ])
        ->setDebouches([
            'Chef de projet digital',
            'Responsable marketing digital',
            'Consultant en stratégie digitale',
        ])
        ->setCodes(['RS5678', 'RNCP34567'])
        ->setMore([
            'Formation éligible au CPF',
            'Possibilité de passer la certification RS5678',
        ])
        ->setContenu([])
        ->addProgramme(new FormationProgramme(
            'Module 1 : Les fondamentaux du marketing digital',
            [
                'L\'écosystème digital et ses acteurs',
                'Les leviers du marketing digital',
                'Définir ses objectifs et KPI',
            ],
        ))
        ->addProgramme(new FormationProgramme(
            'Module 2 : Référencement et visibilité',
            [
                'Optimisation SEO on-page et off-page',
                'Campagnes Google Ads (SEA)',
                'Content marketing et stratégie éditoriale',
            ],
        ))
        ->addProgramme(new FormationProgramme(
            'Module 3 : Réseaux sociaux et e-réputation',
            [
                'Stratégie social media',
                'Community management',
                'Publicité sur les réseaux sociaux',
                'Gestion de l\'e-réputation',
            ],
        ))
        ->addProgramme(new FormationProgramme(
            'Module 4 : Analytics et pilotage',
            [
                'Google Analytics : installation et configuration',
                'Analyse des données et tableaux de bord',
                'Optimisation des conversions',
            ],
        ))
        ->setModules([])
        ->addSession(new FormationSession(
            'Paris',
            ['Du 15 au 19 mars 2026', 'Du 22 au 26 juin 2026', 'Du 14 au 18 septembre 2026'],
        ))
        ->addSession(new FormationSession(
            'Lyon',
            ['Du 6 au 10 avril 2026', 'Du 5 au 9 octobre 2026'],
        ));

    $builder = new FormationBuilder(
        __DIR__ . '/resources/',
        __DIR__ . '/files/' . $data->get('code') . '.pdf',
        $data
    );
    $builder->setFormationData($formationData);
    $builder->build();

    echo 'Formation generated successfully: files/' . $data->get('code') . '.pdf' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

exit();
