<?php

namespace App\model;
use App\model\daily_energy;
use Illuminate\Database\Eloquent\Model;

class energy_device_detail extends Model
{
    //
    protected $primaryKey = 'device_id';
    public function daily_energy(){
       return $this->hasMany(daily_energy::class,'device_id');
   }
}
