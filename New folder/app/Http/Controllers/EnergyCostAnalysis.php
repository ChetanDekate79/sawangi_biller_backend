<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnergyCostAnalysis extends Controller
{
    //

    public function getEnergyCosAnalysis($date)
    {
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');
        return [
            'day'=> $this-> getEnergyCostForDay($year,$month,$day),
            'month'=> $this-> getEnergyCostForMonth($year,$month),
            'year'=> $this-> getEnergyCostForYear($year)
        ];


    }

    public function getEnergyCostForDay($year,$month,$day)
    {

       // return "hello";
        $totalCost=  DB::select("
            select
                T4.date,
                T4.group_name as incomer,
                T4.energy as incomer_import_energy, T4.kwh_delivered as incomer_export_energy,
                T4.net_kwh as incomer_net_energy,
                T4.energy_cost as incomer_import_energy_cost, T4.kwh_delivered_cost as incomer_export_energy_cost,
                T4.net_kwh_cost as incomer_net_energy_cost,

                T5.group_name as solar, T5.energy as solar_import_energy,
                T5.kwh_delivered as solar_export_energy, T5.net_kwh as solar_net_energy,
                T5.energy_cost as solar_import_energy_cost,
                T5.kwh_delivered_cost as solar_export_energy_cost,
                T5.net_kwh_cost as solar_net_energy_cost,
                (T4.net_kwh_cost + T5.net_kwh_cost) as total_cost

                from( select
                            max(cast(T2.date as date)) as date ,
                            max(T1.device_category_code) as  group_id,
                            max(T1.device_name) as  device_name,
                            max(T1.device_category) as group_name,
                            sum(rs485_30) as energy,
                            round(sum(rs485_30)* rc.rate,2) as energy_cost,
                            round(sum(net_kwh),1) as net_kwh,
                            round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                            round(sum(kwh_delivered),1) as kwh_delivered,
                            round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                        from device_details T1
                        join data_rs485_summary T2 on T1.device_id = T2.device_id
                        join department T3 on T3.department_id = T1.department_id
                        cross join rate_config rc on rc.type = 'money'
                        where visibility = 1 and T1.device_category_level = 1
                        and year(T2.date) = $year
                        and month(T2.date) = $month
                        and day(T2.date) = $day
                        and device_category_code in('cat_1')
                        group by T2.date, device_category_code, rc.rate) T4
                join ( select
                            max(cast(T2.date as date)) as date ,
                            max(T1.device_category_code) as  group_id,
                            max(T1.device_name) as  device_name,
                            max(T1.device_category) as group_name,
                            sum(rs485_30) as energy,
                            round(sum(rs485_30)* rc.rate,2) as energy_cost,
                            round(sum(net_kwh),1) as net_kwh,
                            round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                            round(sum(kwh_delivered),1) as kwh_delivered,
                            round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                        from device_details T1
                        join data_rs485_summary T2 on T1.device_id = T2.device_id
                        join department T3 on T3.department_id = T1.department_id
                        cross join rate_config rc on rc.type = 'money'
                        where visibility = 1 and T1.device_category_level = 1
                        and year(T2.date) = $year
                        and month(T2.date) = $month
                        and day(T2.date) = $day
                        and device_category_code in('cat_3')
                        group by  device_category_code, rc.rate) T5
                on T4.date = T5.date;");

        $total_load_cost =  DB::select("
                 select
                        max(cast(T2.date as date)) as date ,
                        max(T1.device_category_code) as  group_id,
                        max(T3.department_name) as name,
                        max(T1.device_category) as group_name,
                        sum(rs485_30) as energy,
                        round(sum(rs485_30)* rc.rate,2) as energy_cost,
                        round(sum(net_kwh),1) as net_kwh,
                        round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                        round(sum(kwh_delivered),1) as kwh_delivered,
                        round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                    from device_details T1
                    join data_rs485_summary T2 on T1.device_id = T2.device_id
                    join department T3 on T3.department_id = T1.department_id
                    cross join rate_config rc on rc.type = 'money'
                    where visibility = 1 and T1.device_category_level = 1
                    and year(T2.date) = $year
                    and month(T2.date) = $month
                    and day(T2.date) = $day
                    and device_category_code in('cat_4')
                    group by T2.date, T3.department_id, rc.rate;");

            $total_load_cost_sum =  DB::select("
                    select
                           max(cast(T2.date as date)) as date ,
                           max(T1.device_category_code) as  group_id,
                           max(T3.department_name) as name,
                           max(T1.device_category) as group_name,
                           sum(rs485_30) as energy,
                           round(sum(rs485_30)* rc.rate,2) as energy_cost,
                           round(sum(net_kwh),1) as net_kwh,
                           round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                           round(sum(kwh_delivered),1) as kwh_delivered,
                           round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                       from device_details T1
                       join data_rs485_summary T2 on T1.device_id = T2.device_id
                       join department T3 on T3.department_id = T1.department_id
                       cross join rate_config rc on rc.type = 'money'
                       where visibility = 1 and T1.device_category_level = 1
                       and year(T2.date) = $year
                       and month(T2.date) = $month
                       and day(T2.date) = $day
                       and device_category_code in('cat_4')
                       group by  rc.rate;");

            return  [
                'data'=> [
                    'totalcost' => $totalCost,
                    'total_load_cost' => $total_load_cost,
                    'total_load_cost_sum' =>  $total_load_cost_sum
                ]
        ];
    }

    public function getEnergyCostForMonth($year,$month)
    {

       // return "hello";
        $totalCost=  DB::select("
            select
                T4.monthname, T4.month,
                T4.no_of_day,
                T4.energy as incomer_import_energy, T4.kwh_delivered as incomer_export_energy,
                T4.net_kwh as incomer_net_energy,
                T4.energy_cost as incomer_import_energy_cost, T4.kwh_delivered_cost as incomer_export_energy_cost,
                T4.net_kwh_cost as incomer_net_energy_cost,

                T5.group_name as solar, T5.energy as solar_import_energy,
                T5.kwh_delivered as solar_export_energy, T5.net_kwh as solar_net_energy,
                T5.energy_cost as solar_import_energy_cost,
                T5.kwh_delivered_cost as solar_export_energy_cost,
                T5.net_kwh_cost as solar_net_energy_cost,
                (T4.net_kwh_cost + T5.net_kwh_cost) as total_cost

                from( select
                            max(cast(T2.date as date)) as date ,
                            monthname(max(T2.date)) as monthname,
                            count(distinct T2.date) as no_of_day,
                            month(max(T2.date)) as month,
                            max(T1.device_category_code) as  group_id,
                            max(T1.device_name) as  device_name,
                            max(T1.device_category) as group_name,
                            sum(rs485_30) as energy,
                            round(sum(rs485_30)* rc.rate,2) as energy_cost,
                            round(sum(net_kwh),1) as net_kwh,
                            round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                            round(sum(kwh_delivered),1) as kwh_delivered,
                            round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                        from device_details T1
                        join data_rs485_summary T2 on T1.device_id = T2.device_id
                        join department T3 on T3.department_id = T1.department_id
                        cross join rate_config rc on rc.type = 'money'
                        where visibility = 1 and T1.device_category_level = 1
                        and year(T2.date) = $year
                        and month(T2.date) = $month

                        and device_category_code in('cat_1')
                        group by  device_category_code, rc.rate) T4
                join ( select
                            max(cast(T2.date as date)) as date ,
                            monthname(max(T2.date)) as monthname,
                            count(distinct date) as no_of_day,
                            month(max(T2.date)) as month,
                            max(T1.device_category_code) as  group_id,
                            max(T1.device_name) as  device_name,
                            max(T1.device_category) as group_name,
                            sum(rs485_30) as energy,
                            round(sum(rs485_30)* rc.rate,2) as energy_cost,
                            round(sum(net_kwh),1) as net_kwh,
                            round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                            round(sum(kwh_delivered),1) as kwh_delivered,
                            round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                        from device_details T1
                        join data_rs485_summary T2 on T1.device_id = T2.device_id
                        join department T3 on T3.department_id = T1.department_id
                        cross join rate_config rc on rc.type = 'money'
                        where visibility = 1 and T1.device_category_level = 1
                        and year(T2.date) = $year
                        and month(T2.date) = $month

                        and device_category_code in('cat_3')
                        group by  device_category_code, rc.rate) T5
                on T4.month = T5.month;");

        $total_load_cost =  DB::select("
                 select
                        max(cast(T2.date as date)) as date ,
                        max(T1.device_category_code) as  group_id,
                        monthname(max(T2.date)) as monthname,
                        month(max(T2.date)) as month,
                        count(distinct date) as no_of_day,
                        max(T3.department_name) as name,
                        max(T1.device_category) as group_name,
                        sum(rs485_30) as energy,
                        round(sum(rs485_30)* rc.rate,2) as energy_cost,
                        round(sum(net_kwh),1) as net_kwh,
                        round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                        round(sum(kwh_delivered),1) as kwh_delivered,
                        round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                    from device_details T1
                    join data_rs485_summary T2 on T1.device_id = T2.device_id
                    join department T3 on T3.department_id = T1.department_id
                    cross join rate_config rc on rc.type = 'money'
                    where visibility = 1 and T1.device_category_level = 1
                    and year(T2.date) = $year
                    and month(T2.date) = $month

                    and device_category_code in('cat_4')
                    group by  T3.department_id, rc.rate;");

                    $total_load_cost_sum =  DB::select("
                            select
                                max(cast(T2.date as date)) as date ,
                                monthname(max(T2.date)) as monthname,
                                month(max(T2.date)) as month,
                                count(distinct date) as no_of_day,
                                max(T3.department_name) as name,
                                max(T1.device_category) as group_name,
                                sum(rs485_30) as energy,
                                round(sum(rs485_30)* rc.rate,2) as energy_cost,
                                round(sum(net_kwh),1) as net_kwh,
                                round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                                round(sum(kwh_delivered),1) as kwh_delivered,
                                round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                            from device_details T1
                            join data_rs485_summary T2 on T1.device_id = T2.device_id
                            join department T3 on T3.department_id = T1.department_id
                            cross join rate_config rc on rc.type = 'money'
                            where visibility = 1 and T1.device_category_level = 1
                            and year(T2.date) = $year
                            and month(T2.date) = $month

                            and device_category_code in('cat_4')
                            group by rc.rate;
                    ");

            return  [
                'data'=> [
                    'totalcost' => $totalCost,
                    'total_load_cost' => $total_load_cost,
                    'total_load_cost_sum' =>  $total_load_cost_sum
                ]
        ];
    }

    public function getEnergyCostForYear($year)
    {

       // return "hello";
        $totalCost=  DB::select("
            select
                T4.year,T4.no_of_day,T4.no_of_month,
                T4.energy as incomer_import_energy, T4.kwh_delivered as incomer_export_energy,
                T4.net_kwh as incomer_net_energy,
                T4.energy_cost as incomer_import_energy_cost, T4.kwh_delivered_cost as incomer_export_energy_cost,
                T4.net_kwh_cost as incomer_net_energy_cost,

                T5.group_name as solar, T5.energy as solar_import_energy,
                T5.kwh_delivered as solar_export_energy, T5.net_kwh as solar_net_energy,
                T5.energy_cost as solar_import_energy_cost,
                T5.kwh_delivered_cost as solar_export_energy_cost,
                T5.net_kwh_cost as solar_net_energy_cost,
                (T4.net_kwh_cost + T5.net_kwh_cost) as total_cost
                from( select
                            max(cast(T2.date as date)) as date ,
                            year(max(T2.date)) as year,
                            count(distinct T2.date) as no_of_day,
                            count(distinct month(T2.date)) as no_of_month,
                            max(T1.device_category_code) as  group_id,
                            max(T1.device_name) as  device_name,
                            max(T1.device_category) as group_name,
                            sum(rs485_30) as energy,
                            round(sum(rs485_30)* rc.rate,2) as energy_cost,
                            round(sum(net_kwh),1) as net_kwh,
                            round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                            round(sum(kwh_delivered),1) as kwh_delivered,
                            round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                        from device_details T1
                        join data_rs485_summary T2 on T1.device_id = T2.device_id
                        join department T3 on T3.department_id = T1.department_id
                        cross join rate_config rc on rc.type = 'money'
                        where visibility = 1 and T1.device_category_level = 1
                        and year(T2.date) = $year
                        and device_category_code in('cat_1')
                        group by  device_category_code, rc.rate) T4
                join ( select
                            max(cast(T2.date as date)) as date ,
                            year(max(T2.date)) as year,
                            count(distinct T2.date) as no_of_day,
                            count(distinct month(T2.date)) as no_of_month,
                            max(T1.device_category_code) as  group_id,
                            max(T1.device_name) as  device_name,
                            max(T1.device_category) as group_name,
                            sum(rs485_30) as energy,
                            round(sum(rs485_30)* rc.rate,2) as energy_cost,
                            round(sum(net_kwh),1) as net_kwh,
                            round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                            round(sum(kwh_delivered),1) as kwh_delivered,
                            round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                        from device_details T1
                        join data_rs485_summary T2 on T1.device_id = T2.device_id
                        join department T3 on T3.department_id = T1.department_id
                        cross join rate_config rc on rc.type = 'money'
                        where visibility = 1 and T1.device_category_level = 1
                        and year(T2.date) = $year
                        and device_category_code in('cat_3')
                        group by  device_category_code, rc.rate) T5
                on T4.year = T5.year;");

        $total_load_cost =  DB::select("
                 select
                        max(cast(T2.date as date)) as date ,
                        year(max(T2.date))as year,
                        max(T1.device_category_code) as  group_id,
                        max(T3.department_name) as name,
                        count(distinct date) as no_of_day,
                        count(distinct month(date)) as no_of_month,
                        max(T1.device_category) as group_name,
                        sum(rs485_30) as energy,
                        round(sum(rs485_30)* rc.rate,2) as energy_cost,
                        round(sum(net_kwh),1) as net_kwh,
                        round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                        round(sum(kwh_delivered),1) as kwh_delivered,
                        round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                    from device_details T1
                    join data_rs485_summary T2 on T1.device_id = T2.device_id
                    join department T3 on T3.department_id = T1.department_id
                    cross join rate_config rc on rc.type = 'money'
                    where visibility = 1 and T1.device_category_level = 1
                    and year(T2.date) = $year

                    and device_category_code in('cat_4')
                    group by  T3.department_id, rc.rate;");

            $total_load_cost_sum =  DB::select("
                    select
                           max(cast(T2.date as date)) as date ,
                           year(max(T2.date))as year,
                           max(T3.department_name) as name,
                           count(distinct date) as no_of_day,
                           max(T1.device_category) as group_name,
                           count(distinct month(date)) as no_of_month,

                           sum(rs485_30) as energy,
                           round(sum(rs485_30)* rc.rate,2) as energy_cost,
                           round(sum(net_kwh),1) as net_kwh,
                           round(sum(net_kwh)* rc.rate,2) as net_kwh_cost,
                           round(sum(kwh_delivered),1) as kwh_delivered,
                           round(sum(kwh_delivered * rc.rate),2) as kwh_delivered_cost
                       from device_details T1
                       join data_rs485_summary T2 on T1.device_id = T2.device_id
                       join department T3 on T3.department_id = T1.department_id
                       cross join rate_config rc on rc.type = 'money'
                       where visibility = 1 and T1.device_category_level = 1
                       and year(T2.date) = $year

                       and device_category_code in('cat_4')
                       group by rc.rate;");

            return  [
                'data'=> [
                    'totalcost' => $totalCost,
                    'total_load_cost' => $total_load_cost,
                    'total_load_cost_sum' =>  $total_load_cost_sum
                ]
        ];
    }
}
