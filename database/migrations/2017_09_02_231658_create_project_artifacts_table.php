<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectArtifactsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('project_artifacts', function (Blueprint $table) {
      $table->increments('id');
      $table->timestamps();
      $table->string('name');
      $table->integer('project_id')->unsigned();

      $table->unique(['project_id', 'name']);

      $table->foreign('project_id')
        ->references('id')->on('projects')
        ->onDelete('cascade');
    }
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('project_artifacts');
  }
}
