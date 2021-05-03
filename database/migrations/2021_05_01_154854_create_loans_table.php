<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrower_id')->default(0);
            $table->text('description')->nullable();
            $table->unsignedDecimal('amount')->default(0);
            $table->unsignedInteger('terms')->default('0'); //in years            
            $table->unsignedInteger('interest_rate')->default('0'); //5% per annum 
            $table->unsignedDecimal('total_interest')->default('0');
            $table->unsignedDecimal('running_balance')->default('0');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
