<?php

namespace NYPL\SchemaBuilder;

class Wrapper {

  protected $wrapperName = '';

  protected $content = '';

  /**
   * @var WrapperAttribute[]
   */
  protected $wrapperAttributes;

  /**
   * @param string $wrapperName
   */
  public function __construct($wrapperName = '') {
    $this->setWrapperName($wrapperName);
  }

  public function addAttribute($name = '', $value = '') {
    $this->wrapperAttributes[] = new WrapperAttribute($name, $value);
  }

  /**
   * @param WrapperAttribute[] $wrapperAttributes
   *
   * @return string
   */
  public function output(array $wrapperAttributes = []) {
    $wrapperAttributes = array_merge($this->getWrapperAttributes(), $wrapperAttributes);

    $output = '<' . $this->getWrapperName();

    foreach ($wrapperAttributes as $attribute) {
      $output .= ' ';

      $output .= htmlentities($attribute->getName());

      if ($attribute->getValue()) {
        $output .= '="' . htmlentities($attribute->getValue()) . '"';
      }
    }

    if ($this->getContent()) {
      $output .= '>' . htmlentities($this->getContent()) . '</' . $this->getWrapperName() . '>';
    }
    else {
      $output .= '>';
    }

    if ($this->wrapperName != 'span') {
      $output .= "\r\n";
    }

    return $output;
  }

  /**
   * @return WrapperAttribute[]
   */
  public function getWrapperAttributes() {
    return $this->wrapperAttributes;
  }

  /**
   * @return string
   */
  public function getWrapperName() {
    return $this->wrapperName;
  }

  /**
   * @param string $wrapperName
   */
  public function setWrapperName($wrapperName) {
    $this->wrapperName = $wrapperName;
  }

  /**
   * @return string
   */
  protected function getContent() {
    return $this->content;
  }

  /**
   * @param string $content
   */
  public function addContent($content = '') {
    $this->content .= $content;
  }
}
