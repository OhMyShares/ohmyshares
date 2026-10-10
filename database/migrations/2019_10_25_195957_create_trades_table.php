<?php

use App\Models\Portfolio;
use App\Models\Trade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedBigInteger('portfolio_id');
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedSmallInteger('type')->default(1);
            $table->unsignedSmallInteger('asset')->default(1);
            $table->string('currency_code', 3)->default('USD');
            $table->string('ticker');
            $table->string('exchange', 100)->default('');
            $table->integer('created_at', false, true);
            $table->decimal('cost', 13, 5)->nullable();
            $table->decimal('shares', 20, 10)->nullable();
            $table->decimal('fee', 8, 2)->default(0);
            $table->string('fee_currency_code', 3)->default('USD');
            $table->decimal('sum', 13, 5);
            $table->unsignedBigInteger('related_trade_id')->nullable();
            $table->string('notes')->nullable();
            $table->string('txn')->nullable()->unique();
            $table->boolean('is_wo_money')->default(false)->comment('Ignore affecting on money');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trades');
    }
}
