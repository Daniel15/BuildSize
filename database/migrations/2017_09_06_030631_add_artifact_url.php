<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArtifactURL extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('build_artifacts', function (Blueprint $table) {
      $table->string('url', 4000)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('build_artifacts', function (Blueprint $table) {
      $table->dropColumn('url');
    });
  }
}
