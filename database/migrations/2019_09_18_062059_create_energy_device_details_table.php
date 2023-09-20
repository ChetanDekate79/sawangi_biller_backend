<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnergyDeviceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('energy_device_details', function (Blueprint $table) {
            //$table->bigIncrements('id');
            $table->timestamps();
            $table->integer('device_id')->index();
            $table->integer('logger_id');
            $table->primary('device_id');
            $table->unique(['device_id','logger_id']);
            $table->integer('group_id');
            $table->integer('group_roots');
            $table->integer('department_id');
            $table->string('name');
            $table->integer('order');
            $table->integer('tree_level');
            $table->integer('parent_id');
            $table->integer('visibility');
            $table->integer('incommer_source_id');
            $table->integer('load');
            $table->string('ct_ratio');
            $table->integer('existance');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('energy_device_details');
    }
}
