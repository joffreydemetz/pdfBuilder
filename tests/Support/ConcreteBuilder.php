<?php

namespace JDZ\Pdf\Tests\Support;

use JDZ\Pdf\Builder;

/**
 * Builder with a no-op build() and public access to the protected config loaders.
 * It inherits every getter/setter, so the real Builder code is what runs.
 */
class ConcreteBuilder extends Builder
{
    public function build(): void {}

    public function exposeLoadModelizerConfig(string $modelizer): array
    {
        return $this->loadModelizerConfig($modelizer);
    }

    public function exposeLoadModelizerConfigFile(string $file, array &$inherits = []): array
    {
        return $this->loadModelizerConfigFile($file, $inherits);
    }
}
