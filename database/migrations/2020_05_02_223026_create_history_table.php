<?php

use App\Models\Trade;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 100);
            $table->string('exchange', 100)->default('');
            $table->unsignedSmallInteger('asset')->default(1);
            $table->date('date');
            $table->decimal('open', 13, 5)->nullable();
            $table->decimal('high', 13, 5)->nullable();
            $table->decimal('low', 13, 5)->nullable();
            $table->decimal('close', 13, 5)->nullable();
            $table->bigInteger('volume')->unsigned()->nullable();
            $table->decimal('dividends')->default(0);
            $table->decimal('stock_splits')->default(0);

            $table->unique(['ticker', 'exchange', 'asset', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history');
    }
}
