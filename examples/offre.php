<?php

/**
 * Build an Offer PDF
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Utils\Data as jData;
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Data;

class OffreCompany
{
    public function __construct(
        public string $name = '',
        public string $description = '',
    ) {}
}

class OffreContact
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $url = '',
    ) {}
}

class OffreLocation
{
    public function __construct(
        public string $town = '',
        public string $region = '',
    ) {}
}

class OffreImage
{
    public function __construct(
        public string $path = '',
    ) {}
}

class OffreData
{
    public string $theme = '';
    public string $jobtitle = '';
    public string $jobtype = '';
    public bool $fulltime = false;
    public string $reference = '';
    public string $startDate = '';
    public string $duration = '';
    public string $experience = '';
    public string $salary = '';
    public OffreCompany $company;
    public ?OffreImage $logoImage = null;
    public OffreContact $contact;
    public OffreLocation $location;
    /** @var string[] */
    public array $metiers = [];
    public string $description = '';
    public string $profile = '';
    /** @var string[] */
    public array $missions = [];
    /** @var string[] */
    public array $formations = [];
    /** @var string[] */
    public array $languages = [];
    /** @var string[] */
    public array $competences = [];

    public function __construct()
    {
        $this->company = new OffreCompany();
        $this->contact = new OffreContact();
        $this->location = new OffreLocation();
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function setJobtitle(string $jobtitle): static
    {
        $this->jobtitle = $jobtitle;
        return $this;
    }

    public function setJobtype(string $jobtype): static
    {
        $this->jobtype = $jobtype;
        return $this;
    }

    public function setFulltime(bool $fulltime): static
    {
        $this->fulltime = $fulltime;
        return $this;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function setStartDate(string $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function setExperience(string $experience): static
    {
        $this->experience = $experience;
        return $this;
    }

    public function setSalary(string $salary): static
    {
        $this->salary = $salary;
        return $this;
    }

    public function setCompany(OffreCompany $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function setLogoImage(?OffreImage $logoImage): static
    {
        $this->logoImage = $logoImage;
        return $this;
    }

    public function setContact(OffreContact $contact): static
    {
        $this->contact = $contact;
        return $this;
    }

    public function setLocation(OffreLocation $location): static
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @param string[] $metiers
     */
    public function setMetiers(array $metiers): static
    {
        $this->metiers = $metiers;
        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setProfile(string $profile): static
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @param string[] $missions
     */
    public function setMissions(array $missions): static
    {
        $this->missions = $missions;
        return $this;
    }

    /**
     * @param string[] $formations
     */
    public function setFormations(array $formations): static
    {
        $this->formations = $formations;
        return $this;
    }

    /**
     * @param string[] $languages
     */
    public function setLanguages(array $languages): static
    {
        $this->languages = $languages;
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
}

class OffreBuilder extends Builder
{
    protected OffreData $offreData;

    public function setOffreData(OffreData $offreData): static
    {
        $this->offreData = $offreData;
        return $this;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle($this->offreData->jobtitle);
        $pdf->SetSubject('Offre');
        $pdf->SetCreator('Jobboard');
        $pdf->SetAuthor('Jobboard Offre');
        $pdf->SetKeywords('jobboard, offre, emploi');
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
        $modelizerData->sets($this->loadModelizerConfig('offre'));

        $fontDefault = $modelizerData->get('fontDefault');

        $modelizerData->set('h1Font', [$fontDefault, 'B']);
        $modelizerData->set('h2Font', [$fontDefault, 'B']);
        $modelizerData->set('h3Font', [$fontDefault, 'B']);
        $modelizerData->set('h4Font', [$fontDefault, 'B']);
        $modelizerData->set('h5Font', [$fontDefault, 'B']);
        $modelizerData->set('h6Font', [$fontDefault, 'B']);
        $modelizerData->set('pFont', [$fontDefault, '']);
        $modelizerData->set('liFont', [$fontDefault, '']);

        $modelizerData->set('h1Color', $this->offreData->theme);
        $modelizerData->set('h2Color', $this->offreData->theme);
        $modelizerData->set('h3Color', $this->offreData->theme);
        $modelizerData->set('h4Color', $this->offreData->theme);
        $modelizerData->set('h5Color', $this->offreData->theme);
        $modelizerData->set('h6Color', $this->offreData->theme);
        $modelizerData->set('hrColor', $this->offreData->theme);

        $modelizerData->set('theme', $this->offreData->theme);
        $modelizerData->set('h1', $this->offreData->theme);
        $modelizerData->set('h2', $this->offreData->theme);
        $modelizerData->set('bullets', $this->offreData->theme);

        $modelizerData->set('offre', $this->offreData);

        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }

        \set_time_limit(240);

        $modelizer = new OffreModelizer($pdf, $modelizerData);
        $modelizer->load();
        $modelizer->toPdf();

        $pdf->Output($this->targetPath, 'F');

        if (!file_exists($this->targetPath)) {
            throw new \Exception('Impossible de créer la fiche.');
        }
    }
}

class OffreModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;

    public function load(): void
    {
        parent::load();

        $top    = $this->helper->px2mm(50);
        $right  = $this->helper->px2mm(40);
        $left   = $this->helper->px2mm(40);
        $bottom = $this->helper->px2mm(50);

        $columnsGap = $this->helper->px2mm(20);

        $pageWidth = $this->pdf->getPageWidth();
        $pageWidth -= $left;
        $pageWidth -= $right;

        $leftColWidth = $pageWidth * 60 / 100;
        $rightColWidth = $pageWidth - $columnsGap - $leftColWidth;
        $rightColOffset = $left + $leftColWidth + $columnsGap;

        $this->pdf->SetMargins($left, $top, $right); // left / top / right
        $this->pdf->SetAutoPageBreak(true, $bottom);

        $this->data
            ->set('pageWidth', $pageWidth)
            ->set('leftColWidth', $leftColWidth)
            ->set('rightColWidth', $rightColWidth)
            ->set('rightColOffset', $rightColOffset);
    }

    protected function headerBloc(int|float $y): int|float
    {
        $offre = $this->data->get('offre');

        $w = $this->data->get('leftColWidth');
        $x = $this->pdf->getX();

        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 14);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->data->toUppercase($offre->company->name), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();
        $y += $this->helper->px2mm(10);

        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->jobtype . ', ' . ($offre->fulltime ? ' Temps plein' : 'Temps partiel'), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();
        $y += $this->helper->px2mm(8);

        $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 12);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->data->toUppercase($offre->jobtitle), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();

        if ($offre->reference) {
            $y += $this->helper->px2mm(8);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->reference, 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
        }

        $y += $this->helper->px2mm(12);

        $this->pdf->Line($x, $y, $x + $this->data->get('availableWidth'), $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('hrColor')));

        return $y;
    }

    protected function imageBloc(int|float $y): void
    {
        $offre = $this->data->get('offre');

        if ($offre->logoImage && $offre->logoImage->path) {
            $picDims = $this->helper->imageSize($offre->logoImage->path, 40, 40);

            $x = $this->pdf->getX();
            $x += $this->data->get('availableWidth');
            $x -= $picDims['w'];

            $y -= $picDims['h'];
            $y += $this->helper->px2mm(3.5);

            $this->pdf->Image($picDims['path'], $x, $y, $picDims['w'], $picDims['h']);
        }
    }

    protected function rightBloc(int|float $y): int|float
    {
        $offre = $this->data->get('offre');

        $x = $this->data->get('rightColOffset');
        $w = $this->data->get('rightColWidth');

        if ($offre->startDate) {
            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Poste à pourvoir à partir du <strong>' . $offre->startDate . '</strong>', 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        if ($offre->duration) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Durée de la mission', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(4);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->duration, 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        if ($offre->experience) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Expérience', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(4);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->experience, 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        if ($offre->salary) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Salaire', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(4);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->salary, 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        if ($offre->location) {
            if ($offre->location->town) {
                $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
                $this->pdf->SetTextColorArray($this->data->getColor('theme'));
                $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Lieu du poste', 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(4);

                $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
                $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
                $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->location->town, 0, 1, false, true, '', true);

                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(10);
            }

            if ($offre->location->region) {
                $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
                $this->pdf->SetTextColorArray($this->data->getColor('theme'));
                $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Région', 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(4);

                $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
                $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
                $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->location->region, 0, 1, false, true, '', true);

                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(10);
            }
        }

        if ($offre->metiers) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Métier', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, implode(' / ', $offre->metiers), 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Personne à contacter', 0, 1, false, true, '', true);
        $y = $this->pdf->getY();
        $y += $this->helper->px2mm(4);

        $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

        if ($offre->contact->name) {
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $offre->contact->name, 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(4);
        }

        if ($offre->contact->email) {
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));

            $this->pdf->writeHTMLCell($w, 0, $x, $y, '<a href="mailto:' . $offre->contact->email . '">' . mb_strimwidth($offre->contact->email, 0, 42, '..') . '</a>', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(4);

            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        }

        if ($offre->contact->url) {
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));

            $this->pdf->writeHTMLCell($w, 0, $x, $y, '<a href="' . $offre->contact->url . '">' . mb_strimwidth($offre->contact->url, 0, 42, '..') . '</a>', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(4);

            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        }

        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'CV et lettre de motivation requis', 0, 1, false, true, '', true);

        $y = $this->pdf->getY();
        $y += $this->helper->px2mm(10);

        return $y;
    }

    protected function leftBloc(int|float $y): int|float
    {
        $offre = $this->data->get('offre');

        $x = $this->pdf->getX();
        $w = $this->data->get('leftColWidth');
        $x2 = $x + $this->helper->px2mm(10);

        if ($offre->description) {
            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->liToDash($offre->description), 0, 1, false, true, 'L', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(12);

            $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('hr2')));

            $y += $this->helper->px2mm(12);
        }

        if ($offre->profile) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Profil recherché', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->liToDash($offre->profile), 0, 1, false, true, 'L', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(12);
        }

        if ($offre->missions) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Vos missions', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            foreach ($offre->missions as $li) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $li, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        if ($offre->formations) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Formation', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            foreach ($offre->formations as $li) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $li, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        if ($offre->languages) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Langues', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            foreach ($offre->languages as $li) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $li, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        if ($offre->competences) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Compétences requises', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            foreach ($offre->competences as $li) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $li, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        return $y;
    }

    protected function bottomBloc(int|float $y): int|float
    {
        $offre = $this->data->get('offre');

        $x = $this->pdf->getX();
        $w = $this->data->get('availableWidth');

        if ($offre->company->description) {
            $this->pdf->Line($x, $y, $x + $this->data->get('availableWidth'), $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('hrColor')));

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(20);

            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Un peu plus sur ' . $offre->company->name, 0, 1, false, true, 'L', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->liToDash($offre->company->description), 0, 1, false, true, 'L', true);
            $y = $this->pdf->getY();
        }

        return $y;
    }

    public function Page(): void
    {
        parent::Page();

        $offre = $this->data->get('offre');

        $x = $this->pdf->getX();
        $y = $this->pdf->getY();

        $y = $this->headerBloc($y);

        $this->imageBloc($y);

        $y += $this->helper->px2mm(12);
        $this->pdf->setY($y, false);
        $this->pdf->setX($x);

        $y1 = $this->rightBloc($y);
        $y2 = $this->leftBloc($y);

        $y = max($y1, $y2);
        $this->pdf->setY($y);

        $this->bottomBloc($y);
    }
}

require_once __DIR__ . '/autoload.php';

try {
    $data = new jData();
    $data->sets([
        'code' => 'offre-1',
    ]);

    $offreData = new OffreData();

    $offreData->setTheme('#fab600')
        ->setJobtitle('Développeur PHP/Symfony Senior')
        ->setJobtype('CDI')
        ->setFulltime(true)
        ->setReference('REF-2026-001')
        ->setStartDate('01/04/2026')
        ->setDuration('')
        ->setExperience('5 ans minimum')
        ->setSalary('55 000 - 65 000 brut annuel')
        ->setCompany(new OffreCompany(
            'Tech Solutions SAS',
            '<p>Tech Solutions est une entreprise innovante spécialisée dans le développement de solutions digitales pour les grandes entreprises. Fondée en 2010, nous comptons plus de 200 collaborateurs répartis sur 3 sites en France.</p><p>Notre culture favorise l\'innovation, le partage de connaissances et l\'évolution professionnelle.</p>',
        ))
        ->setLogoImage(null)
        ->setContact(new OffreContact(
            'Marie Martin - RH',
            'recrutement@techsolutions.fr',
            'https://www.techsolutions.fr/carrieres',
        ))
        ->setLocation(new OffreLocation('Paris', 'Île-de-France'))
        ->setMetiers(['Développement Web', 'Informatique'])
        ->setDescription('<p>Nous recherchons un développeur PHP/Symfony senior pour rejoindre notre équipe technique. Vous serez en charge du développement et de la maintenance de nos applications web, dans un environnement agile et collaboratif.</p>')
        ->setProfile('<p>Passionné(e) par le développement web, vous disposez d\'une solide expérience en PHP/Symfony. Autonome, rigoureux(se) et collaboratif(ve).</p>')
        ->setMissions([
            'Concevoir et développer de nouvelles fonctionnalités',
            'Maintenir et optimiser les applications existantes',
            'Participer aux revues de code et au mentoring',
            'Contribuer aux pratiques de développement',
            'Collaborer avec les équipes produit et design',
        ])
        ->setFormations([
            'Bac+5 en informatique ou équivalent',
            'Formation continue appréciée',
        ])
        ->setLanguages([
            'Français (courant)',
            'Anglais (professionnel)',
        ])
        ->setCompetences([
            'PHP 8+ / Symfony 6+',
            'MySQL / PostgreSQL',
            'Docker / Kubernetes',
            'CI/CD (GitLab CI, GitHub Actions)',
            'API REST / GraphQL',
            'Tests unitaires et fonctionnels',
        ]);

    $builder = new OffreBuilder(
        __DIR__ . '/resources/',
        __DIR__ . '/files/' . $data->get('code') . '.pdf',
        $data
    );
    $builder->setOffreData($offreData);
    $builder->build();

    echo 'Offre generated successfully: files/' . $data->get('code') . '.pdf' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

exit();
