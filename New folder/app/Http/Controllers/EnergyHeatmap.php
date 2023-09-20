<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnergyHeatmap extends Controller
{
    //
	 CONST CACHE_KEY = 'live_heatmap';
    public function getCacheKey()
    {
        return self::CACHE_KEY;
    }
    function getHeatmapDataLive(){
			$cacheKey = $this->getCacheKey();
			$data = cache()->remember($cacheKey, Carbon::now()->addMinutes(1), function (){
				$latest_date_time = DB::select('select date , dt_time as time from productivity_data_rs485_summary
				order by date desc,dt_time desc
				limit 1;');
				// return  $latest_date_time[0]->date;
				$date = $latest_date_time[0]->date;
				$time = $latest_date_time[0]->time;
				return $this->getHeatmapDataForDate($date);
			});
        return $data;
    }

    function getHeatmapDataForDate($date){
        $header =  DB::select("
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
            group by T1.hour, T1.device_id, T2.report_sort
            order by  T1.hour,T2.report_sort;");
        //return $result;
        return [
            'data'=>  $this->transformHeatmapData($header,$date)
            ];
     /*return [
         'data'=> $data
     ];*/
    }


    function getHeatmapDataForMonth($date){
        $header =  DB::select("
            /* for selected month */
            select T1.hour as hour,
                    max(T1.date) as max_date,
                    min(T1.date) as min_date,
                    month(max(T1.date)) as month_no,
                    monthname(max(T1.date)) as month,
                    time(max(T1.DT_TIME)) as time,
                    /*max(T1.DATE) as date,*/
                    max(location_id) as location ,
                    max(T2.device_name) as name,
                    count(T1.DATE) as no_of_days,
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
            and month(T1.date)= month('$date')
            and year(T1.date) = year('$date')
            group by T1.hour, T1.device_id
            order by T1.hour;");

            return [
                'data'=>  $this->transformHeatmapData($header,$date)
                ];
     /*return [
         'data'=> $data
     ];*/
    }

    function getHeatmapDataForYear($date){
        $header =  DB::select("
            /* for selected year */
            select T1.hour as hour,
                max(T1.date) as max_date,
                min(T1.date) as min_date,
                count(distinct month(T1.date)) as no_of_months,
                time(max(T1.DT_TIME)) as time,
                max(T1.DATE) as date,
                max(location_id) as location,
                max(T2.device_name) as name,
                count(T1.DATE) as no_of_days,
                year(max(T1.date)) as year,

                /*T1.running_time as running_time,
                T1.idle_time as idle_time,
                T1.off_time as off_time,
                max(device_category) as category*/
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
            and year(T1.date) = year('$date')
            group by T1.hour, T1.device_id
            order by  T1.hour;");

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
