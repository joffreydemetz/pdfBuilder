<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

use JDZ\Pdf\Color;
use JDZ\Pdf\Font;
use JDZ\Pdf\Helper;
use JDZ\Utils\Data as jData;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Data extends jData
{
  private Helper $helper;
  private jData $colors;
  private jData $fonts;

  public function __construct()
  {
    $this->helper = new Helper();
    $this->colors = new jData();
    $this->fonts = new jData();
  }

  public function sets(array $properties, bool $merge = true): self
  {
    if (isset($properties['colors'])) {
      foreach ((array)$properties['colors'] as $key => $value) {
        if (!$value instanceof Color) {
          $value = new Color($value);
        }
        $this->addColor($key, $value);
      }
      unset($properties['colors']);
    }

    if (isset($properties['fonts'])) {
      foreach ((array)$properties['fonts'] as $key => $value) {
        if (!$value instanceof Font) {
          $value = new Font($value);
        }
        $this->addFont($key, $value);
      }
      unset($properties['fonts']);
    }

    return parent::sets($properties, $merge);
  }

  public function addColor(string $name, Color $color)
  {
    $this->colors->set($name, $color);
    return $this;
  }

  public function addFont(string $name, Font $font)
  {
    $this->fonts->set($name, $font);
    return $this;
  }

  public function toUppercase(string $str): string
  {
    return $this->helper->uppercaseString($str);
  }

  public function getString(string $name, string $default = ''): string
  {
    $str = (string)$this->get($name, $default);
    $str = $this->helper->cleanText($str);
    return $str;
  }

  public function getUppercaseString(string $name, string $default = ''): string
  {
    $str = $this->getString($name, $default);
    return $this->toUppercase($str);
  }

  public function getArrayItem(string $name, int $i = 0): mixed
  {
    if ($array = $this->getArray($name)) {
      return $array[$i] ?: false;
    }

    return false;
  }

  public function getColor(string $key): array
  {
    // it's an hex code so build the color if not found
    if (preg_match("/^#[0-9A-Fa-f]+$/", $key)) {
      if (!$this->colors->has($key)) {
        $this->addColor($key, new Color($key));
      }
    } elseif (null !== ($value = $this->get($key))) {
      return $this->getColor($value);
    }

    if (!$this->colors->has($key)) {
      throw new \Exception('Color not found -- ' . $key);
    }

    $color = $this->getColorObject($key);
    return $color->toArray();
  }

  public function getColorObject(string $key): Color
  {
    if (!$this->colors->has($key)) {
      return $this->getColorObject('black');
    }

    return clone $this->colors->get($key);
  }

  public function getFont(string $name, ?int $style = null): array
  {
    if (!$this->fonts->has($name)) {
      if (null !== ($k = $this->get($name))) {
        return $this->getFont($k, $style);
      }

      return $this->getFont('default', $style);
    }

    $font = clone $this->fonts->get($name);

    if (null !== $style) {
      $font->setStyle($style);
    }

    return $this->fonts->get($name)->toArray();
  }

  public function getBorderWidth(int|float $width = 1): int|float
  {
    return $this->getInt('borderWidth') * $width;
  }

  public function px2mm(string $k, int|float|null $default = null, int $dpi = 72): int|float
  {
    if (null !== ($value = $this->get($k, $default))) {
      return $this->helper->px2mm($value, $dpi);
    }

    throw new \Exception('Error in ' . __METHOD__ . ' : $k=' . $k);
  }

  public function px2pt(string $k, int $dpi = 72): int|float
  {
    if ($value = $this->getInt($k)) {
      return $this->helper->px2pt($value, $dpi);
    }

    throw new \Exception('Error in ' . __METHOD__ . ' : $k=' . $k);
  }

  public function mm2px(string $k, int $dpi = 72): int|float
  {
    if ($value = $this->get($k)) {
      return $this->helper->mm2px($value, $dpi);
    }

    throw new \Exception('Error in ' . __METHOD__ . ' : $k=' . $k);
  }

  public function percent2mm(string $k, int $base): int|float
  {
    if ($value = $this->get($k)) {
      return $this->helper->percent2mm($value, $base);
    }

    throw new \Exception('Error in ' . __METHOD__ . ' : $k=' . $k);
  }
}
