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
      ['yarn-1.0.0-1.noarch.rpm', 'yarn-[version].noarch.rpm'],
      ['yarn-1.0.0_20170905.1413-1.noarch.rpm', 'yarn-[version].noarch.rpm'],
      ['hello-world.sqlite3', 'hello-world.sqlite3'],
      ['babel.js', 'babel.js'],
      ['babel.3e0de52baee579f5b435.js', 'babel.[hash].js'],
      ['babel.v2.4.1.3e0de52baee579f5b435.js', 'babel.v[version].[hash].js'],
      ['overenthusiastically.js', 'overenthusiastically.js'],
      ['noextension', 'noextension'],
    ];
  }
}
