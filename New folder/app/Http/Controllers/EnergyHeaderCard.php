<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnergyHeaderCard extends Controller
{
	CONST CACHE_KEY = 'live_header_card';
    public function getCacheKey()
    {
        return self::CACHE_KEY;
    }
    //
    public $time;

    function getHeaderDataLive(){
		$cacheKey = $this->getCacheKey();
        $data = cache()->remember($cacheKey, Carbon::now()->addMinutes(1), function (){
			$latest_date_time = DB::select('select date , time(dt_time) as time from data_rs485_summary
			order by date desc,dt_time desc
			limit 1;');
			// return  $latest_date_time[0]->date;
			$date = $latest_date_time[0]->date;
			$this->time = $latest_date_time[0]->time;
			return $this->getHeaderDataForDate($date);
       });
       return $data;
    }

    function getHeaderDataForDate($date){
        $header =  DB::select("
        select DATE,max(category_color) as category_color  from productivity_data_rs485_summary T1 
join device_details_energy T2 ON T1.DEVICE_ID = T2.device_id  where DATE = '$date' limit 4");

        return [
            'data'=> [
                'data'=> $header,
                'date'=> $date,
                'time'=> $this->time
                ]
            ];
     /*return [
         'data'=> $data
     ];*/
    }


    function getHeaderDataForMonth($date){
        $header =  DB::select("
            select max(T1.date) as max_date,
                min(T1.date) as min_date,
				max(category_color) as category_color,
                month(max(T1.date)) as month_no,
                monthname(max(T1.date)) as month,
                count(Distinct  T1.date) as no_of_days,
                count(Distinct  T1.device_id) as no_of_feeders,
                round(sum(rs485_1),1) as kw,
                round(sum(rs485_30),1) as kwh,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh * rate),1) as net_kwh_expense,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rate),1) as kwh_delivered_expense,
                max(device_category) as category,
                max(device_category_code) as category_code,
                round(sum(rs485_1 * rate),1) as kw_expense,
                round(sum(rs485_30 * rate),1) as kwh_expense,
                max(unit) as expense_unit,
				round(sum(import_kw),1) as import_kw,
				round(sum(import_kw * rate),1) as import_kw_expense,
				round(sum(export_kw),1) as export_kw,
				round(sum(export_kw * rate),1) as export_kw_expense

            from data_rs485_summary T1 join device_details_energy T2
            on T1.device_id = T2.device_id
            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and month(T1.date)= month('$date')
            and year(T1.date) = year('$date')
            group by device_category_code
            order by report_sort;");

        return [
            'data'=> [
                'data'=> $header,
                'date'=> $date,
                'time'=> $this->time
                ]
            ];
     /*return [
         'data'=> $data
     ];*/
    }

    function getHeaderDataMonthlyForYear($date){
        $header =  DB::select("
            select max(T1.date) as max_date,
                min(T1.date) as min_date,
				max(category_color) as category_color,
                month(max(T1.date)) as max_month_no,
                monthname(max(T1.date)) as max_month,
                month(min(T1.date)) as min_month_no,
                monthname(min(T1.date)) as min_month,
                year(max(T1.date)) as year,
                count(Distinct  T1.date) as no_of_days,
                count(Distinct  T1.device_id) as no_of_feeders,
                round(sum(rs485_1),1) as kw,
                round(sum(rs485_30),1) as kwh,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh * rate),1) as net_kwh_expense,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rate),1) as kwh_delivered_expense,
                max(device_category) as category,
                max(device_category_code) as category,
                round(sum(rs485_1 * rate),1) as kw_expense,
                round(sum(rs485_30 * rate),1) as kwh_expense,
                max(unit) as expense_unit,
				round(sum(import_kw),1) as import_kw,
				round(sum(import_kw * rate),1) as import_kw_expense,
				round(sum(export_kw),1) as export_kw,
				round(sum(export_kw * rate),1) as export_kw_expense

            from data_rs485_summary T1 join device_details_energy T2
            on T1.device_id = T2.device_id
            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and year(T1.date) = year('$date')
            group by device_category_code,month(T1.date)
            order by month(T1.date),report_sort;");

        return [
            'data'=> [
                'data'=> $header,
                'date'=> $date,
                'time'=> $this->time
                ]
            ];
     /*return [
         'data'=> $data
     ];*/
    }

    function getHeaderDataForYear($date){
        $header =  DB::select("
            select max(T1.date) as max_date,
                min(T1.date) as min_date,
				max(category_color) as category_color,
                month(max(T1.date)) as max_month_no,
                monthname(max(T1.date)) as max_month,
                month(min(T1.date)) as min_month_no,
                monthname(min(T1.date)) as min_month,
                year(max(T1.date)) as year,
                count(Distinct  T1.date) as no_of_days,
                count(Distinct  T1.device_id) as no_of_feeders,
                round(sum(rs485_1),1) as kw,
                round(sum(rs485_30),1) as kwh,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh * rate),1) as net_kwh_expense,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rate),1) as kwh_delivered_expense,
                max(device_category) as category,
                max(device_category_code) as category,
                round(sum(rs485_1 * rate),1) as kw_expense,
                round(sum(rs485_30 * rate),1) as kwh_expense,
                max(unit) as expense_unit,
				round(sum(import_kw),1) as import_kw,
				round(sum(import_kw * rate),1) as import_kw_expense,
				round(sum(export_kw),1) as export_kw,
				round(sum(export_kw * rate),1) as export_kw_expense

            from data_rs485_summary T1 join device_details_energy T2
            on T1.device_id = T2.device_id
            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and year(T1.date) = year('$date')
            group by device_category_code
            order by report_sort;");

        return [
            'data'=> [
                'data'=> $header,
                'date'=> $date,
                'time'=> $this->time
                ]
            ];
     /*return [
         'data'=> $data
     ];*/
    }
}
