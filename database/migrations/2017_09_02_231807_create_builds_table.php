<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('builds', function (Blueprint $table) {
      $table->increments('id');
      $table->timestamps();
      $table->integer('project_id')->unsigned();
      $table->string('identifier');
      $table->string('commit', 40);
      $table->string('committer', 100)->index();
      $table->integer('pull_request')->unsigned()->nullable();
      $table->string('base_branch', 100)->nullable();
      $table->string('base_commit', 40)->nullable();
      $table->string('branch', 100);

      $table->foreign('project_id')
        ->references('id')->on('projects')
        ->onDelete('cascade');

      $table->unique(['project_id', 'identifier']);
    }
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('builds');
  }
}
