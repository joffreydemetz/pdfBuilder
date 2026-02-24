<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

use JDZ\Pdf\Helper;
use JDZ\Pdf\Contract\BuilderInterface;
use JDZ\Utils\Data as jData;
use Symfony\Component\Yaml\Yaml;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class Builder implements BuilderInterface
{
  protected string $sourcesPath;
  protected string $targetPath;
  protected jData $data;
  protected Helper $helper;
  protected bool $toc = false;

  public function __construct(string $sourcesPath, string $targetPath, jData $data)
  {
    $this->sourcesPath = $sourcesPath;
    $this->targetPath = $targetPath;
    $this->data = $data;
    $this->helper = new Helper();
  }

  public function getSourcesPath(): string
  {
    return $this->sourcesPath;
  }

  public function setSourcesPath(string $sourcesPath): static
  {
    $this->sourcesPath = $sourcesPath;
    return $this;
  }

  public function getTargetPath(): string
  {
    return $this->targetPath;
  }

  public function setTargetPath(string $targetPath): static
  {
    $this->targetPath = $targetPath;
    return $this;
  }

  public function getData(): jData
  {
    return $this->data;
  }

  public function setData(jData $data): static
  {
    $this->data = $data;
    return $this;
  }

  public function getHelper(): Helper
  {
    return $this->helper;
  }

  public function getToc(): bool
  {
    return $this->toc;
  }

  public function setToc(bool $toc): static
  {
    $this->toc = $toc;
    return $this;
  }

  protected function loadModelizerConfig(string $modelizer): array
  {
    $data = new jData();
    $data->sets([
      'colors' => [],
      'fonts' => [],
    ]);

    $colors   = [];
    $fonts    = [];
    $inherits = [];

    $config = $this->loadModelizerConfigFile($modelizer, $inherits);

    foreach ($inherits as $inherit) {
      $tmp = $this->loadModelizerConfigFile($inherit);

      if (isset($tmp['colors'])) {
        $colors = array_merge($colors, $tmp['colors']);
        unset($tmp['colors']);
      }

      if (isset($tmp['fonts'])) {
        $fonts = array_merge($fonts, $tmp['fonts']);
        unset($tmp['fonts']);
      }

      $data->sets($tmp);
    }

    if (isset($config['colors'])) {
      $colors = array_merge($colors, $config['colors']);
      unset($config['colors']);
    }

    if (isset($config['fonts'])) {
      $fonts = array_merge($fonts, $config['fonts']);
      unset($config['fonts']);
    }

    $config['colors'] = $colors;
    $config['fonts'] = $fonts;

    $data->sets($config);

    return $data->all();
  }

  protected function loadModelizerConfigFile(string $file, array &$inherits = []): array
  {
    $path = $this->sourcesPath . 'config/modelizer.' . $file . '.yml';

    $data = [];

    if (file_exists($path)) {
      if ($data = Yaml::parseFile($path, Yaml::PARSE_CONSTANT)) {
        if (!empty($data['inherits'])) {
          foreach ($data['inherits'] as $inherit) {
            if (!in_array($inherit, $inherits)) {
              $this->loadModelizerConfigFile($inherit, $inherits);
              if (!in_array($inherit, $inherits)) {
                $inherits[] = $inherit;
              }
            }
          }
          unset($data['inherits']);
        }
      }
    }

    return $data;
  }
}
