<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    //
    public function getDeviceStatus()
    {
      //  return energy_device_detail::all();
      $date = Carbon::now()->format('Y-m-d');
      //$date = '2019-11-28';
      $data_rs485 = DB::select("select T2.abbrivation_name as device_name,
            ifnull(dt_time,(date_sub('$date', interval 1 day  ))) as dt_time ,
            num_rows,
            T2.client_id,
            T2.device_id
            from (
            select
            max(dt_time) as dt_time,count(dt_time) as num_rows,
            max(T1.device_id) as device_id,
            max(T1.client_id) as  client_id
            from data_rs485 T1

            where date = date_sub('$date', interval 0 day)
            group by date,T1.device_id
            ) T1
           right join device_details T2 on T1.DEVICE_ID = T2.device_id");

      $productivity_data_logger = DB::select("select T2.abbrivation_name as device_name,
             ifnull(dt_time,(date_sub('$date', interval 1 day  ))) as dt_time ,
             num_rows,
            T2.client_id,
            T2.device_id
            from (
            select
            max(dt_time) as dt_time,count(dt_time) as num_rows,
            max(T1.device_id) as device_id,
            max(T1.client_id) as  client_id
            from data_logger T1

            where date = date_sub('$date', interval 0 day)
            group by date,T1.device_id
            ) T1
      right join device_details_productivity T2 on T1.DEVICE_ID = T2.device_id ");
      return [
            'project'=>'Zim Lab Limited',
            'data'=>[
                ['data'=>$this->updateStatus($data_rs485),
                'project'=> 'energy'],

                ['data'=>$this->updateStatus($productivity_data_logger),
                'project'=> 'productivity']
          ]
      ];
    }

    public function updateStatus($device_data){
        $device_data = json_decode(json_encode($device_data), True);

        $client = [];
        $cureent_date_time = Carbon::now();
        foreach($device_data as $key=>$data){
           $device_db_time =  Carbon::parse($data['dt_time']);
            $diff = ($cureent_date_time->diffInSeconds($device_db_time))/60;
            if($diff <= 10){
                $device_data[$key]['status'] = 1;
                $device_data[$key]['diff'] = round($diff,2);
                $device_data[$key]['comment'] = "Device is live at ".$data['dt_time']." [ last data is before  $diff minutes]";
                $client[$data['client_id']]['name'] = $data['client_id'];
                $client[$data['client_id']]['device'] = $device_data;
            }
            else if($diff > 10 && $diff <= 20){
                $device_data[$key]['status'] = 2;
                $device_data[$key]['diff'] = round($diff,2);
                $device_data[$key]['comment'] = "Device was live at ".$data['dt_time']." [ last data is before  $diff minutes]";
                $client[$data['client_id']]['name'] = $data['client_id'];
                $client[$data['client_id']]['device'] = $device_data;
            }
            else if($diff > 20 && $diff <= 1440){
                $device_data[$key]['status'] = 0;
                $device_data[$key]['diff'] =round($diff,2);
                $device_data[$key]['comment'] = "Data Not pressent  From-".$data['dt_time']." [ last data is before  $diff minutes]";
                $client[$data['client_id']]['name'] = $data['client_id'];
                $client[$data['client_id']]['device'] = $device_data;
            }
            else{
                $device_data[$key]['status'] = 3;
                $device_data[$key]['diff'] = 'na';
                $device_data[$key]['comment'] = "anable to find data for durrent date ";
                $client[$data['client_id']]['name'] = $data['client_id'];
                $client[$data['client_id']]['device'] = $device_data;
            }
        }

        return $device_data;
    }
}
