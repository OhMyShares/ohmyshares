<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('ticker');
            $table->string('currency_code', 3);
            $table->string('name')->nullable();
            $table->text('info')->nullable();

            $table->unique(['user_id', 'ticker'], 'idx_unique_name');
        });

        Schema::create('custom_assets_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('custom_asset_id');
            $table->foreign('custom_asset_id')->references('id')->on('custom_assets')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->integer('created_at', false, true);
            $table->decimal('price', 13, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_assets_prices');
        Schema::dropIfExists('custom_assets');
    }
}
