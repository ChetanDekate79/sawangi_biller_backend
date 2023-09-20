<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductivityAnalysisController extends Controller
{
    //


   public function getDeviceProductivityDataAll($device_id,$date){
    $day = Carbon::parse($date)->format('d');
    $month = Carbon::parse($date)->format('m');
    $year = Carbon::parse($date)->format('Y');
    return [
        'day'=> $this-> getDeviceProductivityDataForDay($device_id,$year,$month,$day),
        'month'=> $this-> getDeviceProductivityDataForMonth($device_id,$year,$month),
        'year'=> $this-> getDeviceProductivityDataForYear($device_id,$year),
		'dates' => $this->associativeToIndex(DB::select("select distinct date  from productivity_data_logger_summary ;"))
    ];

}

    public function getDeviceProductivityDataForYear($device_id,$year)
    {
        $monthlyDataForYear=  DB::select("
            select
                max(T1.DATE) as date,
				max(T2.device_id) as device_id,
                max(T2.device_name) as name,
                max(T1.date) as max_date,
                min(T1.date) as min_date,
                month(max(T1.date)) as month_no,
                monthname(max(T1.date)) as month,
                count(T1.DATE) as no_of_days,
                count(distinct month(T1.date)) as no_of_months,
                max(location_id) as location,
                max(device_category) as category,
                round(sum(kwh)) as kwh,
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
            and year(T1.date) = $year
            and T1.DEVICE_ID = $device_id
            group by  T1.DEVICE_ID,MONTH(T1.date)
            order by  date;");

        $DataForYear =  DB::select("
            select
                max(T1.DATE) as date,
				max(T2.device_id) as device_id,
                max(T2.device_name) as name,
                max(T1.date) as max_date,
                min(T1.date) as min_date,
                month(max(T1.date)) as month_no,
                monthname(max(T1.date)) as month,
                count(T1.DATE) as no_of_days,
                count(distinct month(T1.date)) as no_of_months,
                max(location_id) as location,
                max(device_category) as category,
                round(sum(kwh)) as kwh,
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
            and year(T1.date) = $year
            and T1.DEVICE_ID = $device_id
            group by  T1.DEVICE_ID
            order by T1.DEVICE_ID;");

            return  [
                'data'=> [
                    'series' =>$monthlyDataForYear,
                    'pie' => $DataForYear
                ],
				 'label'=> [
                    'series' => "Machine Performance For Year-".$year,
                    'pie' => "Machine Performance For Year-".$year,
                ]
         ];
    }

    public function getDeviceProductivityDataForMonth($device_id,$year,$month)
    {
        $dailyDataForMonth=  DB::select("
            select
                max(T1.DATE) as date,
				max(T2.device_id) as device_id,
                max(T2.device_name) as name,
                max(T1.date) as max_date,
                min(T1.date) as min_date,
                month(max(T1.date)) as month_no,
                monthname(max(T1.date)) as month,
                count(T1.DATE) as no_of_days,
                max(location_id) as location,
                max(device_category) as category,
                round(sum(kwh)) as kwh,
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
            and month(T1.date)= $month
            and year(T1.date) = $year
            and T1.DEVICE_ID = $device_id
            group by T1.device_id , T1.date
            order by  T1.date;");

        $DataForMonth =  DB::select("
            select
                max(T1.DATE) as date,
				max(T2.device_id) as device_id,
                max(T2.device_name) as name,
                max(T1.date) as max_date,
                min(T1.date) as min_date,
                month(max(T1.date)) as month_no,
                monthname(max(T1.date)) as month,
                count(T1.DATE) as no_of_days,
                max(location_id) as location,
                max(device_category) as category,
                round(sum(kwh)) as kwh,
                round(sum(time_to_sec(T1.running_time))/60) as running_time,
                round(sum(time_to_sec(T1.idle_time))/60) as idle_time,
                round(sum(time_to_sec(T1.off_time))/60) as off_time,
                round(sum(time_to_sec(T1.total_time))/60) as total_time,
                round((sum(time_to_sec(T1.running_time))/sum(time_to_sec(T1.total_time)))*100) as eff

            from productivity_data_logger_summary T1 join device_details_productivity T2
            on T1.DEVICE_ID = T2.device_id
            /*cross join rate_config on type ='money'*/
            where visibility = 1
            and month(T1.date)= $month
            and year(T1.date) = $year
            and T1.DEVICE_ID = $device_id
            group by T1.device_id
            order by  T1.device_id;");

            return  [
                'data'=> [
                    'series' =>$dailyDataForMonth,
                    'pie' => $DataForMonth
                ],
				 'label'=> [
                    'series' => "Machine Performance For Month -".$dailyDataForMonth[0]->month,
                    'pie' => "Machine Performance For Month -".$dailyDataForMonth[0]->month,
                ]
         ];
    }
    public function getDeviceProductivityDataForDay($device_id,$year,$month,$day)
    {
        $horlyDataForDay=  DB::select("
            select
                T1.DATE as date,
				(T2.device_id) as device_id,
                hour,
                time(dt_time) as time,
                (T2.device_name) as name,
                (T2.device_id) as id,
                (location_id) as location,
                (device_category) as category,
                round((kwh)) as kwh,
                round((time_to_sec(T1.running_time))/60) as running_time,
                round((time_to_sec(T1.idle_time))/60) as idle_time,
                round((time_to_sec(T1.off_time))/60) as off_time,
                round((time_to_sec(T1.total_time))/60) as total_time,
                round(((time_to_sec(T1.running_time))/(time_to_sec(T1.total_time)))*100) as eff,
                if(T1.current>=T2.kw_range,1,0) as status
            from productivity_data_logger_data_rs485_summary T1 join device_details_productivity T2
            on T1.DEVICE_ID = T2.device_id
            where visibility = 1
            and year(T1.date) = $year
            and month(T1.date) = $month
            and day(T1.date) = $day
            and T1.DEVICE_ID = $device_id
            order by  hour;");

        $DataForDay =  DB::select("
            select
                T1.DATE as date,
				(T2.device_id) as device_id,
                (T2.device_name) as name,
                (T2.device_id) as id,
                (location_id) as location,
                (device_category) as category,
                round((kwh)) as kwh,
                round((time_to_sec(T1.running_time))/60) as running_time,
                round((time_to_sec(T1.idle_time))/60) as idle_time,
                round((time_to_sec(T1.off_time))/60) as off_time,
                round((time_to_sec(T1.total_time))/60) as total_time,
                round(((time_to_sec(T1.running_time))/(time_to_sec(T1.total_time)))*100) as eff
            from productivity_data_logger_summary T1 join device_details_productivity T2
            on T1.DEVICE_ID = T2.device_id
            /*cross join rate_config on type ='money'*/
            where visibility = 1
            and year(T1.date) = $year
            and month(T1.date) = $month
            and day(T1.date) = $day
            and T1.DEVICE_ID = $device_id

            order by  T1.date;");

            return  [
                'data'=> [
                    'series' =>$horlyDataForDay,
                    'pie' => $DataForDay
                ],
				 'label'=> [
                    'series' => " Houly Machine Performance For  -".$DataForDay[0]->date,
                    'pie' => "Machine Performance at -".$DataForDay[0]->date,
                ]
         ];
    }
	
	function associativeToIndex($data){
        $resp_data = array();
        foreach($data as $key => $value_arr){
            foreach($value_arr as $key => $value){
                $resp_data[] = $value;
            }
        }
        return $resp_data;
    }

}
