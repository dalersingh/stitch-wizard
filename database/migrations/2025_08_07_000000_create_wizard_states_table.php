<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wizard_states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('session_id', 100);
            $table->string('wizard_id', 100);
            $table->json('state')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'wizard_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wizard_states');
    }
};
