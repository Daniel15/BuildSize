<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinChangeForComment extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('projects', function (Blueprint $table) {
      $table->integer('min_change_for_comment')
        ->default(750);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('projects', function (Blueprint $table) {
      $table->dropColumn('min_change_for_comment');
    });
  }
}
