<?php

namespace JDZ\Pdf\Tests\Support;

use JDZ\Pdf\Pdf;

/**
 * A real Pdf that also logs the drawing calls it receives from the package (or from a
 * test), so a test can check what was drawn without comparing PDF bytes. Calls that
 * TCPDF / FPDI make on themselves (call site under vendor/) are not logged.
 */
class SpyPdf extends Pdf
{
    /** @var list<array{0: string, 1: list<mixed>}> */
    public array $calls = [];

    /**
     * @return list<list<mixed>> the arguments of each logged call to $method, in order
     */
    public function argsOf(string $method): array
    {
        $args = [];
        foreach ($this->calls as [$name, $callArgs]) {
            if ($name === $method) {
                $args[] = $callArgs;
            }
        }
        return $args;
    }

    /**
     * @return list<string> the logged method names, in call order
     */
    public function methods(): array
    {
        return array_column($this->calls, 0);
    }

    private function log(string $method, array $args): void
    {
        $callSite = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'] ?? '';
        if (!str_contains(str_replace('\\', '/', $callSite), '/vendor/')) {
            $this->calls[] = [$method, $args];
        }
    }

    // pages

    public function AddPage(...$args)
    {
        $this->log('AddPage', $args);
        return parent::AddPage(...$args);
    }

    public function endPage(...$args)
    {
        $this->log('endPage', $args);
        return parent::endPage(...$args);
    }

    public function setPage(...$args)
    {
        $this->log('setPage', $args);
        return parent::setPage(...$args);
    }

    public function lastPage(...$args)
    {
        $this->log('lastPage', $args);
        return parent::lastPage(...$args);
    }

    public function setPageMark(...$args)
    {
        $this->log('setPageMark', $args);
        return parent::setPageMark(...$args);
    }

    public function setAutoPageBreak(...$args)
    {
        $this->log('setAutoPageBreak', $args);
        return parent::setAutoPageBreak(...$args);
    }

    // fonts and colors

    public function AddFont(...$args)
    {
        $this->log('AddFont', $args);
        return parent::AddFont(...$args);
    }

    public function setFont(...$args)
    {
        $this->log('setFont', $args);
        return parent::setFont(...$args);
    }

    public function setTextColorArray(...$args)
    {
        $this->log('setTextColorArray', $args);
        return parent::setTextColorArray(...$args);
    }

    public function setFillColorArray(...$args)
    {
        $this->log('setFillColorArray', $args);
        return parent::setFillColorArray(...$args);
    }

    public function setDrawColorArray(...$args)
    {
        $this->log('setDrawColorArray', $args);
        return parent::setDrawColorArray(...$args);
    }

    // position

    public function setX(...$args)
    {
        $this->log('setX', $args);
        return parent::setX(...$args);
    }

    public function setY(...$args)
    {
        $this->log('setY', $args);
        return parent::setY(...$args);
    }

    public function Ln(...$args)
    {
        $this->log('Ln', $args);
        return parent::Ln(...$args);
    }

    public function setCellPaddings(...$args)
    {
        $this->log('setCellPaddings', $args);
        return parent::setCellPaddings(...$args);
    }

    // content

    public function Cell(...$args)
    {
        $this->log('Cell', $args);
        return parent::Cell(...$args);
    }

    public function writeHTMLCell(...$args)
    {
        $this->log('writeHTMLCell', $args);
        return parent::writeHTMLCell(...$args);
    }

    public function Line(...$args)
    {
        $this->log('Line', $args);
        return parent::Line(...$args);
    }

    public function Image(...$args)
    {
        $this->log('Image', $args);
        return parent::Image(...$args);
    }

    public function TextField(...$args)
    {
        $this->log('TextField', $args);
        return parent::TextField(...$args);
    }

    public function CheckBox(...$args)
    {
        $this->log('CheckBox', $args);
        return parent::CheckBox(...$args);
    }

    public function AddLink(...$args)
    {
        $this->log('AddLink', $args);
        return parent::AddLink(...$args);
    }

    public function setLink(...$args)
    {
        $this->log('setLink', $args);
        return parent::setLink(...$args);
    }

    // FPDI import

    public function setSourceFile(...$args)
    {
        $this->log('setSourceFile', $args);
        return parent::setSourceFile(...$args);
    }

    public function importPage(...$args)
    {
        $this->log('importPage', $args);
        return parent::importPage(...$args);
    }

    public function useTemplate(...$args)
    {
        $this->log('useTemplate', $args);
        return parent::useTemplate(...$args);
    }
}
