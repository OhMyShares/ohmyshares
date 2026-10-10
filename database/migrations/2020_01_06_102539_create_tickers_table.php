<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTickersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickers', function (Blueprint $table) {
            $table->date('created_at')->nullable();
            $table->string('ticker', 100);
            $table->string('exchange', 100)->default('');
            $table->string('isin', 100)->nullable();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('country')->nullable();
            $table->decimal('nominal', 15, 10)->nullable();
            $table->decimal('coupon_percent', 3, 2)->nullable();
            $table->unsignedSmallInteger('type');
            $table->boolean('is_hidden')->default(false);
            $table->string('currency_code', 3)->nullable();
            $table->string('info_url')->nullable();
            $table->string('disclosure_url')->nullable();

            $table->primary(['ticker', 'exchange']);
            $table->index('ticker');
            $table->index('isin');
            $table->index('name');
            $table->index('exchange');
            $table->index('category');
            $table->index('country');
            $table->index('type');
            $table->index('currency_code');
        });
        DB::statement('ALTER TABLE tickers ADD FULLTEXT search(ticker, name, isin)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickers');
    }
}
