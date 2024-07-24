<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('inventory_id');
            $table->enum('transaction_type', ['delivery', 'inventory_management']);
            $table->integer('in_value')->nullable();
            $table->integer('out_value')->nullable();
            $table->enum('status', ['pending', 'en-route', 'delivered'])->nullable();
            $table->timestamp('order_date');
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
