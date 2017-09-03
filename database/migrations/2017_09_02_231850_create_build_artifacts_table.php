<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildArtifactsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('build_artifacts', function (Blueprint $table) {
      $table->increments('id');
      $table->timestamps();
      $table->string('filename');
      $table->bigInteger('size')->unsigned();
      $table->integer('build_id')->unsigned();
      $table->integer('project_artifact_id')->unsigned();

      $table->foreign('build_id')
        ->references('id')->on('builds')
        ->onDelete('cascade');

      $table->foreign('project_artifact_id')
        ->references('id')->on('project_artifacts')
        ->onDelete('cascade');

      $table->unique(['build_id', 'project_artifact_id']);
    }
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('build_artifacts');
  }
}
