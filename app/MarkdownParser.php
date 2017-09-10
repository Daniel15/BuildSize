<?php

namespace App;

class MarkdownParser extends \Parsedown {
  private $title;

  /**
   * Overrides header handling to save the first header as the page title
   */
  protected function blockSetextHeader($Line, array $Block = null) {
    $header = parent::blockSetextHeader($Line, $Block);
    if ($header && !$this->title) {
      // Save first header as page title
      $this->title = $header['element']['text'];
    }
    return $header;
  }

  public function getTitle(): ?string {
    return $this->title;
  }

  /**
   * Overrides table handling to add Bootstrap classes
   */
  protected function blockTable($Line, array $Block = null) {
    $table = parent::blockTable($Line, $Block);
    if ($table) {
      $table['element']['attributes']['class'] = 'table table-striped';
    }
    return $table;
  }

  /**
   * Overrides link handling to remove ".md" from the link
   */
  function inlineLink($Excerpt) {
    $link = parent::inlineLink($Excerpt);
    if ($link && ends_with($link['element']['attributes']['href'], '.md')) {
      $link['element']['attributes']['href'] = str_before($link['element']['attributes']['href'], '.md');
    }
    return $link;
  }
}
