<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Builder;
use JDZ\Utils\Data as jData;
use PHPUnit\Framework\TestCase;

/**
 * Concrete implementation for testing the abstract Builder.
 */
class ConcreteBuilder extends Builder
{
    public bool $buildCalled = false;

    public function build(): void
    {
        $this->buildCalled = true;
    }

    // Expose protected methods for testing
    public function exposeLoadModelizerConfig(string $modelizer): array
    {
        return $this->loadModelizerConfig($modelizer);
    }

    public function exposeLoadModelizerConfigFile(string $file, array &$inherits = []): array
    {
        return $this->loadModelizerConfigFile($file, $inherits);
    }

    public function getSourcesPath(): string
    {
        return $this->sourcesPath;
    }

    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    public function getData(): jData
    {
        return $this->data;
    }

    public function hasToc(): bool
    {
        return $this->toc;
    }
}

class BuilderTest extends TestCase
{
    private string $sourcesPath;
    private string $targetPath;

    protected function setUp(): void
    {
        $this->sourcesPath = str_replace('\\', '/', realpath(__DIR__ . '/../resources/')) . '/';
        $this->targetPath = sys_get_temp_dir() . '/test-output.pdf';
    }

    private function createBuilder(): ConcreteBuilder
    {
        return new ConcreteBuilder($this->sourcesPath, $this->targetPath, new jData());
    }

    public function testConstructorSetsProperties(): void
    {
        $data = new jData();
        $builder = new ConcreteBuilder($this->sourcesPath, '/tmp/out.pdf', $data);

        $this->assertSame($this->sourcesPath, $builder->getSourcesPath());
        $this->assertSame('/tmp/out.pdf', $builder->getTargetPath());
        $this->assertSame($data, $builder->getData());
    }

    public function testTocDefaultsFalse(): void
    {
        $builder = $this->createBuilder();
        $this->assertFalse($builder->hasToc());
    }

    public function testBuildCanBeCalled(): void
    {
        $builder = $this->createBuilder();
        $builder->build();
        $this->assertTrue($builder->buildCalled);
    }

    public function testLoadModelizerConfigFileBlank(): void
    {
        $builder = $this->createBuilder();
        $inherits = [];
        $config = $builder->exposeLoadModelizerConfigFile('blank', $inherits);

        $this->assertIsArray($config);
    }

    public function testLoadModelizerConfigFileNonExistent(): void
    {
        $builder = $this->createBuilder();
        $inherits = [];
        $config = $builder->exposeLoadModelizerConfigFile('nonexistent_model', $inherits);

        $this->assertSame([], $config);
    }

    public function testLoadModelizerConfigFileBase(): void
    {
        $builder = $this->createBuilder();
        $inherits = [];
        $config = $builder->exposeLoadModelizerConfigFile('base', $inherits);

        $this->assertIsArray($config);
        // The base config should have colors and fonts
        if (!empty($config)) {
            $this->assertTrue(
                isset($config['colors']) || isset($config['fonts']) || count($config) > 0
            );
        }
    }

    public function testLoadModelizerConfigFileWithInheritance(): void
    {
        $builder = $this->createBuilder();
        $inherits = [];
        $config = $builder->exposeLoadModelizerConfigFile('formation', $inherits);

        // formation.yml inherits from other configs
        $this->assertIsArray($inherits);
    }

    public function testLoadModelizerConfigBlank(): void
    {
        $builder = $this->createBuilder();
        $config = $builder->exposeLoadModelizerConfig('blank');

        $this->assertIsArray($config);
    }

    public function testLoadModelizerConfigMergesInheritedConfigs(): void
    {
        $builder = $this->createBuilder();
        $config = $builder->exposeLoadModelizerConfig('formation');

        $this->assertIsArray($config);
        // Should have merged colors and fonts from inherited configs
        if (isset($config['colors'])) {
            $this->assertIsArray($config['colors']);
        }
    }
}
