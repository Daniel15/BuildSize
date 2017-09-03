<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('projects', function (Blueprint $table) {
      $table->increments('id');
      $table->timestamps();
      $table->enum('host', ['github']);
      $table->string('org_name');
      $table->string('repo_name');
      $table->string('url')->nullable();
      $table->boolean('active')->default(true);

      $table->index(['org_name', 'repo_name']);
    }
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('projects');
  }
}
