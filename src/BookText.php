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
class BookText
{
  const DEFAULT_LANGUAGE = 'en';

  protected array $availableLanguages = ['fr', 'en', 'it'];
  protected string $language = self::DEFAULT_LANGUAGE;
  protected array $data = [];

  public function setAvailableLanguages(array $availableLanguages): self
  {
    $this->availableLanguages = $availableLanguages;
    return $this;
  }

  public function addAvailableLanguage(string $availableLanguage): self
  {
    $this->availableLanguages[] = $availableLanguage;
    return $this;
  }

  public function setLanguage(string $language): self
  {
    if ($language && in_array($language, $this->availableLanguages)) {
      $this->language = $language;
    } else {
      $this->language = self::DEFAULT_LANGUAGE;
    }

    return $this;
  }

  public function addStrings(string $lang, array $strings): self
  {
    if (!isset($this->data[$lang])) {
      $this->data[$lang] = [];
    }
    $this->data[$lang] = array_merge($this->data[$lang], $strings);
    return $this;
  }

  public function get(string $k): string
  {
    $k = strtoupper($k);

    if (isset($this->data[$this->language])) {
      if (isset($this->data[$this->language][$k])) {
        return $this->data[$this->language][$k];
      }
    }

    if ($this->language !== self::DEFAULT_LANGUAGE) {
      if (isset($this->data[self::DEFAULT_LANGUAGE])) {
        if (isset($this->data[self::DEFAULT_LANGUAGE][$k])) {
          return $this->data[self::DEFAULT_LANGUAGE][$k];
        }
      }
    }

    return $k;
  }

  public function plural(string $k, int $num = 0): string
  {
    $num = (int)$num;
    if ($num > 1) {
      return $num . ' ' . $this->get($k . '_MORE');
    }
    if ($num === 1) {
      return $num . ' ' . $this->get($k . '_1');
    }
    return $num . ' ' . $this->get($k . '_0');
  }

  public function pluralSimple(string $k, int $num = 0): string
  {
    $num = (int)$num;
    if ($num > 1) {
      return $this->get($k . '_MORE');
    }
    if ($num === 1) {
      return $this->get($k . '_1');
    }
    return $this->get($k . '_0');
  }

  public function sprintf(string $k): string
  {
    $args = func_get_args();
    $count = count($args);
    if ($count > 0) {
      $args[0] = $this->get($k);
      $args[0] = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', $args[0]);
      return call_user_func_array('sprintf', $args);
    }
    return '';
  }

  public function printf(string $k): string
  {
    $args = func_get_args();
    $count = count($args);
    if ($count > 0) {
      $args[0] = $this->get($k);
      return call_user_func_array('printf', $args);
    }
    return '';
  }
}
