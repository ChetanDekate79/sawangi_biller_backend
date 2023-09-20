<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnergyAnalysisController extends Controller
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




    public function getGroupEnergyDataForYear($group_id,$year)
    {
        $monthlyDataForYear=  DB::select("
            select
                monthname(max(T2.date)) as month ,
                max(T2.date) as date ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_category) as name,
                round(sum(T2.rs485_30)) as energy,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(T2.rs485_30)* rc.rate) as energy_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.device_category_level = 1
            and visibility = 1  and T1.device_category_level = 1
            and T1.device_category_code = '$group_id'
            and year(T2.date) = $year
            group by month(T2.date), rc.rate, T1.device_category_code");

        $DataForYear =  DB::select("
            select
                year(max(T2.date)) as month ,
                max(T2.date) as date,
                max(T1.device_category_code) as  group_id,
                max(T1.device_category) as group_name,
                max(T1.device_name) as device_name,
                max(T1.device_id) as device_id,
                round(sum(rs485_30)) as energy,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(rs485_30)* rc.rate) as energy_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.`type` = 'money'
            where visibility = 1 and T1.device_category_level = 1
            and  T1.device_category_code  = '$group_id'
            and year(T2.date) =  $year
            group by T1.device_id ,rc.rate");
            return  [
                'data'=> [
                    'series' =>$monthlyDataForYear,
                    'pie' => $DataForYear
                ],
                'title'=> [
                    'series' =>"Monthly Energy Used in Year - $year",
                    'pie' => "Energy Used By Category in Year - $year",
                ]
         ];
    }

    public function getGroupEnergyDataForMonth($group_id,$year,$month)
    {
        $dailyDataForMonth=  DB::select("
            select
                dayname(T2.date) as day ,
                max(T2.date) as date,
                max(T1.device_category_code) as  group_id,
                max(T1.device_category) as name,
                round(sum(rs485_30)) as energy,
                round(sum(rs485_30)* rc.rate) as energy_cost,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.device_category_level = 1
            and T1.device_category_code = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            group by  T2.date, rc.rate");

        $DataForMonth =  DB::select("
            select
                monthname(max(T2.date)) as month ,
                max(T2.date) as date,
                month( max(T2.date)) as month_no ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_id) as  device_id,
                max(T1.device_category) as group_name,
                max(T1.device_name) as device_name,
                round(sum(rs485_30)) as energy,
                round(sum(rs485_30)* rc.rate) as energy_cost,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.device_category_level = 1
            and T1.device_category_code = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            group by  T1.device_id, rc.rate;");

            return  [
                'data'=> [
                    'series' =>$dailyDataForMonth,
                    'pie' => $DataForMonth
                ],
                'title'=> [
                    'series' =>" Daily Energy Used in Month -".$DataForMonth[0]->month,
                    'pie' => " Energy Used By Categorys in Month -".$DataForMonth[0]->month,
                ]
         ];
    }
    public function getGroupEnergyDataForDay($group_id,$year,$month,$day)
    {
        $horlyDataForDay=  DB::select("
            select
                hour as hour,
                max(time) as time,
                max(T2.date) as date ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_category) as name,
                sum(kwh) as energy,
                sum(kwh)* rc.rate as energy_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.device_category_level = 1
            and T1.device_category_code = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            and day(T2.date) = $day
            group by hour, rc.rate ");

    	$DataForDay =  DB::select("
            select
                max(cast(T2.date as date)) as date ,
                max(T1.device_id) as device_id ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_id) as  department_id,
                max(T1.device_name) as  device_name,
                max(T1.device_name) as  lable,
                max(T1.device_category) as group_name,
                if( max(T1.last_node) = 1, false, true) as subgroup,
                sum(rs485_30) as energy,
sum(rs485_31) as reactive_energy,
                sum(rs485_30)* rc.rate as energy_cost,
                round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.level_no = 1
            and T1.device_category_code = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            and day(T2.date) =  $day
            group by  T1.device_id,  rc.rate");


            return  [
                'data'=> [
                    'series' =>$horlyDataForDay,
                    'pie' => $DataForDay
                ],
                'title'=> [
                    'series' =>" Hourly Energy Use of day-".$DataForDay[0]->date,
                    'pie' => " Energy Use at -".$DataForDay[0]->date,
                ]
         ];
    }
    /**********************************************************************
     *    for subgroup
     */
    public function getSubGroupEnergyDataForYear($group_id,$year)
    {
        $monthlyDataForYear=  DB::select("
            select
                monthname(max(T2.date)) as month ,
                max(T2.date) as date ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_name) as name,
                max(T1.device_id) as department_id,
                if( max(T1.last_node) = 1, false, true) as subgroup,
                round(sum(T2.rs485_30)) as energy,
                round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(T2.rs485_30)* rc.rate) as energy_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.level_no = 1
            and T1.device_id = '$group_id'
            and year(T2.date) = $year
            group by month(T2.date), rc.rate");

        $DataForYear = '';// DB::select("");
            return  [
                'data'=> [
                    'series' =>$monthlyDataForYear,
                    'pie' => $DataForYear
                ],
                'title'=> [
                    'series' =>"Monthly Energy Used in Year - $year",
                    'pie' => "Energy Used By Category in Year - $year",
                ]
        ];
    }

    public function getSubGroupEnergyDataForMonth($group_id,$year,$month)
    {
        $dailyDataForMonth=  DB::select("
            select
                monthname(max(T2.date)) as month ,
                max(T2.date) as date ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_name) as name,
                max(T1.device_id) as department_id,
                if(max(T1.last_node) = 1,false,true) as subgroup,
                round(sum(T2.rs485_30)) as energy,
                round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(T2.rs485_30)* rc.rate) as energy_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.level_no = 1
            and T1.device_id = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            group by T1.device_id, T2.date, rc.rate ");

        $DataForMonth = '';// DB::select("");

            return  [
                'data'=> [
                    'series' =>$dailyDataForMonth,
                    'pie' => $DataForMonth
                ],
                'title'=> [
                    'series' =>" Daily Energy Used in Month -".$dailyDataForMonth[0]->month,
                    'pie' => " Energy Used By Categorys in Month -".$dailyDataForMonth[0]->month,
                ]
        ];
    }
    public function getSubGroupEnergyDataForDay($group_id,$year,$month,$day)
    {
        $horlyDataForDay=  DB::select("
            select
                hour as hour,
                max(time) as time,
                max(T2.date) as date ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_category) as name,
                sum(kwh) as energy,
                round(sum(kwh)* rc.rate) as energy_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.level_no = 1
            and T2.device_id = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            and day(T2.date) = $day
            group by hour, rc.rate ");

        $DataForDay =  DB::select("
            select
                max(cast(T2.date as date)) as date ,
                T1.device_id as device_id ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_name) as  device_name,
                (select device_name from device_details_energy where device_id = '$group_id' )  as  lable,
                max(T1.device_category) as group_name,
                if(max(T1.last_node) = 1,false,true) as subgroup,
                sum(rs485_30) as energy,
sum(rs485_31) as reactive_energy,
                sum(rs485_30)* rc.rate as energy_cost,
                round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1 and T1.level_no = 2
            and T1.root = '$group_id'
            and year(T2.date) = $year
            and month(T2.date) = $month
            and day(T2.date) = $day
            group by T1.device_id, rc.rate");


            return  [
                'data'=> [
                    'series' =>$horlyDataForDay,
                    'pie' => $DataForDay
                ],
                'title'=> [
                    'series' =>" Hourly Energy Use of day-".$DataForDay[0]->date,
                    'pie' => " Energy Use at -".$DataForDay[0]->date,
                ]
        ];
    }

    /**********************************************************************
     *    for individual device
     */

    public function getDeviceEnergyDataForYear($device_id,$year)
    {
        $monthlyDataForYear=  DB::select("
            select
                monthname(max(T2.date)) as month ,
                max(T2.date) as  date,
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
            group by month(T2.date), rc.rate");

        $DataForYear =  DB::select("
            select
                month(max(T2.date)) as month ,
                max(T2.date) as  date,
                max(T1.device_id) as  divice_id,
                count(T2.date) as  no_of_day,
                count(distinct month(T2.date)) as  no_of_month,
                max(T1.device_name) as name,
                round(sum(rs485_30)) as energy,
                round(sum(rs485_30)* rc.rate) as energy_cost,
				round(sum(net_kwh),1) as net_kwh,
                round(sum(net_kwh)* rc.rate) as net_kwh_cost,
                round(sum(kwh_delivered),1) as kwh_delivered,
                round(sum(kwh_delivered * rc.rate),1) as kwh_delivered_cost
            from device_details_energy T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.`type` = 'money'
            where visibility = 1
            and  T1.device_id =  $device_id
            and year(T2.date) =  $year
            group by T1.device_id ,rc.rate");

            return  [
                'data'=> [
                    'series' =>$monthlyDataForYear,
                    'pie' => $DataForYear
                ],
                'title'=> [
                    'series' =>" Daily Energy Used in Year - $year",
                    'pie' => " Energy Use Of Feeders in Year - $year",
                ]
         ];
    }

    public function getDeviceEnergyDataForMonth($device_id,$year,$month)
    {
        $dailyDataForMonth=  DB::select("
            select
                dayname(T2.date) as day ,
                T2.date ,
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

        $DataForMonth =  DB::select("
            select
                monthname(max(T2.date)) as month ,
                month( max(T2.date)) as month_no ,
                max(T2.date) as date ,
                max(T1.device_id) as  device_id,
                max(T1.device_name) as name,
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
            group by  T1.device_id, rc.rate;");

            return  [
                'data'=> [
                    'series' =>$dailyDataForMonth,
                    'pie' => $DataForMonth
                ],
                'title'=> [
                    'series' =>" Daily Energy Used in Month -".$DataForMonth[0]->month,
                    'pie' => " Energy Used By Feeders in Month -".$DataForMonth[0]->month,
                ]
         ];
    }
    public function getDeviceEnergyDataForDay($device_id,$year,$month,$day)
    {
        $horlyDataForDay=  DB::select("
            select
                hour as hour,
                max(time) as time,
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
                max(T1.device_id) as  device_id,
                max(T1.device_name) as  device_name,
                max(T1.device_name) as  lable,

                max(T1.device_category) as group_name,
                sum(rs485_30) as energy,
sum(rs485_31) as reactive_energy,
                sum(rs485_30)* rc.rate as energy_cost,
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
            group by T1.device_id, rc.rate");

            return  [
                'data'=> [
                    'series' =>$horlyDataForDay,
                    'pie' => $DataForDay
                ],
                'title'=> [
                    'series' =>" Hourly Energy Use of day-".$DataForDay[0]->date,
                    'pie' => " Energy Use at -".$DataForDay[0]->date,
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
