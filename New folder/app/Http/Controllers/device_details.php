<?php
namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class device_details extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	CONST CACHE_KEY = 'device_details1235';
    public function getDeviceDetails()
    {
      //  return energy_device_detail::all();
	  	$cacheKey = $this->getCacheKey();
        $data = cache()->remember($cacheKey, Carbon::now()->addMinutes(500), function (){
			return  [
				'data'=> [
					'energy'=>[
						'device'=> DB::select("select * from device_details_energy
 where visibility = 1 and client_id!= 'V1'

						order by report_sort asc"),
						'parameter_details'=> DB::select("select * from data_rs485_alies_name
						where device_name = 'mfd' and  name !=''")
					],
					'productivity'=>[
						'device'=> DB::select("select * from device_details_productivity
						where visibility = 1
						order by report_sort asc"),
						'parameter_details'=> DB::select("select * from data_rs485_alies_name
						 where device_type = 3 and  name !=''")
					]
				]
			 ];
	   });
      return $data;
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
    public function addDisplayText(Request $request)
    {
        $resp =  DB::insert('insert into display_text (name) values (?)', [$request->text]);
        if($resp){
            return response()->json(['data'=>$resp],200);
        }
        else
        return response()->json(['error'=>$resp],500);
    }
    public function getDisplayText()
    {
        return DB::select("select id,name,msg from display_text");
    }
    public function setDisplayText(Request $request)
    {
        $resp =  DB::update("update display_text SET name='$request->text' WHERE  id=$request->id");
        if($resp){
            return response()->json(['data'=>$resp],200);
        }
        else
            return response()->json(['error'=>$resp],500);
    }

    public function deleteDisplayText(Request $request)
    {
        $resp =  DB::delete("delete from  display_text   WHERE  id=$request->id");
        if($resp){
            return response()->json(['data'=>$resp],200);
        }
        else
            return response()->json(['error'=>$resp],500);

    }
    public function deleteAllDisplayText(Request $request)
    {
        $resp =  DB::delete("delete from  display_text ");
        if($resp){
            return response()->json(['data'=>$resp],200);
        }
        else
            return response()->json(['error'=>$resp],500);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\model\energy_device_detail  $energy_device_detail
     * @return \Illuminate\Http\Response
     */

	 public function getDeviceParameter()
    {
        return DB::select("select * from data_rs485_alies_name
where device_name = 'mfd' and  name !=''");
    }


   
    public function getCacheKey()
    {
        return self::CACHE_KEY;
    }
}
