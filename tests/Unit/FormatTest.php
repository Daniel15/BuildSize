<?php

namespace Tests\Unit;

use App\Helpers\Format;
use Tests\TestCase;

class FormatTest extends TestCase {
  public function testFileSize() {
    $this->assertEquals('-2 bytes', Format::fileSize(-2));
    $this->assertEquals('0 bytes', Format::fileSize(0));
    $this->assertEquals('2 bytes', Format::fileSize(2));
    $this->assertEquals('1 KB', Format::fileSize(1024));
    $this->assertEquals('5.42 KB', Format::fileSize(5555));
    $this->assertEquals('1 MB', Format::fileSize(1048576));
    $this->assertEquals('-4.14 MB', Format::fileSize(-4343425));
  }

  public function testNewFileSizeWithPercentage() {
    $this->assertEquals('100 bytes (100%)', Format::newFileSizeWithPercentage(50, 100));
    $this->assertEquals('100 bytes (0%)', Format::newFileSizeWithPercentage(100, 100));
    $this->assertEquals('100 bytes (50%)', Format::newFileSizeWithPercentage(200, 100));
  }

  public function testDiffFileSizeWithPercentage() {
    $this->assertEquals('50 bytes (100%)', Format::diffFileSizeWithPercentage(50, 100));
    $this->assertEquals('0 bytes (0%)', Format::diffFileSizeWithPercentage(100, 100));
    $this->assertEquals('-100 bytes (50%)', Format::diffFileSizeWithPercentage(200, 100));
  }
}
