<?php

/**
 * Build a multi-page Book PDF with Table of Contents
 *
 * Demonstrates usage of BookToc, Toc marks, and Helper::exportToc()
 * to generate a book with a cover, TOC page, and 5 chapters.
 * Page numbers on the TOC are filled in after all pages are rendered.
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Utils\Data as jData;
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Data;
use JDZ\Pdf\BookToc;
use JDZ\Pdf\Helper;

// ---------------------------------------------------------------------------
// Data classes
// ---------------------------------------------------------------------------

class BookData
{
    public string $title = '';
    public string $subtitle = '';
    public string $author = '';
    public string $theme = '';
    /** @var ChapterData[] */
    public array $chapters = [];

    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function setSubtitle(string $subtitle): static { $this->subtitle = $subtitle; return $this; }
    public function setAuthor(string $author): static { $this->author = $author; return $this; }
    public function setTheme(string $theme): static { $this->theme = $theme; return $this; }

    /**
     * @param ChapterData[] $chapters
     */
    public function setChapters(array $chapters): static { $this->chapters = $chapters; return $this; }
}

class ChapterData
{
    public string $key = '';
    public string $title = '';
    public string $content = '';

    public function __construct(string $key = '', string $title = '', string $content = '')
    {
        $this->key = $key;
        $this->title = $title;
        $this->content = $content;
    }
}

// ---------------------------------------------------------------------------
// Builder
// ---------------------------------------------------------------------------

class BookTocBuilder extends Builder
{
    protected BookData $bookData;

    public function setBookData(BookData $bookData): static
    {
        $this->bookData = $bookData;
        return $this;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle($this->bookData->title);
        $pdf->SetSubject('Book');
        $pdf->SetCreator('JDZ pdfBuilder');
        $pdf->SetAuthor($this->bookData->author);
        $pdf->SetKeywords('book, toc, table of contents');
        $pdf->set('izPageNoShow', true);
        $pdf->set('izSourcesPath', $this->sourcesPath);
        $pdf->set('tocFont', 'helvetica');

        // Cover and TOC are 2 pages before content starts
        $pdf->izPageNoOffset = 2;

        $bookToc = new BookToc();
        $bookToc->setHelper($this->helper)
            ->setPdf($pdf)
            ->withPrintToc(true);

        $baseConfig = $this->loadModelizerConfig('base');

        // -- 1. Cover page --
        $coverData = new Data();
        $coverData->sets($baseConfig);
        $coverData->set('theme', $this->bookData->theme);
        $coverData->set('book', $this->bookData);

        $cover = new BookCoverModelizer($pdf, $coverData);
        $cover->load();
        $cover->toPdf();

        // -- 2. TOC page --
        $tocData = new Data();
        $tocData->sets($baseConfig);
        $tocData->set('theme', $this->bookData->theme);
        $tocData->set('book', $this->bookData);
        $tocData->set('bookToc', $bookToc);

        $toc = new BookTocModelizer($pdf, $tocData);
        $toc->load();
        $toc->toPdf();

        // -- 3. Chapter pages --
        foreach ($this->bookData->chapters as $i => $chapter) {
            $chapterData = new Data();
            $chapterData->sets($baseConfig);
            $chapterData->set('theme', $this->bookData->theme);
            $chapterData->set('chapter', $chapter);
            $chapterData->set('chapterIndex', $i + 1);

            $chapterModelizer = new BookChapterModelizer($pdf, $chapterData);
            $chapterModelizer->load();
            $chapterModelizer->toPdf();

            // Register the actual page number for this chapter in the TOC
            // PageNo() returns the offset-adjusted number (real page - izPageNoOffset)
            $bookToc->setPage($chapter->key, $pdf->PageNo());
        }

        // -- 4. Fill in TOC page numbers --
        $bookToc->toPdf();

        // -- 5. Output --
        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }

        $pdf->lastPage();
        $pdf->Output($this->targetPath, 'F');

        if (!file_exists($this->targetPath)) {
            throw new \Exception('Impossible de créer le livre.');
        }
    }
}

// ---------------------------------------------------------------------------
// Modelizers
// ---------------------------------------------------------------------------

class BookCoverModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;

    public function load(): void
    {
        parent::load();

        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetAutoPageBreak(false, 0);
    }

    public function Page(): void
    {
        $book = $this->data->get('book');

        $pageW = $this->pdf->getPageWidth();
        $pageH = $this->pdf->getPageHeight();
        $themeColor = $this->data->getColor('theme');

        // Full-page background
        $this->pdf->SetFillColorArray($themeColor);
        $this->pdf->Rect(0, 0, $pageW, $pageH, 'F');

        // Decorative accent bar
        $this->pdf->SetFillColorArray([255, 255, 255]);
        $this->pdf->SetAlpha(0.3);
        $this->pdf->Rect(0, $pageH * 0.42, $pageW, 2, 'F');
        $this->pdf->SetAlpha(1);

        // Title
        $this->pdf->SetTextColorArray([255, 255, 255]);
        $this->pdf->SetFont('helvetica', 'B', 28);
        $this->pdf->writeHTMLCell($pageW - 40, 0, 20, $pageH * 0.30, $book->title, 0, 1, false, true, 'C', true);

        // Subtitle
        if ($book->subtitle) {
            $y = $this->pdf->getY() + 8;
            $this->pdf->SetFont('helvetica', '', 14);
            $this->pdf->writeHTMLCell($pageW - 40, 0, 20, $y, $book->subtitle, 0, 1, false, true, 'C', true);
        }

        // Author
        $this->pdf->SetFont('helvetica', '', 16);
        $this->pdf->writeHTMLCell($pageW - 40, 0, 20, $pageH * 0.60, $book->author, 0, 1, false, true, 'C', true);

        // Year
        $this->pdf->SetFont('helvetica', '', 12);
        $this->pdf->SetAlpha(0.6);
        $this->pdf->writeHTMLCell($pageW - 40, 0, 20, $pageH * 0.90, date('Y'), 0, 1, false, true, 'C', true);
        $this->pdf->SetAlpha(1);
    }
}

class BookTocModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;

    public function load(): void
    {
        parent::load();

        $margin = $this->helper->px2mm(50);
        $pageWidth = $this->pdf->getPageWidth() - $margin * 2;

        $this->pdf->SetMargins($margin, $margin, $margin);
        $this->pdf->SetAutoPageBreak(false, $margin);

        $this->data->set('pageWidth', $pageWidth);
    }

    public function Page(): void
    {
        $book = $this->data->get('book');
        $bookToc = $this->data->get('bookToc');

        $x = $this->pdf->getX();
        $y = $this->pdf->getY();
        $w = $this->data->get('pageWidth');

        $themeColor = $this->data->getColor('theme');

        // Title
        $this->pdf->SetTextColorArray($themeColor);
        $this->pdf->SetFont('helvetica', 'B', 18);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'TABLE DES MATIÈRES', 0, 1, false, true, 'L', true);

        $y = $this->pdf->getY() + $this->helper->px2mm(8);

        // Separator line
        $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.5), $themeColor));

        $y += $this->helper->px2mm(24);

        // The real internal PDF page number of this TOC page
        $tocRealPage = $this->pdf->getPage();

        $entryH = $this->helper->px2mm(28);
        $numberW = $this->helper->px2mm(30);
        $numberX = $x + $w - $numberW;

        foreach ($book->chapters as $i => $chapter) {
            // Chapter number + title
            $label = ($i + 1) . '.  ' . $chapter->title;

            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->SetFont('helvetica', '', 11);
            $this->pdf->writeHTMLCell($w - $numberW, 0, $x, $y + $this->helper->px2mm(3), $label, 0, 0, false, true, 'L', true);

            // Dotted leader line
            $lineY = $y + $entryH - $this->helper->px2mm(6);
            $this->pdf->SetLineStyle([
                'width' => $this->data->getBorderWidth(0.3),
                'color' => $this->data->getColor('grayish'),
                'dash' => '1,2',
            ]);
            $this->pdf->Line($x + $this->helper->px2mm(5), $lineY, $numberX - $this->helper->px2mm(3), $lineY);

            // Register TOC position for page number (will be filled in by exportToc)
            $bookToc->setPosition($chapter->key, [
                'p' => $tocRealPage,
                'x' => $numberX,
                'y' => $y,
                'w' => $numberW,
                'h' => $entryH,
            ]);

            $y += $entryH;
        }
    }
}

class BookChapterModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;

    public function load(): void
    {
        parent::load();

        $marginLR = $this->helper->px2mm(50);
        $marginTop = $this->helper->px2mm(50);
        $marginBottom = $this->helper->px2mm(40);

        $pageWidth = $this->pdf->getPageWidth() - $marginLR * 2;

        $this->pdf->SetMargins($marginLR, $marginTop, $marginLR);
        $this->pdf->SetAutoPageBreak(false, $marginBottom);

        $this->data->set('pageWidth', $pageWidth);
    }

    public function Page(): void
    {
        $chapter = $this->data->get('chapter');
        $chapterIndex = $this->data->get('chapterIndex');

        $x = $this->pdf->getX();
        $y = $this->pdf->getY();
        $w = $this->data->get('pageWidth');
        $pageW = $this->pdf->getPageWidth();
        $pageH = $this->pdf->getPageHeight();
        $themeColor = $this->data->getColor('theme');

        // -- Chapter header bar --
        $barH = $this->helper->px2mm(40);
        $this->pdf->SetFillColorArray($themeColor);
        $this->pdf->Rect(0, 0, $pageW, $barH, 'F');

        $this->pdf->SetTextColorArray([255, 255, 255]);
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->writeHTMLCell($w, 0, $x, $this->helper->px2mm(10), 'Chapitre ' . $chapterIndex, 0, 1, false, true, 'L', true);

        // -- Chapter title --
        $y = $barH + $this->helper->px2mm(16);

        $this->pdf->SetTextColorArray($themeColor);
        $this->pdf->SetFont('helvetica', 'B', 18);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $chapter->title, 0, 1, false, true, 'L', true);

        $y = $this->pdf->getY() + $this->helper->px2mm(6);

        // Accent line
        $this->pdf->Line($x, $y, $x + $this->helper->px2mm(60), $y, $this->helper->border($this->data->getBorderWidth(1), $themeColor));

        $y += $this->helper->px2mm(14);

        // -- Content --
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, $chapter->content, 0, 1, false, true, 'L', true);

        // -- Footer: page number --
        $footerY = $pageH - $this->helper->px2mm(25);
        $this->pdf->SetTextColorArray($this->data->getColor('grayish'));
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->writeHTMLCell($w, 0, $x, $footerY, (string)$this->pdf->PageNo(), 0, 1, false, true, 'C', true);
    }
}

// ---------------------------------------------------------------------------
// Autoload & Run
// ---------------------------------------------------------------------------

require_once __DIR__ . '/autoload.php';

try {
    $data = new jData();
    $data->sets([
        'code' => 'booktoc-1',
    ]);

    $bookData = new BookData();
    $bookData
        ->setTitle('GUIDE PRATIQUE DU DÉVELOPPEMENT MODERNE')
        ->setSubtitle('Méthodes, outils et bonnes pratiques')
        ->setAuthor('Jean-Pierre Martin')
        ->setTheme('#34495e')
        ->setChapters([
            new ChapterData(
                'ch-intro',
                'Introduction',
                '<p>Le développement logiciel a connu une évolution considérable au cours des dernières décennies. Les méthodologies, les outils et les pratiques ont profondément changé, passant de modèles rigides et séquentiels à des approches agiles et itératives.</p>'
                . '<p>Ce guide a pour objectif de fournir une vue d\'ensemble des pratiques modernes du développement logiciel. Il s\'adresse aussi bien aux développeurs débutants qu\'aux professionnels souhaitant actualiser leurs connaissances.</p>'
                . '<p>Au fil des chapitres, nous explorerons les fondamentaux théoriques, les techniques avancées, des études de cas concrètes, et nous terminerons par une réflexion sur les perspectives d\'avenir de notre discipline.</p>'
            ),
            new ChapterData(
                'ch-fondamentaux',
                'Les fondamentaux',
                '<p>Avant de plonger dans les techniques avancées, il est essentiel de maîtriser les fondamentaux. La programmation orientée objet, les principes SOLID, et les design patterns constituent le socle sur lequel repose tout développement robuste.</p>'
                . '<p>Le principe de responsabilité unique stipule qu\'une classe ne devrait avoir qu\'une seule raison de changer. Ce principe, apparemment simple, est souvent mal compris et mal appliqué. Une bonne architecture logicielle repose sur la séparation des préoccupations et la modularité.</p>'
                . '<p>Les tests automatisés représentent un autre pilier fondamental. Les tests unitaires vérifient le comportement individuel de chaque composant, tandis que les tests d\'intégration valident les interactions entre composants. Une couverture de tests adéquate garantit la fiabilité du code et facilite la refactorisation.</p>'
            ),
            new ChapterData(
                'ch-avance',
                'Techniques avancées',
                '<p>Les architectures microservices ont gagné en popularité ces dernières années. Elles permettent de décomposer une application monolithique en services indépendants, chacun responsable d\'un domaine métier spécifique. Cette approche offre une meilleure scalabilité et une plus grande flexibilité.</p>'
                . '<p>Le déploiement continu (CI/CD) automatise le processus de mise en production. Grâce à des pipelines bien configurés, chaque modification du code est automatiquement testée, validée et déployée. Cette pratique réduit les risques d\'erreur humaine et accélère le cycle de livraison.</p>'
                . '<p>La conteneurisation avec Docker et l\'orchestration avec Kubernetes sont devenues des compétences incontournables. Ces technologies permettent d\'encapsuler les applications et leurs dépendances dans des environnements reproductibles, garantissant une cohérence entre les environnements de développement, de test et de production.</p>'
            ),
            new ChapterData(
                'ch-etudes',
                'Études de cas',
                '<p>L\'entreprise TechVision a migré son application monolithique vers une architecture microservices en 18 mois. Le projet impliquait une équipe de 12 développeurs et a nécessité une refonte complète du système de déploiement. Les résultats ont été significatifs : une réduction de 40% du temps de déploiement et une amélioration de 60% de la disponibilité.</p>'
                . '<p>DataFlow, une startup spécialisée dans le traitement de données en temps réel, a adopté une approche event-driven dès sa création. En utilisant Apache Kafka comme backbone de messagerie, l\'équipe a pu construire un système capable de traiter plus de 100 000 événements par seconde tout en maintenant une latence inférieure à 50 millisecondes.</p>'
            ),
            new ChapterData(
                'ch-conclusion',
                'Conclusion et perspectives',
                '<p>Le développement logiciel continue d\'évoluer à un rythme soutenu. L\'intelligence artificielle et le machine learning ouvrent de nouvelles possibilités, tant dans l\'assistance au développement que dans les applications elles-mêmes. Les outils de génération de code assistée par IA commencent à transformer la façon dont les développeurs travaillent.</p>'
                . '<p>L\'informatique quantique, bien qu\'encore expérimentale, promet des avancées significatives dans certains domaines de calcul. Les développeurs devront s\'adapter à de nouveaux paradigmes de programmation pour exploiter pleinement cette technologie.</p>'
                . '<p>Quelle que soit l\'évolution technologique, les principes fondamentaux de qualité, de maintenabilité et de collaboration resteront au cœur de notre métier. L\'apprentissage continu et l\'adaptation sont les clés du succès dans un domaine en perpétuelle mutation.</p>'
            ),
        ]);

    $builder = new BookTocBuilder(
        __DIR__ . '/resources/',
        __DIR__ . '/files/' . $data->get('code') . '.pdf',
        $data
    );
    $builder->setBookData($bookData);
    $builder->build();

    echo 'Book generated successfully: files/' . $data->get('code') . '.pdf' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

exit();
