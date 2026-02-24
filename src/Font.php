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
class Font
{
  private string $name;
  private ?int $size = null;
  private ?string $style = null;

  public function __construct(string $name)
  {
    $this->name = $name;
  }

  public function setSize(int $size)
  {
    $this->size = $size;
    return $this;
  }

  public function setStyle(string $style)
  {
    $this->style = $style;
    return $this;
  }

  public function toArray()
  {
    return [
      $this->name,
      $this->style,
      $this->size
    ];
  }
}
