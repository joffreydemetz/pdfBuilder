<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Color
{
  public string $name;
  private string $hex;
  private int $r;
  private int $g;
  private int $b;
  private float $hue;
  private float $saturation;
  private float $lightness;

  public function __construct(array|string|null $value = null)
  {
    if ($value) {
      $this->parse($value);
    }
  }

  public function parse(array|string $value)
  {
    if (\is_string($value)) {
      $this->fromHex($value);
    } else {
      $this->fromArray($value);
    }

    $this->hex = $this->rgbToHex($this->r, $this->g, $this->b);
    $this->name = preg_replace("/[^0-9A-Fa-f]/", "", $this->hex);

    list($h, $s, $l) = $this->rgbToHsv($this->r, $this->g, $this->b);

    $this->hue        = $h;
    $this->saturation = $s;
    $this->lightness  = $l;

    //$this->reRgb = $this->hsvToRgb($this->hue, $this->saturation, $this->lightness);

    return $this;
  }

  public function lighten(int $percent)
  {
    if ($percent > 0 && $percent < 100) {
      $percent /= 100;

      $this->lightness = min(1, $this->lightness + $percent);

      $rgb = $this->hsvToRgb($this->hue, $this->saturation, $this->lightness);
      $this->parse($rgb);
    }

    return $this;
  }

  public function darken(int $percent)
  {
    if ($percent > 0 && $percent < 100) {
      $percent /= 100;

      $this->lightness = max(1, $this->lightness - $percent);

      $rgb = $this->hsvToRgb($this->hue, $this->saturation, $this->lightness);

      $this->parse($rgb);
    }

    return $this;
  }

  public function toArray(): array
  {
    return [$this->r, $this->g, $this->b];
  }

  private function fromArray(array $rgb): void
  {
    $this->r = (int)$rgb[0];
    $this->g = (int)$rgb[1];
    $this->b = (int)$rgb[2];
  }

  private function fromHex(string $hex): void
  {
    list($r, $g, $b) = $this->hexToRgb($hex);

    $this->r = $r;
    $this->g = $g;
    $this->b = $b;
  }

  private function hexToRgb(string $hex): array
  {
    $hex = preg_replace("/[^0-9A-Fa-f]/", "", $hex);

    if (strlen($hex) === 3) {
      $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
      $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
      $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } else {
      $colorVal = hexdec($hex);
      $r = 0xFF & ($colorVal >> 0x10);
      $g = 0xFF & ($colorVal >> 0x8);
      $b = 0xFF & $colorVal;
    }

    return [
      (int)$r,
      (int)$g,
      (int)$b
    ];
  }

  private function rgbToHex(int $r, int $g, int $b): string
  {
    $hex = '#';
    $hex .= str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
    $hex .= str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
    $hex .= str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    return strtoupper($hex);
  }

  private function rgbToHsv(int $r, int $g, int $b): array
  {
    $var_R = ($r / 255);
    $var_G = ($g / 255);
    $var_B = ($b / 255);
    $var_Min = min($var_R, $var_G, $var_B);
    $var_Max = max($var_R, $var_G, $var_B);
    $del_Max = $var_Max - $var_Min;

    if ($del_Max == 0) {
      $h = 0;
      $s = 0;
      return [0, 0, $var_Max];
    }

    $v = $var_Max;
    $s = $del_Max / $var_Max;
    $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
    $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
    $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;
    if ($var_R == $var_Max) $h = $del_B - $del_G;
    else if ($var_G == $var_Max) $h = (1 / 3) + $del_R - $del_B;
    else if ($var_B == $var_Max) $h = (2 / 3) + $del_G - $del_R;
    if ($h < 0) $h++;
    if ($h > 1) $h--;

    return [$h, $s, $v];
  }

  private function hsvToRgb(float $h, float $s, float $v): array
  {
    if ($s == 0) {
      $r = $g = $b = $v * 255;
      return [$r, $g, $b];
    }

    $var_H = $h * 6;
    $var_i = floor($var_H);
    $var_1 = $v * (1 - $s);
    $var_2 = $v * (1 - $s * ($var_H - $var_i));
    $var_3 = $v * (1 - $s * (1 - ($var_H - $var_i)));

    switch ($var_i) {
      case 0:
        $var_R = $v;
        $var_G = $var_3;
        $var_B = $var_1;
        break;
      case 1:
        $var_R = $var_2;
        $var_G = $v;
        $var_B = $var_1;
        break;
      case 2:
        $var_R = $var_1;
        $var_G = $v;
        $var_B = $var_3;
        break;
      case 3:
        $var_R = $var_1;
        $var_G = $var_2;
        $var_B = $v;
        break;
      case 4:
        $var_R = $var_3;
        $var_G = $var_1;
        $var_B = $v;
        break;
      default:
        $var_R = $v;
        $var_G = $var_1;
        $var_B = $var_2;
        break;
    }

    $r = $var_R * 255;
    $g = $var_G * 255;
    $b = $var_B * 255;

    return [$r, $g, $b];
  }
}
