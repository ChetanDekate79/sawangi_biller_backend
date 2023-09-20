<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductivityController extends Controller
{


	public function getDeviceData($device, $date, $parameter)
    {
        $data = DB::select("
        select 
		if(T1.current <= T2.kw_range,T1.current,T1.current) as value , T2.device_name as name, T1.dt_time,
            meter_detail 
		from (				
				select  $parameter as current , DEVICE_ID, dt_time from productivity_data_logger T1
	        	where date = '$date'
	        	and T1.device_id = '$device' limit 720) T1
        join device_details_productivity T2
        on T1.DEVICE_ID = T2.device_id 
		order by T1.dt_time;" );


		/*$data = DB::table('view_data_rs485 T1')
				->join('device_details T2', 'T1.DEVICE_ID', '=', 'T2.device_id')
				->select($parameter.' as value' , 'T2.device_name as name','dt_time','meter_detail')
				->get();*/

		return [
			'data' => $data
		];
    }
	
	 public function getHeatmapData($flore, $category, $date)
    {
        $data = DB::select("
            select T1.hour as hour,
                time(max(T1.DT_TIME)) as time,
                max(T1.DATE) as date,
                max(T2.device_name) as name,
                max(location_id) as location,
                sum(kwh) as kwh,
                round(sum(time_to_sec(T1.running_time))/60) as running_time,
                round(sum(time_to_sec(T1.idle_time))/60) as idle_time,
                round(sum(time_to_sec(T1.off_time))/60) as off_time,
                round(sum(time_to_sec(T1.total_time))/60) as total_time,
                max(device_category) as category,
                max(device_category_code) as category_code
            from productivity_data_rs485_summary T1 join device_details_productivity T2
            on T1.DEVICE_ID = T2.device_id
            /*cross join rate_config on type ='money'*/
            where visibility = 1
            and cast(T1.dt_time as date) = '$date'
            and location_id = '$flore'
            and device_category_code = '$category'
            group by T1.hour, T1.device_id,T2.report_sort
            order by  T1.hour,T2.report_sort;");

		return [
			'data' => $data
		];
    }

}
