<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyEnergiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_energies', function (Blueprint $table) {
           // $table->bigIncrements('id');
            $table->timestamps();
            $table->date('date')->index();
            $table->date('date_time');
            $table->integer('device_id')->index();
            $table->float('logger_id')->index();
            $table->primary(['device_id','logger_id','date',]);
            $table->index(['logger_id','device_id','date']);
            $table->unique(['device_id','logger_id','date',]);
            $table->foreign('device_id')->references('device_id')->on('energy_device_details');
            $table->float('rs485_1');
            $table->float('rs485_2');
            $table->float('rs485_3');
            $table->float('rs485_4');
            $table->float('rs485_5');
            $table->float('rs485_6');
            $table->float('rs485_7');
            $table->float('rs485_8');
            $table->float('rs485_9');
            $table->float('rs485_10');
            $table->float('rs485_11');
            $table->float('rs485_12');
            $table->float('rs485_13');
            $table->float('rs485_14');
            $table->float('rs485_15');
            $table->float('rs485_16');
            $table->float('rs485_17');
            $table->float('rs485_18');
            $table->float('rs485_19');
            $table->float('rs485_20');
            $table->float('rs485_21');
            $table->float('rs485_22');
            $table->float('rs485_23');
            $table->float('rs485_24');
            $table->float('rs485_25');
            $table->float('rs485_26');
            $table->float('rs485_27');
            $table->float('rs485_28');
            $table->float('rs485_29');
            $table->float('rs485_30');
            $table->float('rs485_31');
            $table->float('rs485_32');
            $table->float('rs485_33');
            $table->float('rs485_34');
            $table->float('rs485_35');
            $table->float('rs485_36');
            $table->float('rs485_37');
            $table->float('rs485_38');
            $table->float('rs485_39');
            $table->float('rs485_40');
            $table->float('rs485_41');
            $table->float('rs485_42');
            $table->float('rs485_43');
            $table->float('rs485_44');
            $table->float('rs485_45');
            $table->float('rs485_46');
            $table->float('rs485_47');
            $table->float('rs485_48');
            $table->float('energy');
            $table->float('kwh');
            $table->float('kwh_min');
            $table->float('kwh_max');
            $table->float('kwh_avg');
            $table->integer('running_time');
            $table->integer('off_time');
            $table->integer('idle_time');
            $table->float('production');
            $table->float('eff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_energies');
    }
}
