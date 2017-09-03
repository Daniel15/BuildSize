<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGithubInstallsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('github_installs', function (Blueprint $table) {
      $table->increments('id');
      $table->timestamps();
      $table->integer('install_id')->unsigned()->unique();
      $table->string('access_token', 1000)->nullable();
      $table->dateTime('access_token_expiry')->nullable();
      $table->string('org_name')->unique();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('github_installs');
  }
}
