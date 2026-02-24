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
class Toc
{
  private array $marks = [];

  public function getItem(string|int $page): object
  {
    if (!isset($this->marks[$page])) {
      $this->marks[$page] = (object)[
        'page' => 1,
        'positions' => [],
      ];
    }

    return $this->marks[$page];
  }

  public function setPosition(string|int $key, array $data)
  {
    $item = $this->getItem($key);

    $item->positions[] = (object)[
      'p' => $data['p'],
      'x' => $data['x'],
      'y' => $data['y'],
      'w' => $data['w'],
      'h' => $data['h'],
    ];

    return $this;
  }

  public function setPage($key, int $page)
  {
    if (!isset($this->marks[$key])) {
      return;
    }
    $this->marks[$key]->page = $page;

    return $this;
  }

  public function hasMarks(): bool
  {
    return count($this->marks) > 0;
  }

  public function getMarks(): array|false
  {
    return true === $this->hasMarks() ? $this->marks : false;
  }
}
