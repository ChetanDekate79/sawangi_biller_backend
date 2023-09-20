<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Machine extends Controller
{
    //
	 CONST CACHE_KEY = 'live_pichart';
    public function getCacheKey()
    {
        return self::CACHE_KEY;
    }
    function getMachineDataLive(){
		 $cacheKey = $this->getCacheKey();
         $data = cache()->remember($cacheKey, Carbon::now()->addMinutes(1), function (){
			$latest_date_time = DB::select('select date , dt_time as time from productivity_data_rs485_summary
			order by date desc,dt_time desc
			limit 1;');
		// return  $latest_date_time[0]->date;
			$date = $latest_date_time[0]->date;
			$time = $latest_date_time[0]->time;

			return $this->getMachineDataForDate($date);
  		});
        return $data;
    }
    function getMachineDataForDate($date){
        $header =  DB::select("
            select
                T1.DATE as date,
                (T2.device_name) as name,
                (location_id) as location,
				T2.device_id as device_id,
                (kwh) as kwh,
                (device_category) as category,
                (device_category_code) as category_code,
                round((time_to_sec(T1.running_time))/60) as running_time,
                round((time_to_sec(T1.idle_time))/60) as idle_time,
                round((time_to_sec(T1.off_time))/60) as off_time,
                round((time_to_sec(T1.total_time))/60) as total_time,
                round(((time_to_sec(T1.running_time))/(time_to_sec(T1.total_time)))*100) as eff,
                if(T1.current <=T2.kw_range,0,1) as status

            from productivity_data_logger_summary T1 join device_details_productivity T2
            on T1.DEVICE_ID = T2.device_id
            /*cross join rate_config on type ='money'*/
            where visibility = 1
            and T1.date = '$date'
            /*group by T1.DATE, T1.device_id*/
            order by  report_sort;");
            return [
                'data'=>  $this->transformHeatmapData($header,$date)
                ];
     /*return [
         'data'=> $data
     ];*/
    }


    function getMachineDataForMonth($date){
        $header =  DB::select("
        /* for selected month */
        select
            max(T1.DATE) as date,
            max(T2.device_name) as name,
            max(T1.date) as max_date,
            min(T1.date) as min_date,
            month(max(T1.date)) as month_no,
            monthname(max(T1.date)) as month,
            count(T1.DATE) as no_of_days,
            max(location_id) as location,
            max(device_category) as category,
            max(device_category_code) as category_code,
            sum(kwh) as kwh,
            round(sum(time_to_sec(T1.running_time))/60) as running_time,
            round(sum(time_to_sec(T1.idle_time))/60) as idle_time,
            round(sum(time_to_sec(T1.off_time))/60) as off_time,
            round(sum(time_to_sec(T1.total_time))/60) as total_time,
            round((sum(time_to_sec(T1.running_time))/sum(time_to_sec(T1.total_time)))*100) as eff
           /* if(avg(T1.current)>=T2.kw_range,1,0) as status*/

        from productivity_data_logger_summary T1 join device_details_productivity T2
        on T1.DEVICE_ID = T2.device_id
        /*cross join rate_config on type ='money'*/
        where visibility = 1
        and month(T1.date)= month('$date')
        and year(T1.date) = year('$date')
        group by T1.device_id
        order by  report_sort;");

        return [
            'data'=>  $this->transformHeatmapData($header,$date)
            ];
     /*return [
         'data'=> $data
     ];*/
    }

    function getMachineDataForYear($date){
        $header =  DB::select("
            select
                max(T1.DATE) as date,
                max(T2.device_name) as name,
                max(T1.date) as max_date,
                min(T1.date) as min_date,
                month(max(T1.date)) as month_no,
                monthname(max(T1.date)) as month,
                count(T1.DATE) as no_of_days,
                count(distinct month(T1.date)) as no_of_months,
                max(location_id) as location,
                max(device_category) as category,
                max(device_category_code) as category_code,
                sum(kwh) as kwh,
                round(sum(time_to_sec(T1.running_time))/60) as running_time,
                round(sum(time_to_sec(T1.idle_time))/60) as idle_time,
                round(sum(time_to_sec(T1.off_time))/60) as off_time,
                round(sum(time_to_sec(T1.total_time))/60) as total_time,
                round((sum(time_to_sec(T1.running_time))/sum(time_to_sec(T1.total_time)))*100) as eff
                /*if(T1.current>=T2.kw_range,1,0) as status*/

            from productivity_data_logger_summary T1 join device_details_productivity T2
            on T1.DEVICE_ID = T2.device_id
            /*cross join rate_config on type ='money'*/
            where visibility = 1
            and year(T1.date) = year('$date')
            group by T1.device_id
            order by  report_sort;");

            return [
                'data'=>  $this->transformHeatmapData($header,$date)
                ];
     /*return [
         'data'=> $data
     ];*/
    }

    function transformHeatmapData($data,$date){
        $result = array();
        $label = array();
        $array = json_decode(json_encode($data), True);
        foreach($array as $k => $v) {
            $result[$v['category_code'].'_'.$v['location']][] = $v;
            $label[$v['category_code'].'_'.$v['location']] = $v['category'].' : '.$v['location'];
        }
ksort($result);
ksort($label);
        return [
            'data'=>$result,
            'label'=>$label,
            'date'=>$date
        ];
    }
}
