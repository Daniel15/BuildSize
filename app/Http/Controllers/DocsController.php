<?php

namespace App\Http\Controllers;

use App\MarkdownParser;

class DocsController extends Controller {
  public function __invoke($relative_path = 'index') {
    $path = realpath(__DIR__ . '/../../../docs/' . $relative_path . '.md');
    if ($path === false || !ends_with($path, '.md')) {
      abort(404, 'Documentation page not found');
    }

    $markdown = new MarkdownParser();
    $content = $markdown->text(file_get_contents($path));
    return view('doc', [
      'content' => $content,
      'title' => $markdown->getTitle()
    ]);
  }
}
