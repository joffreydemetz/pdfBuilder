<?php

namespace JDZ\Pdf\Tests\Support;

use JDZ\Pdf\Modelizer\FormAbstractModelizer;

/**
 * Concrete form modelizer: calls the protected form helpers on demand and provides the
 * `setFormSample()` part body that setPart('sample', …) dispatches to.
 */
class FormModelizerProbe extends FormAbstractModelizer
{
    /** @var list<array{0: mixed, 1: mixed}> the (x, y) each setFormSample() call received */
    public array $sampleParts = [];

    public function call(string $method, mixed ...$args): mixed
    {
        return $this->{$method}(...$args);
    }

    public function exposeToNextRow(float &$lastY, $width = null)
    {
        return $this->toNextRow($lastY, $width);
    }

    protected function setFormSample($x, $y): void
    {
        $this->sampleParts[] = [$x, $y];
        $this->pdf->Cell(50, 10, 'sample body', 0, 1);
    }
}
