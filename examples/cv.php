<?php

/**
 * Build a CV
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Utils\Data as jData;
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Data;

class CvContact
{
    public function __construct(
        public string $email = '',
        public string $phone = '',
    ) {}
}

class CvImage
{
    public function __construct(
        public string $path = '',
    ) {}
}

class CvDiploma
{
    public function __construct(
        public string $diploma = '',
        public string $school = '',
        public string $promotion = '',
    ) {}
}

class CvExperience
{
    public function __construct(
        public string $jobtitle = '',
        public string $company = '',
        public string $dates = '',
        public string $summary = '',
    ) {}
}

class CvData
{
    public string $name = '';
    public string $jobtitle = '';
    /** @var string[] */
    public array $jobTypes = [];
    public bool $fulltime = false;
    /** @var string[] */
    public array $regions = [];
    public string $dispoDate = '';
    public ?CvImage $pictureImage = null;
    public CvContact $contact;
    public string $summary = '';
    public string $description = '';
    /** @var string[] */
    public array $competences = [];
    /** @var CvExperience[] */
    public array $experiences = [];
    /** @var CvDiploma[] */
    public array $diplomas = [];
    /** @var string[] */
    public array $languages = [];
    /** @var string[] */
    public array $links = [];
    /** @var string[] */
    public array $certifications = [];

    public function __construct()
    {
        $this->contact = new CvContact();
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setJobtitle(string $jobtitle): static
    {
        $this->jobtitle = $jobtitle;
        return $this;
    }

    /**
     * @param string[] $jobTypes
     */
    public function setJobTypes(array $jobTypes): static
    {
        $this->jobTypes = $jobTypes;
        return $this;
    }

    public function setFulltime(bool $fulltime): static
    {
        $this->fulltime = $fulltime;
        return $this;
    }

    /**
     * @param string[] $regions
     */
    public function setRegions(array $regions): static
    {
        $this->regions = $regions;
        return $this;
    }

    public function setDispoDate(string $dispoDate): static
    {
        $this->dispoDate = $dispoDate;
        return $this;
    }

    public function setPictureImage(?CvImage $pictureImage): static
    {
        $this->pictureImage = $pictureImage;
        return $this;
    }

    public function setContact(CvContact $contact): static
    {
        $this->contact = $contact;
        return $this;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
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
     * @param CvExperience[] $experiences
     */
    public function setExperiences(array $experiences): static
    {
        $this->experiences = $experiences;
        return $this;
    }

    public function addExperience(CvExperience $experience): static
    {
        $this->experiences[] = $experience;
        return $this;
    }

    /**
     * @param CvDiploma[] $diplomas
     */
    public function setDiplomas(array $diplomas): static
    {
        $this->diplomas = $diplomas;
        return $this;
    }

    public function addDiploma(CvDiploma $diploma): static
    {
        $this->diplomas[] = $diploma;
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
     * @param string[] $links
     */
    public function setLinks(array $links): static
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @param string[] $certifications
     */
    public function setCertifications(array $certifications): static
    {
        $this->certifications = $certifications;
        return $this;
    }
}

class CvBuilder extends Builder
{
    protected CvData $cvData;

    public function setCvData(CvData $cvData): static
    {
        $this->cvData = $cvData;
        return $this;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle($this->data->get('label'));
        $pdf->SetSubject('CV');
        $pdf->SetCreator('Me');
        $pdf->SetAuthor('Me');
        $pdf->SetKeywords('jobboard, curriculum vitae, cv, candidat');
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
        $modelizerData->sets($this->loadModelizerConfig('cv'));

        $fontDefault = $modelizerData->get('fontDefault');
        $theme = $this->data->get('theme');

        $modelizerData->set('h1Font', [$fontDefault, 'B']);
        $modelizerData->set('h2Font', [$fontDefault, 'B']);
        $modelizerData->set('h3Font', [$fontDefault, 'B']);
        $modelizerData->set('h4Font', [$fontDefault, 'B']);
        $modelizerData->set('h5Font', [$fontDefault, 'B']);
        $modelizerData->set('h6Font', [$fontDefault, 'B']);
        $modelizerData->set('pFont', [$fontDefault, '']);
        $modelizerData->set('liFont', [$fontDefault, '']);

        $modelizerData->set('h1Color', $theme);
        $modelizerData->set('h2Color', $theme);
        $modelizerData->set('h3Color', $theme);
        $modelizerData->set('h4Color', $theme);
        $modelizerData->set('h5Color', $theme);
        $modelizerData->set('h6Color', $theme);
        $modelizerData->set('hrColor', $theme);

        $modelizerData->set('theme', $theme);
        $modelizerData->set('h1', $theme);
        $modelizerData->set('h2', $theme);
        $modelizerData->set('bullets', $theme);

        $modelizerData->set('cv', $this->cvData);

        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }

        \set_time_limit(240);

        $modelizer = new CvModelizer($pdf, $modelizerData);
        $modelizer->load();
        $modelizer->toPdf();

        $pdf->Output($this->targetPath, 'F');

        if (!file_exists($this->targetPath)) {
            throw new \Exception('Impossible de créer la fiche.');
        }
    }
}

class CvModelizer extends Modelizer
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
        $cv = $this->data->get('cv');

        $w = $this->data->get('leftColWidth');
        $x = $this->pdf->getX();

        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 14);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->data->toUppercase($cv->name), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();

        $y += $this->helper->px2mm(14);

        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, implode(' / ', $cv->jobTypes) . ', ' . ($cv->fulltime ? ' Temps plein' : 'Temps partiel'), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();

        $y += $this->helper->px2mm(8);

        $this->pdf->SetFont($this->data->get('fontDefault'), 'B', 10);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $this->data->toUppercase($cv->jobtitle), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();

        $y += $this->helper->px2mm(8);

        $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Région: ' . implode(', ', $cv->regions), 0, 1, false, true, '', true);

        $y = $this->pdf->getY();

        if ($cv->dispoDate) {
            $y += $this->helper->px2mm(8);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Disponibilité: ' . $cv->dispoDate, 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
        }

        $y += $this->helper->px2mm(12);

        $this->pdf->Line($x, $y, $x + $this->data->get('availableWidth'), $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('hrColor')));

        return $y;
    }

    protected function imageBloc(int|float $y): void
    {
        $cv = $this->data->get('cv');

        if (!$cv->pictureImage) {
            return;
        }

        $boxW = 35;
        $boxH = 35;

        $boxX = $this->pdf->getX() + $this->data->get('availableWidth') - $boxW;
        $boxY = $y;

        // Draw border rectangle
        $this->pdf->SetLineStyle([
            'width' => $this->data->getBorderWidth(0.5),
            'color' => $this->data->getColor('grayish'),
        ]);
        $this->pdf->Rect($boxX, $boxY, $boxW, $boxH);

        // Fit image inside the box preserving aspect ratio
        list($imgW, $imgH) = \getimagesize($cv->pictureImage->path);
        $ratio = $imgW / $imgH;

        $pad = 1;
        $innerW = $boxW - $pad * 2;
        $innerH = $boxH - $pad * 2;

        if ($ratio >= 1) {
            $drawW = $innerW;
            $drawH = $innerW / $ratio;
        } else {
            $drawH = $innerH;
            $drawW = $innerH * $ratio;
        }

        // Center inside box
        $imgX = $boxX + ($boxW - $drawW) / 2;
        $imgY = $boxY + ($boxH - $drawH) / 2;

        $this->pdf->Image($cv->pictureImage->path, $imgX, $imgY, $drawW, $drawH);
    }

    protected function rightBloc(int|float $y): int|float
    {
        $cv = $this->data->get('cv');

        $x = $this->data->get('rightColOffset');
        $w = $this->data->get('rightColWidth');

        if ($cv->contact->email) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Email', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, '<a href="mailto:' . $cv->contact->email . '">' . $cv->contact->email . '</a>', 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        if ($cv->contact->phone) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Téléphone', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $cv->contact->phone, 0, 1, false, true, '', true);

            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(10);
        }

        if ($cv->diplomas) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Diplômes', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            foreach ($cv->diplomas as $diploma) {
                $this->pdf->writeHTMLCell($w, 0, $x, $y, '<strong>' . $diploma->diploma . '</strong><br />' . $diploma->school, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);

                if ($diploma->promotion) {
                    $this->pdf->SetFont($this->data->get('fontDefault'), '', 8);
                    $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Promotion ' . $diploma->promotion, 0, 1, false, true, '', true);
                    $y = $this->pdf->getY();
                    $y += $this->helper->px2mm(2);
                }

                $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
                $y += $this->helper->px2mm(4);
            }

            $y += $this->helper->px2mm(6);
        }

        if ($cv->languages) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Langues', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $x2 = $x + $this->helper->px2mm(10);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            foreach ($cv->languages as $language) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $language, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(8);
        }

        if ($cv->links) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Liens', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $x2 = $x + $this->helper->px2mm(10);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            foreach ($cv->links as $link) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $link, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        return $y;
    }

    protected function leftBloc(int|float $y): int|float
    {
        $cv = $this->data->get('cv');

        $x = $this->pdf->getX();
        $w = $this->data->get('leftColWidth');

        if ($cv->summary) {
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $cv->summary, 0, 1, false, true, 'J', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(12);

            $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('hr2')));

            $y += $this->helper->px2mm(12);
        }

        if ($cv->description) {
            $this->pdf->writeHTMLCell($w, 0, $x, $y, $cv->description, 0, 1, false, true, 'J', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(12);
        }

        if ($cv->competences) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Compétences', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $x2 = $x + $this->helper->px2mm(10);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            foreach ($cv->competences as $competence) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $competence, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        if ($cv->experiences) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Expérience', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            foreach ($cv->experiences as $experience) {
                $this->pdf->writeHTMLCell($w, 0, $x, $y, '<strong>' . $experience->jobtitle . '</strong> ' . ($experience->company ? ' - ' . $experience->company : ''), 0, 1, false, true, '', true);
                $y = $this->pdf->getY();

                $this->pdf->SetFont($this->data->get('fontDefault'), '', 8);
                $this->pdf->writeHTMLCell($w, 0, $x, $y, $experience->dates, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);

                $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);

                if ($experience->summary) {
                    $this->pdf->writeHTMLCell($w, 0, $x, $y, $experience->summary, 0, 1, false, true, '', true);
                    $y = $this->pdf->getY();
                    $y += $this->helper->px2mm(2);
                }

                $y += $this->helper->px2mm(6);
            }

            $y += $this->helper->px2mm(10);
        }

        if ($cv->certifications) {
            $this->pdf->SetFont($this->data->get('fontDark'), 'B', 11);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Certifications', 0, 1, false, true, '', true);
            $y = $this->pdf->getY();
            $y += $this->helper->px2mm(6);

            $this->pdf->SetFont($this->data->get('fontDefault'), '', 10);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

            $x2 = $x + $this->helper->px2mm(10);

            foreach ($cv->certifications as $certification) {
                $this->pdf->writeHTMLCell($w, 0, $x2, $y, ' - ' . $certification, 0, 1, false, true, '', true);
                $y = $this->pdf->getY();
                $y += $this->helper->px2mm(2);
            }

            $y += $this->helper->px2mm(10);
        }

        return $y;
    }

    public function Page(): void
    {
        parent::Page();

        $cv = $this->data->get('cv');

        $x = $this->pdf->getX();
        $y = $this->pdf->getY();

        $this->imageBloc($y);

        $y = $this->headerBloc($y);

        $y += $this->helper->px2mm(12);
        $this->pdf->setY($y, false);
        $this->pdf->setX($x);

        $this->rightBloc($y);
        $this->leftBloc($y);
    }
}

require_once realpath(__DIR__ . '/autoload.php');

try {
    $data = new jData();

    $data->sets([
        'code' => 'cv-1',
        'theme' => '#fab600',
        'label' => 'CV de test',
    ]);

    $cvData = new CvData();

    $cvData->setName('Jean Dupont')
        ->setJobtitle('Développeur Web Full Stack')
        ->setJobTypes(['CDI', 'CDD'])
        ->setFulltime(true)
        ->setRegions(['Île-de-France', 'Provence-Alpes-Côte d\'Azur'])
        ->setDispoDate('01/03/2026')
        ->setPictureImage(new CvImage(__DIR__ . '/resources/images/cv-photo.jpg'))
        ->setContact(new CvContact('jean.dupont@email.com', '06 12 34 56 78'))
        ->setSummary('<p>Développeur web passionné avec 8 ans d\'expérience dans la conception et le développement d\'applications web modernes. Spécialisé en PHP/Symfony et React.</p>')
        ->setDescription('<p>Fort d\'une solide expérience en développement full stack, je maîtrise les architectures modernes et les méthodologies agiles. Je suis à la recherche de nouveaux défis techniques au sein d\'une équipe dynamique.</p>')
        ->setCompetences([
            'PHP / Symfony',
            'JavaScript / React',
            'MySQL / PostgreSQL',
            'Docker / CI-CD',
            'API REST',
            'Git',
        ])
        ->addExperience(new CvExperience(
            'Développeur Senior Full Stack',
            'Tech Corp',
            'Janvier 2020 - Présent',
            'Développement et maintenance d\'applications web à fort trafic. Mise en place de l\'architecture microservices et mentoring de l\'équipe junior.',
        ))
        ->addExperience(new CvExperience(
            'Développeur Web',
            'Web Agency',
            'Mars 2017 - Décembre 2019',
            'Conception et développement de sites web et applications pour divers clients. Intégration de solutions e-commerce et CMS sur mesure.',
        ))
        ->addExperience(new CvExperience(
            'Développeur Junior',
            'StartUp Inc',
            'Septembre 2015 - Février 2017',
        ))
        ->addDiploma(new CvDiploma('Master Informatique', 'Université Paris-Saclay', '2015'))
        ->addDiploma(new CvDiploma('Licence Informatique', 'Université Paris-Sud', '2013'))
        ->setLanguages([
            'Français (natif)',
            'Anglais (courant)',
            'Espagnol (intermédiaire)',
        ])
        ->setLinks([
            'github.com/jdupont',
            'linkedin.com/in/jdupont',
        ])
        ->setCertifications([
            'AWS Certified Developer Associate',
            'Symfony Certified Developer',
        ]);


    $builder = new CvBuilder(
        __DIR__ . '/resources/',
        __DIR__ . '/files/' . $data->get('code') . '.pdf',
        $data
    );
    $builder->setCvData($cvData);
    $builder->build();

    echo 'CV generated successfully: files/' . $data->get('code') . '.pdf' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
exit();
