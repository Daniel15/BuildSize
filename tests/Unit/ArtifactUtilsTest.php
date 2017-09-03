<?php

namespace Tests\Unit;

use App\ArtifactUtils;
use Tests\TestCase;

class ArtifactUtilsTest extends TestCase {
  /**
   * @dataProvider generalizeNameProvider
   */
  public function testGeneralizeName(string $name, string $expected) {
    $this->assertEquals($expected, ArtifactUtils::generalizeName($name));
  }

  public function generalizeNameProvider() {
    return [
      ['yarn-1.2.3.js', 'yarn-[version].js'],
      ['yarn-v1.0.0-20170829.1752.tar.gz', 'yarn-v[version].tar.gz'],
      ['hello-world.sqlite3', 'hello-world.sqlite3'],
      ['babel.js', 'babel.js'],
    ];
  }
}
