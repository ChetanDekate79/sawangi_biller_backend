<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         //$this->call(UsersTableSeeder::class);
        // factory(App\model\energy_device_detail::class,50)->create();
        // factory(App\model\daily_energy::class,100)->create();
         factory(App\model\hourly_energy::class,2400)->create();

    }
}
