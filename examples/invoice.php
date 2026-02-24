<?php

/**
 * Build an Invoice PDF
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Utils\Data as jData;
use JDZ\Pdf\Builder;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Data;

class InvoiceItem
{
    public string $description = '';
    public int $quantity = 0;
    public float $unitPrice = 0.0;

    public function __construct(string $description = '', int $quantity = 0, float $unitPrice = 0.0)
    {
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function total(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}

class InvoiceCompany
{
    public string $name = '';
    public string $address = '';
    public string $zipCity = '';
    public string $phone = '';
    public string $email = '';
    public string $siret = '';
    public string $tvaIntra = '';
    public string $iban = '';

    public function __construct(
        string $name = '',
        string $address = '',
        string $zipCity = '',
        string $phone = '',
        string $email = '',
        string $siret = '',
        string $tvaIntra = '',
        string $iban = ''
    ) {
        $this->name = $name;
        $this->address = $address;
        $this->zipCity = $zipCity;
        $this->phone = $phone;
        $this->email = $email;
        $this->siret = $siret;
        $this->tvaIntra = $tvaIntra;
        $this->iban = $iban;
    }
}

class InvoiceClient
{
    public string $name = '';
    public string $address = '';
    public string $zipCity = '';
    public string $siret = '';

    public function __construct(string $name = '', string $address = '', string $zipCity = '', string $siret = '')
    {
        $this->name = $name;
        $this->address = $address;
        $this->zipCity = $zipCity;
        $this->siret = $siret;
    }
}

class InvoiceData
{
    public string $theme = '';
    public string $number = '';
    public string $date = '';
    public string $dueDate = '';
    public string $paymentTerms = '';
    public float $tvaRate = 20.0;
    public InvoiceCompany $company;
    public InvoiceClient $client;
    /** @var InvoiceItem[] */
    public array $items = [];

    public function __construct()
    {
        $this->company = new InvoiceCompany();
        $this->client = new InvoiceClient();
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function setDueDate(string $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function setPaymentTerms(string $paymentTerms): static
    {
        $this->paymentTerms = $paymentTerms;
        return $this;
    }

    public function setTvaRate(float $tvaRate): static
    {
        $this->tvaRate = $tvaRate;
        return $this;
    }

    public function setCompany(InvoiceCompany $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function setClient(InvoiceClient $client): static
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param InvoiceItem[] $items
     */
    public function setItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    public function addItem(InvoiceItem $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    public function totalHT(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->total();
        }
        return $total;
    }

    public function totalTVA(): float
    {
        return $this->totalHT() * ($this->tvaRate / 100);
    }

    public function totalTTC(): float
    {
        return $this->totalHT() + $this->totalTVA();
    }
}

class InvoiceBuilder extends Builder
{
    protected InvoiceData $invoiceData;

    public function setInvoiceData(InvoiceData $invoiceData): static
    {
        $this->invoiceData = $invoiceData;
        return $this;
    }

    public function build(): void
    {
        $pdf = new Pdf();
        $pdf->SetTitle('Facture ' . $this->invoiceData->number);
        $pdf->SetSubject('Facture');
        $pdf->SetCreator('JDZ Solutions');
        $pdf->SetAuthor('JDZ Solutions');
        $pdf->SetKeywords('facture, invoice');
        $pdf->set('izPageNoShow', false);
        $pdf->set('izSourcesPath', $this->sourcesPath);

        $modelizerData = new Data();
        $modelizerData->sets($this->loadModelizerConfig('invoice'));

        $modelizerData->set('theme', $this->invoiceData->theme);
        $modelizerData->set('invoice', $this->invoiceData);

        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }

        \set_time_limit(240);

        $modelizer = new InvoiceModelizer($pdf, $modelizerData);
        $modelizer->load();
        $modelizer->toPdf();

        $pdf->Output($this->targetPath, 'F');

        if (!file_exists($this->targetPath)) {
            throw new \Exception('Impossible de créer la facture.');
        }
    }
}

class InvoiceModelizer extends Modelizer
{
    protected bool $loadWithPdfMargins = false;
    protected bool $autoPageBreak = false;
    protected bool $printHeader = false;
    protected bool $printFooter = false;

    public function load(): void
    {
        parent::load();

        $margin = $this->helper->px2mm(40);

        $pageWidth = $this->pdf->getPageWidth();
        $pageWidth -= $margin * 2;

        $this->pdf->SetMargins($margin, $margin, $margin);
        $this->pdf->SetAutoPageBreak(false, $margin);

        $this->data->set('pageWidth', $pageWidth);
    }

    public function Page(): void
    {
        parent::Page();

        $x = $this->pdf->getX();
        $y = $this->pdf->getY();

        $y = $this->headerSection($y);
        $y = $this->clientSection($y);
        $y = $this->itemsTable($y);
        $y = $this->totalsSection($y);
        $y = $this->paymentSection($y);
        $this->footerSection();
    }

    private function headerSection(float|int $y): float|int
    {
        $invoice = $this->data->get('invoice');
        $x = $this->pdf->getX();
        $w = $this->data->get('pageWidth');

        // -- Left: company info --
        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->writeHTMLCell($w / 2, 0, $x, $y, $invoice->company->name, 0, 1, false, true, '', true);

        $companyY = $this->pdf->getY() + $this->helper->px2mm(6);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->writeHTMLCell($w / 2, 0, $x, $companyY, $invoice->company->address, 0, 1, false, true, '', true);
        $companyY = $this->pdf->getY();
        $this->pdf->writeHTMLCell($w / 2, 0, $x, $companyY, $invoice->company->zipCity, 0, 1, false, true, '', true);
        $companyY = $this->pdf->getY() + $this->helper->px2mm(4);
        $this->pdf->writeHTMLCell($w / 2, 0, $x, $companyY, $invoice->company->phone, 0, 1, false, true, '', true);
        $companyY = $this->pdf->getY();
        $this->pdf->writeHTMLCell($w / 2, 0, $x, $companyY, $invoice->company->email, 0, 1, false, true, '', true);

        $leftEndY = $this->pdf->getY();

        // -- Right: FACTURE title + invoice details --
        $rightX = $x + $w / 2;
        $rightW = $w / 2;

        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->SetFont('helvetica', 'B', 22);
        $this->pdf->writeHTMLCell($rightW, 0, $rightX, $y, 'FACTURE', 0, 1, false, true, 'R', true);

        $detailY = $this->pdf->getY() + $this->helper->px2mm(8);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont('helvetica', '', 9);

        $this->pdf->writeHTMLCell($rightW, 0, $rightX, $detailY, '<strong>N° :</strong> ' . $invoice->number, 0, 1, false, true, 'R', true);
        $detailY = $this->pdf->getY() + $this->helper->px2mm(2);
        $this->pdf->writeHTMLCell($rightW, 0, $rightX, $detailY, '<strong>Date :</strong> ' . $invoice->date, 0, 1, false, true, 'R', true);
        $detailY = $this->pdf->getY() + $this->helper->px2mm(2);
        $this->pdf->writeHTMLCell($rightW, 0, $rightX, $detailY, '<strong>Échéance :</strong> ' . $invoice->dueDate, 0, 1, false, true, 'R', true);

        $rightEndY = $this->pdf->getY();

        $y = max($leftEndY, $rightEndY) + $this->helper->px2mm(20);

        // Separator line
        $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('theme')));

        $y += $this->helper->px2mm(16);

        return $y;
    }

    private function clientSection(float|int $y): float|int
    {
        $invoice = $this->data->get('invoice');
        $x = $this->pdf->getX();
        $w = $this->data->get('pageWidth');

        // Client box on the right side
        $boxW = $w * 0.45;
        $boxX = $x + $w - $boxW;
        $boxPad = $this->helper->px2mm(10);

        // Background rect
        $boxH = $this->helper->px2mm(70);
        $this->pdf->SetFillColorArray($this->data->getColor('invoiceBg'));
        $this->pdf->Rect($boxX, $y, $boxW, $boxH, 'F');

        $innerX = $boxX + $boxPad;
        $innerW = $boxW - $boxPad * 2;
        $innerY = $y + $boxPad;

        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->writeHTMLCell($innerW, 0, $innerX, $innerY, 'FACTURER À', 0, 1, false, true, '', true);

        $innerY = $this->pdf->getY() + $this->helper->px2mm(6);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->writeHTMLCell($innerW, 0, $innerX, $innerY, $invoice->client->name, 0, 1, false, true, '', true);

        $innerY = $this->pdf->getY() + $this->helper->px2mm(2);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->writeHTMLCell($innerW, 0, $innerX, $innerY, $invoice->client->address, 0, 1, false, true, '', true);
        $innerY = $this->pdf->getY();
        $this->pdf->writeHTMLCell($innerW, 0, $innerX, $innerY, $invoice->client->zipCity, 0, 1, false, true, '', true);

        if ($invoice->client->siret) {
            $innerY = $this->pdf->getY() + $this->helper->px2mm(4);
            $this->pdf->writeHTMLCell($innerW, 0, $innerX, $innerY, 'SIRET : ' . $invoice->client->siret, 0, 1, false, true, '', true);
        }

        $y += $boxH + $this->helper->px2mm(20);

        return $y;
    }

    private function itemsTable(float|int $y): float|int
    {
        $invoice = $this->data->get('invoice');
        $x = $this->pdf->getX();
        $w = $this->data->get('pageWidth');

        $colDesc = $w * 0.46;
        $colQty = $w * 0.14;
        $colUnit = $w * 0.20;
        $colTotal = $w * 0.20;
        $rowH = $this->helper->px2mm(24);

        // -- Table header --
        $this->pdf->SetFillColorArray($this->data->getColor('tableHeaderBg'));
        $this->pdf->SetTextColorArray($this->data->getColor('white'));
        $this->pdf->SetFont('helvetica', 'B', 9);

        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($colDesc, $rowH, '  Description', 0, 0, 'L', true);
        $this->pdf->Cell($colQty, $rowH, 'Quantité', 0, 0, 'C', true);
        $this->pdf->Cell($colUnit, $rowH, 'Prix unit. HT', 0, 0, 'R', true);
        $this->pdf->Cell($colTotal, $rowH, 'Total HT  ', 0, 1, 'R', true);

        $y += $rowH;

        // -- Table rows --
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));

        foreach ($invoice->items as $i => $item) {
            $isAlt = ($i % 2 === 1);

            if ($isAlt) {
                $this->pdf->SetFillColorArray($this->data->getColor('tableAltRow'));
            } else {
                $this->pdf->SetFillColorArray($this->data->getColor('white'));
            }

            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell($colDesc, $rowH, '  ' . $item->description, 0, 0, 'L', true);
            $this->pdf->Cell($colQty, $rowH, (string)$item->quantity, 0, 0, 'C', true);
            $this->pdf->Cell($colUnit, $rowH, $this->formatMoney($item->unitPrice), 0, 0, 'R', true);
            $this->pdf->Cell($colTotal, $rowH, $this->formatMoney($item->total()) . '  ', 0, 1, 'R', true);

            $y += $rowH;
        }

        // Bottom border line
        $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('tableBorder')));

        $y += $this->helper->px2mm(8);

        return $y;
    }

    private function totalsSection(float|int $y): float|int
    {
        $invoice = $this->data->get('invoice');
        $x = $this->pdf->getX();
        $w = $this->data->get('pageWidth');

        $totalsW = $w * 0.40;
        $totalsX = $x + $w - $totalsW;
        $labelW = $totalsW * 0.55;
        $valueW = $totalsW * 0.45;
        $rowH = $this->helper->px2mm(20);

        // Total HT
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->SetXY($totalsX, $y);
        $this->pdf->Cell($labelW, $rowH, 'Total HT', 0, 0, 'L');
        $this->pdf->Cell($valueW, $rowH, $this->formatMoney($invoice->totalHT()), 0, 1, 'R');
        $y += $rowH;

        // TVA
        $this->pdf->SetXY($totalsX, $y);
        $this->pdf->Cell($labelW, $rowH, 'TVA (' . number_format($invoice->tvaRate, 0) . ' %)', 0, 0, 'L');
        $this->pdf->Cell($valueW, $rowH, $this->formatMoney($invoice->totalTVA()), 0, 1, 'R');
        $y += $rowH;

        // Separator
        $this->pdf->Line($totalsX, $y, $totalsX + $totalsW, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('textColor')));
        $y += $this->helper->px2mm(2);

        // Total TTC
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->SetXY($totalsX, $y);
        $this->pdf->Cell($labelW, $rowH, 'Total TTC', 0, 0, 'L');
        $this->pdf->Cell($valueW, $rowH, $this->formatMoney($invoice->totalTTC()), 0, 1, 'R');
        $y += $rowH;

        $y += $this->helper->px2mm(16);

        return $y;
    }

    private function paymentSection(float|int $y): float|int
    {
        $invoice = $this->data->get('invoice');
        $x = $this->pdf->getX();
        $w = $this->data->get('pageWidth');

        // Separator
        $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('tableBorder')));
        $y += $this->helper->px2mm(12);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetTextColorArray($this->data->getColor('theme'));
        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'CONDITIONS DE PAIEMENT', 0, 1, false, true, '', true);

        $y = $this->pdf->getY() + $this->helper->px2mm(6);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Paiement à ' . $invoice->paymentTerms . ' à compter de la date de facturation.', 0, 1, false, true, '', true);
        $y = $this->pdf->getY() + $this->helper->px2mm(2);
        $this->pdf->writeHTMLCell($w, 0, $x, $y, 'Date d\'échéance : ' . $invoice->dueDate, 0, 1, false, true, '', true);

        if ($invoice->company->iban) {
            $y = $this->pdf->getY() + $this->helper->px2mm(8);

            $this->pdf->SetFont('helvetica', 'B', 9);
            $this->pdf->SetTextColorArray($this->data->getColor('theme'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'COORDONNÉES BANCAIRES', 0, 1, false, true, '', true);

            $y = $this->pdf->getY() + $this->helper->px2mm(6);
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->SetTextColorArray($this->data->getColor('textColor'));
            $this->pdf->writeHTMLCell($w, 0, $x, $y, 'IBAN : ' . $invoice->company->iban, 0, 1, false, true, '', true);
        }

        $y = $this->pdf->getY() + $this->helper->px2mm(10);

        return $y;
    }

    private function footerSection(): void
    {
        $invoice = $this->data->get('invoice');
        $x = $this->pdf->getX();
        $w = $this->data->get('pageWidth');
        $pageH = $this->pdf->getPageHeight();
        $y = $pageH - $this->helper->px2mm(30);

        // Separator
        $this->pdf->Line($x, $y, $x + $w, $y, $this->helper->border($this->data->getBorderWidth(0.1), $this->data->getColor('tableBorder')));
        $y += $this->helper->px2mm(6);

        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->SetTextColorArray($this->lightColorArray('textColor', 30));

        $footer = $invoice->company->name;
        $footer .= ' — SIRET : ' . $invoice->company->siret;
        $footer .= ' — TVA Intra. : ' . $invoice->company->tvaIntra;
        $footer .= ' — ' . $invoice->company->address . ', ' . $invoice->company->zipCity;

        $this->pdf->writeHTMLCell($w, 0, $x, $y, $footer, 0, 1, false, true, 'C', true);
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' €';
    }
}

// ---------------------------------------------------------------------------
// Autoload & Run
// ---------------------------------------------------------------------------

require_once __DIR__ . '/autoload.php';

try {
    $data = new jData();
    $data->sets([
        'code' => 'invoice-1',
    ]);

    $invoiceData = new InvoiceData();

    $invoiceData
        ->setTheme('#2c3e50')
        ->setNumber('F-2026-0042')
        ->setDate('24/02/2026')
        ->setDueDate('26/03/2026')
        ->setPaymentTerms('30 jours')
        ->setTvaRate(20.0)
        ->setCompany(new InvoiceCompany(
            'JDZ Solutions',
            '12 rue de l\'Innovation',
            '75001 Paris',
            'Tél. : 01 23 45 67 89',
            'contact@jdz-solutions.fr',
            '123 456 789 00011',
            'FR12 123456789',
            'FR76 1234 5678 9012 3456 7890 123'
        ))
        ->setClient(new InvoiceClient(
            'Acme Corp SAS',
            '45 avenue des Champs',
            '69001 Lyon',
            '987 654 321 00022'
        ))
        ->setItems([
            new InvoiceItem('Développement application web - Sprint 1', 12, 650.00),
            new InvoiceItem('Consulting architecture technique', 3, 850.00),
            new InvoiceItem('Design UX/UI - maquettes et prototypes', 5, 550.00),
            new InvoiceItem('Hébergement et maintenance serveur (trimestriel)', 1, 450.00),
            new InvoiceItem('Formation équipe développement PHP/Symfony', 2, 1200.00),
        ]);

    $builder = new InvoiceBuilder(
        __DIR__ . '/resources/',
        __DIR__ . '/files/' . $data->get('code') . '.pdf',
        $data
    );
    $builder->setInvoiceData($invoiceData);
    $builder->build();

    echo 'Invoice generated successfully: files/' . $data->get('code') . '.pdf' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

exit();
