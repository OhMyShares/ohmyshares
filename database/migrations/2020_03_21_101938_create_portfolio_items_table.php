<?php

use App\Models\Trade;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortfolioItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('created_at', false, true);
            $table->integer('updated_at', false, true);
            $table->unsignedBigInteger('portfolio_id');
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('ticker');
            $table->string('exchange', 100)->default('');
            $table->unsignedSmallInteger('asset')->default(1);
            $table->decimal('count', 20, 10)->default(0);
            $table->decimal('sum', 20, 10)->default(0);
            $table->decimal('average_price', 20, 10)->default(0);
            $table->decimal('earned_dividends', 20, 10)->default(0);
            $table->decimal('earned_coupons', 20, 10)->default(0);
            $table->decimal('paid_fees', 20, 10)->default(0);
            $table->decimal('paid_taxes', 20, 10)->default(0);
            //$table->decimal('part_percent')->default(0);
            $table->integer('trades')->default(0);
            $table->string('currency_code', 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portfolio_items');
    }
}
