<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyEnergyController extends Controller
{
    public function getEnergyDataMonthlyForYear($group_id,$year)
    {
        $groups_monthly_energy =  DB::select("
                select
                monthname(max(T2.date)) as month ,
                max(T1.device_category_code) as  group_id,
                max(T1.device_category) as name,
                round(sum(T2.rs485_30)) as energy,
                round(sum(T2.rs485_30)* rc.rate) as energy_cost
            from device_details T1
            join data_rs485_summary T2 on T1.device_id = T2.device_id
            cross join rate_config rc on rc.type = 'money'
            where visibility = 1
            and T1.device_category_code = '$group_id'
            and year(T2.date) = 2019
            group by month(T2.date), rc.rate, T1.device_category_code");

        $pichart_monthly_energy =  DB::select("
                    select
                        monthname(max(T2.date)) as month ,
                        max(T1.device_id) as  group_id,
                        max(T1.name) as name,
                        sum(kwh) as energy,
                        sum(kwh)* pd.price as energy_cost
                    from energy_device_details T1
                    join daily_energies T2 on T1.device_id = T2.device_id
                    join group_info T3 on T3.id = T1.group_id
                    cross join price_detail pd on pd.unit = 'kwh'
                    where T3.id = $group_id
                    and year(T2.date) =  $year
                    group by T1.device_id ,pd.price",
                ['1990-05-25','Grid','1990-05-25','Grid']);
                return  [
            'data'=> [
                'linechart' =>$groups_monthly_energy,
                'pichart' => $pichart_monthly_energy
            ]
         ];
    }
}
