<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{


	public function getLiveData($date)
    {
        $data = DB::select("
            SELECT date, project_id, amb_temp, humidity,
                client_name
            FROM sensor_summary T1 
                JOIN clientconfig T2 ON UPPER(T1.project_id) = UPPER(T2.client_code)
                WHERE T1.date = '$date';" );
		return [
			'data' => $data
		];
    }
	
	 public function getGraphData($date, $id)
    {
        $data = DB::select("
            SELECT T1.dt_time, project_id, amb_temp, humidity,
                client_name
                FROM sensor_data T1 
                    JOIN clientconfig T2 ON LOWER(T1.project_id) = LOWER(T2.client_code)
                    WHERE cast(T1.DT_TIME AS DATE) = '$date'
                    AND LOWER(project_id) = LOWER('$id')
                ");
		return [
			'data' => $data
		];
    }

}
