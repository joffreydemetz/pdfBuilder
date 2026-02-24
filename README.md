# jdz/pdfbuilder

PHP library for building structured PDFs using TCPDF and FPDI.

Provides a **Builder/Modelizer** pattern for generating multi-page PDFs with YAML-driven configuration, automatic config inheritance, color/font management, and unit conversion utilities.

## Requirements

- PHP >= 8.1
- [TCPDF](https://github.com/tecnickcom/TCPDF) ^6.3
- [FPDI](https://www.setasign.com/products/fpdi/about/) ^2.6
- [Symfony YAML](https://symfony.com/doc/current/components/yaml.html) ^7.2

## Installation

```bash
composer require jdz/pdfbuilder
```

FPDI requires a private Composer repository. Add this to your `composer.json` if not already present:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://www.setasign.com/downloads/"
        }
    ]
}
```

## Architecture

### Core classes

| Class | Role |
|-------|------|
| `Builder` | Abstract base class. Loads YAML modelizer configs with inheritance, registers fonts, orchestrates PDF generation. Concrete builders extend this and implement `build()`. |
| `Modelizer` | Base class for rendering a single page type. Subclasses override `Page()`, `Header()`, and `Footer()` to define layout. Handles margins, page breaks, and page number placeholders. |
| `Data` | Configuration container extending `jData`. Manages colors (`Color`), fonts (`Font`), and provides unit conversion methods (`px2mm`, `px2pt`, `mm2px`, `percent2mm`). |
| `Pdf` | FPDI/TCPDF bridge. Wraps `setasign\Fpdi\Tcpdf\Fpdi` with custom font loading, model-based Header/Footer callbacks, and page number offset support. |
| `Helper` | Utility class for unit conversions, image sizing/resolution, border definition, and TOC export. |

### Built-in modelizers

| Modelizer | Purpose |
|-----------|---------|
| `ImageModelizer` | Renders a full-page image scaled to page width. |
| `PdfModelizer` | Includes pages from an existing PDF file via FPDI. |
| `FormAbstractModelizer` | Provides form building helpers: text fields, checkboxes, radio buttons, signatures. |

### Supporting classes

| Class | Purpose |
|-------|---------|
| `Color` | RGB/Hex color with HSV conversion, `lighten()` and `darken()` methods. |
| `Font` | Font name, size, and style holder. |
| `Book` | Container for `Toc` and `BookText` in multi-page documents. |
| `BookText` | Multilingual text management (fr, en, it) with plural support. |
| `Toc` | Table of Contents with page mark tracking. |

## Configuration

Modelizer configs are YAML files stored in a `config/` directory. They support inheritance via the `inherits` key.

**Example:** `modelizer.formation.yml`
```yaml
inherits:
  - base
  - catalogue

colors:
  pictoNewColor: "#cc0000"
  pictoCpfColor: "#01a6ba"

marginTop: 120
marginBottom: 62
pagePadding: 18
```

The `base` config provides defaults for all colors, fonts, and font sizes:

```yaml
colors:
    white: "#FFFFFF"
    black: "#000000"
    textColor: "#000000"
    linkColor: "#0b8181"

fonts:
    default: helvetica
    black: helvetica
    light: helvetica

h1Fsize: 17
h2Fsize: 16
pFsize: 9
borderWidth: 0.1
```

Configs are loaded via `Builder::loadModelizerConfig('formation')`, which resolves the full inheritance chain and merges colors, fonts, and all other properties.

## Usage

### 1. Create a Modelizer (page layout)

```php
use JDZ\Pdf\Modelizer;

class InvoiceModelizer extends Modelizer
{
    protected bool $printHeader = true;
    protected bool $printFooter = true;

    public function Header(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
    }

    public function Page(): void
    {
        $invoice = $this->data->get('invoice');

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 8, 'Client: ' . $invoice->client, 0, 1);
        $this->pdf->Cell(0, 8, 'Amount: ' . $invoice->amount, 0, 1);
    }

    public function Footer(): void
    {
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(0, 10, 'Page ' . $this->pdf->getAliasNumPage(), 0, 0, 'C');
    }
}
```

### 2. Create a Builder

```php
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Data;

class InvoiceBuilder extends Builder
{
    private object $invoiceData;

    public function setInvoiceData(object $data): void
    {
        $this->invoiceData = $data;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle('Invoice');
        $pdf->set('izSourcesPath', $this->sourcesPath);

        // Load YAML config with inheritance
        $modelizerData = new Data();
        $modelizerData->sets($this->loadModelizerConfig('invoice'));
        $modelizerData->set('invoice', $this->invoiceData);

        // Create modelizer, render page
        $modelizer = new InvoiceModelizer($pdf, $modelizerData);
        $modelizer->load();
        $modelizer->toPdf();

        // Write PDF to disk
        $pdf->Output($this->targetPath, 'F');
    }
}
```

### 3. Generate the PDF

```php
use JDZ\Utils\Data as jData;

$data = new jData();
$data->set('label', 'Invoice #001');

$builder = new InvoiceBuilder(
    sourcesPath: __DIR__ . '/resources/',
    targetPath: __DIR__ . '/files/invoice.pdf',
    data: $data
);

$builder->setInvoiceData((object)[
    'client' => 'Acme Corp',
    'amount' => '1 500,00 EUR',
]);

$builder->build();
```

## Custom fonts

Register fonts in your Builder's `build()` method before creating the Modelizer:

```php
$pdf->set('font', (object)[
    'name' => 'montserrat',
    'styles' => [null, 'B', 'I', 'BI'],
]);
```

Font definition files (PHP arrays) go in your `resources/fonts/` directory. The library ships with Helvetica, Courier, Montserrat, and Merienda.

## Color manipulation

```php
// Get a color clone and lighten it
$color = $data->getColorObject('theme');
$color->lighten(20);
$lightTheme = $color->toArray(); // [r, g, b]
```

The `Color` class supports hex-to-RGB conversion, HSV transformations, and `lighten()`/`darken()` methods.

## Examples

The `examples/` directory contains complete working examples:

| Example | Description | Output |
|---------|-------------|--------|
| `cv.php` | CV / Resume | Single-page CV with photo, skills, experience |
| `offre.php` | Job offer | Job posting with company info and description |
| `formation.php` | Training course | Course sheet with programme, sessions, side panel |
| `catalogue.php` | Multi-page catalogue | Cover page + category separators + 10 course sheets |
| `invoice.php` | Invoice | Professional invoice with items table and totals |
| `booktoc.php` | Book with TOC | Cover + table of contents + 5 chapters with clickable links |

Run an example:

```bash
php examples/cv.php
# Output: examples/files/cv-1.pdf
```

## License

MIT - See [LICENSE](LICENSE) for details.
