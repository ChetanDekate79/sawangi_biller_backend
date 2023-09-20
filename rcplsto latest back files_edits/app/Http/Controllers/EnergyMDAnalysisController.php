<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnergyMDAnalysisController extends Controller
{
    //


   public function getGroupEnergyDataAll($group_id,$date){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');
        return [
            'day'=> $this-> getGroupEnergyDataForDay($group_id,$year,$month,$day),
            'month'=> $this-> getGroupEnergyDataForMonth($group_id,$year,$month),
            'year'=> $this-> getGroupEnergyDataForYear($group_id,$year),
			'dates' =>$this->associativeToIndex(DB::select("select distinct date  from productivity_data_rs485_summary;"))
        ];

    }

    public function getSubGroupEnergyDataAll($group_id,$date){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');
        return [
            'day'=> $this-> getSubGroupEnergyDataForDay($group_id,$year,$month,$day),
            'month'=> $this-> getSubGroupEnergyDataForMonth($group_id,$year,$month),
            'year'=> $this-> getSubGroupEnergyDataForYear($group_id,$year)
        ];

    }


    public function getDeviceEnergyDataAll($device_id,$date){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');
        return [
            'day'=> $this-> getDeviceEnergyDataForDay($device_id,$year,$month,$day),
            'month'=> $this-> getDeviceEnergyDataForMonth($device_id,$year,$month),
            'year'=> $this-> getDeviceEnergyDataForYear($device_id,$year)
        ];

    }


    /**********************************************************************
     *    for individual device
     */

    public function getDeviceEnergyDataForYear($device_id,$year)
    {
        $monthlyDataForYear=  DB::select("
        SELECT * FROM (
            select
                monthname(max(T2.date)) as month ,
                month(max(T2.date)) as month_no ,
                max(T2.date) as  date,
                MAX(md) AS md,
               
                round(AVG(load_factor),2) AS load_factor,
                max(T1.device_id) as  divice_id,
                count(T2.date) as  no_of_days,
                max(T1.device_name) as name,
                round(sum(T2.rs485_30)) as energy,
                round(sum(T2.rs485_30)* rc.rate) as energy_cost,
                round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1
            and T1.device_id = $device_id
            and year(T2.date) = $year
          
            group by month(T2.date), rc.rate ) T1
            JOIN (
					select                
                distinct month(T2.date) as month_no_2 ,
                md_time AS md_time,
                T2.md as md_2                
               
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1
            and T1.device_id = $device_id
            and year(T2.date) = $year     
				) T2
				ON T1.month_no = T2.month_no_2
				AND T1.md = T2.md_2
         ORDER BY month_no");

        $DataForYear = '';// DB::select("");

            return  [
                'data'=> [
                    'series' =>$monthlyDataForYear,
                    'pie' => $DataForYear
                ],
                'title'=> [
                    'series' =>" MD in  Year - $year",
                    'pie' => " Energy Use Of Feeders in Year - $year",
                ]
         ];
    }

    public function getDeviceEnergyDataForMonth($device_id,$year,$month)
    {
        $dailyDataForMonth=  DB::select("
            select
                dayname(T2.date) as day ,
                monthname(T2.date) as month ,
                T2.date ,
                MAX(md) AS md,
                MAX(md_time) AS md_time,
                round(AVG(load_factor),2) AS load_factor,
                round(max(T1.device_id)) as  device_id,
                round(max(T1.device_name)) as name,
                round(sum(rs485_30)) as energy,
                round(sum(rs485_30)* rc.rate) as energy_cost,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1
            and T1.device_id = $device_id
            and year(T2.date) = $year
            and month(T2.date) = $month
            group by  T2.date, rc.rate");

        $DataForMonth =  '';//DB::select("");

            return  [
                'data'=> [
                    'series' =>$dailyDataForMonth,
                    'pie' => $DataForMonth
                ],
                'title'=> [
                    'series' =>"  MD in Month -".$dailyDataForMonth[0]->month,
                    'pie' => " Energy Used By Feeders in Month -".$dailyDataForMonth[0]->month,
                ]
         ];
    }
    public function getDeviceEnergyDataForDay($device_id,$year,$month,$day)
    {
        //dd($device_id);
        $horlyDataForDay=  DB::select("
            select
                hour as hour,
                max(time) as time,
                MAX(md) AS md,
                MAX(md_time) AS md_time,
                round(AVG(load_factor),2) AS load_factor,
                max(T2.date) as date ,
                count(T2.date) as date ,
                max(T1.device_id) as  device_id,
                max(T1.device_name) as name,
                sum(kwh) as energy,
                round(sum(kwh)* rc.rate) as energy_cost,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1
            and T1.device_id = $device_id
            and year(T2.date) = $year
            and month(T2.date) = $month
            and day(T2.date) = $day
            group by hour, rc.rate ");

        $DataForDay =  DB::select("
                       select
                max(cast(T2.date as date)) as date ,
                max(T1.device_name) as  device_name,                
                round(ifnull(MAX(md),0),2) AS md,
                MAX(md_time) AS md_time,
                ifnull(round(AVG(load_factor),2),0) AS load_factor,                  
                sum(rs485_30) as energy,
                sum(rs485_30)* rc.rate as energy_cost			
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1
            and T1.device_id = $device_id
            and year(T2.date) = $year
            and month(T2.date) = $month
            and day(T2.date) = $day
            group by T1.device_id, rc.rate");
        //dd($DataForDay);

            return  [
                'data'=> [
                    'series' =>$horlyDataForDay,
                    'pie' => $DataForDay
                ],
                'title'=> [
                    'series' =>"  MD in Hour-".$DataForDay[0]->date,
                    'pie' => "  MD in a day  -".$DataForDay[0]->date,
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
