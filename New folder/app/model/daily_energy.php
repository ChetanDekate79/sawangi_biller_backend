<?php

namespace App\model;
use App\model\energy_device_detail;
use Illuminate\Database\Eloquent\Model;

class daily_energy extends Model
{
    //
    protected $primaryKey = 'device_id';
    public function device_details(){
        return $this->belongsTo(energy_device_detail::class,'device_id');
    }
}
