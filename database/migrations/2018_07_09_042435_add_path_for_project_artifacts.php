<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPathForProjectArtifacts extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('project_artifacts', function (Blueprint $table) {
      $table->string('full_path');

      // In theory this should be unique, however old rows won't have a full_path yet.
      $table->index(['project_id', 'full_path']);

      // Combination of project_id and name will no longer be unique, so we need to drop this
      // unique index and recreate it as a regular index.
      $table->dropUnique(['project_id', 'name']);
      $table->index(['project_id', 'name']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('project_artifacts', function (Blueprint $table) {
      $table->dropColumn('full_path');
      $table->dropIndex(['project_id', 'full_path']);

      // Restore [project_id, name] to be a unique index
      $table->dropIndex(['project_id', 'name']);
      $table->unique(['project_id', 'name']);
    });
  }
}
