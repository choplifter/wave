<?php
// database/migrations/xxxx_xx_xx_create_tesla_api_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tesla_api_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('method');
            $table->string('path');
            $table->integer('status');
            $table->text('request_body')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tesla_api_transactions');
    }
};