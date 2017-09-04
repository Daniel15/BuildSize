<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchesTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('branches', function (Blueprint $table) {
      $table->increments('id');
      $table->timestamps();
      $table->string('org_name', 100);
      $table->string('repo_name', 100);
      $table->string('branch', 100);
      $table->string('latest_commit', 40);
      $table->string('author', 100);

      $table->unique(['org_name', 'repo_name', 'branch']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('branches');
  }
}
