<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('number', 12);
            $table->string('conversation_id', 256);
            $table->string('uuid', 256);
            $table->string('direction', 10);
            $table->integer('status'); //0->started, 1->ringing, 2->answered, 3->completed, 4->aborted
            $table->text('raw_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_logs');
    }
}
