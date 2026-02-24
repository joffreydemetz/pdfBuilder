<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

use JDZ\Pdf\Toc;
use JDZ\Pdf\Pdf;

/** 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Helper
{
  public function exportToc(Toc $toc, Pdf $pdf): void
  {
    if (null === $toc) {
      return;
    }

    if (false !== ($marks = $toc->getMarks())) {
      set_time_limit(120);

      foreach ($marks as $data) {
        foreach ($data->positions as $item) {
          $pdf->setPage($item->p);

          $pdf->SetFillColorArray([255, 255, 255]);
          $pdf->SetTextColorArray([0, 0, 0]);
          $pdf->SetFont($pdf->get('tocFont'), '', $this->px2pt(7.5));

          $pdf->SetX($item->x);
          $pdf->SetY($item->y, false);

          $link = $pdf->AddLink();
          $pdf->SetLink($link, 0, $data->page + $pdf->get('izPageNoOffset'));

          $pdf->setCellPaddings(0, $this->px2mm(3), $this->px2mm(10), $this->px2mm(2));
          $pdf->Cell($item->w, $item->h, $data->page, 0, 0, 'R', false, $link);
        }
      }

      $pdf->lastPage();
    }
  }

  public function cleanText(string $str): string
  {
    $str = str_replace("\xE2\x80\x99", "'", $str);
    $str = str_replace("\xE2\x80\x98", "'", $str);
    return $str;
  }

  public function uppercaseString(string $str): string
  {
    $mbEncoding = mb_internal_encoding();
    mb_internal_encoding('UTF-8');
    $str = mb_strtoupper($str);
    mb_internal_encoding($mbEncoding);
    return $str;
  }

  public function px2mm(int|float $px, int $dpi = 72): int|float
  {
    $dpcm = $dpi * 0.393701;

    /* switch($dpi){
      case 72:
        $dpcm = 28.35; // ≈ 30
        break;
      case 96:
        $dpcm = 37.8; // ≈ 40
        break;
      case 150:
        $dpcm = 59.06; // ≈ 60
        break;
      case 300:
        $dpcm = 118.11; // ≈ 120
        break;
      default:
        throw new \Exception($dpi);
        break;
    } */

    $cm = $px / $dpcm; // round($dpcm, 1); // en cm
    $mm = $cm * 10; // en mm
    return $mm;
  }

  public function px2pt(int|float $px, int $dpi = 72): int|float
  {
    $pt = ($px / $dpi) * 72;
    return round($pt, 1);
  }

  public function percent2mm(int|float $percent, int|float $base): int|float
  {
    return $base * ($percent / 100);
  }

  public function mm2px(int|float $mm, int $dpi = 72): int|float
  {
    $in = $mm * 0.039370079;
    $px = $in * $dpi;
    return round($px);
  }

  public function border(int|float $size, string|array $color, bool $rounded = false, bool $dash = false): array
  {
    $data = [];
    $data['width'] = $size;
    $data['color'] = $color;
    $data['cap']   = 'square'; // round
    $data['join']  = 'round';
    $data['dash']  = 0;
    $data['phase'] = 0;

    if (false !== $dash) {
      $data['cap']  = 'butt';
      $data['join'] = 'miter';
      $data['dash'] = $dash;
    }

    if ($rounded === true) {
      $data['cap']  = 'butt';
      $data['join'] = 'miter';
    }

    return $data;
  }

  public function borders(string $sides = 'all', float|int $size = 1, string|array|null $color = null, bool $rounded = false, bool $dash = false): array
  {
    $borders = [];

    if (strpos('T', $sides) !== false && strpos('R', $sides) !== false && strpos('B', $sides) !== false && strpos('L', $sides) !== false) {
      $sides = 'all';
    }

    if ($sides === 'all') {
      $borders['all'] = $this->border($size, $color, $rounded, $dash);
      return $borders;
    }

    while (strlen($sides) > 0) {
      $side = substr($sides, 0, 1);
      $borders[$side] = $this->border($size, $color, $rounded, $dash);
      $sides = substr($sides, 1);
    }

    return $borders;
  }

  public function getImageInfos(string $path): \stdClass
  {
    $img = new \stdClass;

    if (!file_exists($path)) {
      throw new \Exception('image ' . $path . ' not found');
    }

    // $fi = new \SplFileInfo($path);

    list($width, $height, $type) = \getimagesize($path);

    $img->path = $path;
    $img->width = $width;
    $img->height = $height;
    $img->type = $type;

    if (\IMAGETYPE_JPEG === $type) {
      $src = \imagecreatefromjpeg($path);
    } elseif (\IMAGETYPE_GIF === $type) {
      $src = \imagecreatefromgif($path);
    } elseif (\IMAGETYPE_PNG === $type) {
      $src = \imagecreatefrompng($path);
    } else {
      return $img;
    }

    list($resX, $resY) = \imageresolution($src);
    $img->resX = $resX;
    $img->resY = $resY;

    return $img;
  }

  public function imageSize(string $path, int|float|null $w = null, int|float|null $h = null): array
  {
    static $images;

    if (!isset($images)) {
      $images = [];
    }

    $fi = new \SplFileInfo($path);

    $key  = $fi->getFilename() . '.' . ($w ? $w : '-') . '.' . ($h ? $h : '-');

    if (isset($images[$key])) {
      return $images[$key];
    }

    if (!file_exists($path)) {
      throw new \Exception('image ' . $path . ' not found');
    }

    $img = $this->getImageInfos($path);

    $images[$key] = [
      'path' => $img->path,
      'oriW' => $img->width,
      'oriH' => $img->height,
      'wPx'  => $img->width,
      'hPx'  => $img->height,
      'resX' => $img->resX,
      'resY' => $img->resY,
    ];

    $maxHpx = null !== $h ? $this->mm2px($h, $img->resY) : 0;
    $maxWpx = null !== $w ? $this->mm2px($w, $img->resX) : 0;

    if (null !== $h && $maxHpx && $img->height > $maxHpx) {
      $images[$key]['hPx'] = $maxHpx;
      $images[$key]['wPx'] = abs($maxHpx * $img->width / $img->height);
    } elseif (null !== $w && $maxWpx && $img->width > $maxWpx) {
      $images[$key]['wPx'] = $maxWpx;
      $images[$key]['hPx'] = abs($maxWpx * $img->height / $img->width);
    }

    $images[$key]['w'] = $this->px2mm($images[$key]['wPx'], $img->resY);
    $images[$key]['h'] = $this->px2mm($images[$key]['hPx'], $img->resX);

    return $images[$key];
  }

  public function spanner(int $height, string $text): string
  {
    return '<span style="line-height:' . $height . 'pt;">' . $text . '</span>';
  }
}
