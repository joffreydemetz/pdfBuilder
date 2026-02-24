<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Pdf;

use JDZ\Pdf\Toc;
use JDZ\Pdf\BookText;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Book
{
  public ?Toc $toc;
  public BookText $text;

  public function __construct(?Toc $toc = null)
  {
    $this->toc = $toc;
    $this->text = new BookText();
  }
}
