<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\model\energy_device_detail;

use Faker\Generator as Faker;

$factory->define(energy_device_detail::class, function (Faker $faker) {
    return [
            //
        'device_id' =>  $faker->numberBetween(1,500),
        'logger_id' =>  $faker->numberBetween(1,5),
        'group_id' =>  $faker->numberBetween(1,5),
        'group_roots' =>  $faker->numberBetween(1,10),
        'department_id' =>  $faker->numberBetween(1,10),
        'name' =>  $faker->word,
        'order' =>  $faker->numberBetween(1,100),
        'tree_level' =>  $faker->numberBetween(1,5),
        'parent_id' =>  $faker->randomDigit,
        'visibility' =>  $faker->randomDigit,
        'incommer_source_id' =>  $faker->randomDigit,
        'load' =>  $faker->numberBetween(500,1000),
        'ct_ratio' => '100:1',
        'existance' =>  $faker->numberBetween(0,2),
    ];
});
