<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnergyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        //
    }


public function getCurrentDateTime()
    {
        $latest_date_time = DB::select('select dt_time as dt_time from productivity_data_rs485_summary
        order by date desc,dt_time desc
        limit 1;');
        // return  $latest_date_time[0]->date;
        return $latest_date_time[0]->dt_time;
        //return [$this->$latest_date_time[0]->dt_time];
    }


	public function getDeviceData($device, $date, $parameter)
    {
		$parameter = $this->devide1000($parameter);
        $data = DB::select("
        select $parameter as value , T2.device_name as name,dt_time,
            meter_detail from data_rs485 T1
        join device_details_energy T2
        on T1.DEVICE_ID = T2.device_id
        where date = '$date'
        and (T1.device_id = '$device' /*or T2.device_name = '$device'*/) order by T1.dt_time;" );


		/*$data = DB::table('view_data_rs485 T1')
				->join('device_details T2', 'T1.DEVICE_ID', '=', 'T2.device_id')
				->select($parameter.' as value' , 'T2.device_name as name','dt_time','meter_detail')
				->get();*/

		return [
			'data' => $data
		];
    }

	public function getGroupDeviceData($group, $date)
    {
        $data = DB::select("
            select 
		sum(ifnull(rs485_1/1000,0)) as import_kw , 
		sum(ifnull(rs485_52/1000,0)) as export_kw  , 
		dt_time, 
		MAX(T1.device_category)     
		from 	(select date, T1.DEVICE_ID, rs485_1 , rs485_52  , T2.device_category	, 
	  		/*if(minute(dt_time)%2 = 0,dt_time, date_sub(dt_time, interval 1 minute) ) as*/ dt_time
    	
			from data_rs485 T1
			join device_details_energy T2
			on T1.DEVICE_ID = T2.device_id			
			where DATE = '$date'		
			and T2.device_category_code = '$group') T1
		group by dt_time; "
				);
		return [
			'data' => $data
		];
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\model\energy  $energy
     * @return \Illuminate\Http\Response
     */
    public function show(energy $energy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\model\energy  $energy
     * @return \Illuminate\Http\Response
     */
    public function edit(energy $energy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\model\energy  $energy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, energy $energy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\model\energy  $energy
     * @return \Illuminate\Http\Response
     */
    public function destroy(energy $energy)
    {
        //
    }
	public function devide1000($parameter){
		$arr = ['RS485_1','RS485_2','RS485_3','RS485_4','RS485_30','RS485_34','RS485_49','RS485_51','RS485_52'];
		if(in_array($parameter,$arr)){
			return $parameter.'/1000';
		}
		else{
			return $parameter;
		}
	}
}
