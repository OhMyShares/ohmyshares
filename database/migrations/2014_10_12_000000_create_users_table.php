<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('email_verified_at', false, true)->nullable();
            $table->string('password');
            //$table->string('api_token', 80)->unique()->nullable()->default(null);
            $table->rememberToken();
            $table->boolean('is_test')->default(0);
            $table->boolean('is_ignore_funds_sufficiency')->default(0);
            $table->integer('created_at', false, true);
            $table->integer('updated_at', false, true);
            $table->smallInteger('role', false, true)->default(0);
            $table->string('locale', 10)->nullable();
            $table->integer('last_active_at', false, true)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
