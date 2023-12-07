<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class Device_DetailsController extends Controller
{
    public function host()
    {
        // Build your SQL query
        $query = "SELECT DISTINCT client_id as client_name FROM room_mfd;";

        // Execute the query
        $results = DB::select($query);

        // Return the results
        return $results;
    }

    public function device(Request $request)
    {
        // Get the client_id from the request
        $client_id = $request->query('client_id');
        
        // Build your SQL query
        $query = "SELECT DISTINCT device_id as device_name FROM room_mfd WHERE client_id = '$client_id'  order by device_id";
    
        // Execute the query
        $results = DB::select($query);
    
        // Return the results
        return $results;
    }

    public function room(Request $request)
    {
        // Get the client_id from the request
        $room_no = $request->query('room_no');
        
        // Build your SQL query
        $query = "select distinct r.room_no FROM  
        rooms r
          LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
          LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
          LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
         where r.hostel_id = '$room_no' 
         order by r.room_no
         ";
    
        // Execute the query
        $results = DB::select($query);

        // Manually add "all" to the result and place it at the beginning
        array_unshift($results, (object) ['room_no' => 'All']);
    
        // Return the results
        return $results;
    }

    public function hostel()
    {
        // Build your SQL query
        $query = "SELECT DISTINCT hostel_id FROM rooms ORDER BY hostel_id;";

        // Execute the query
        $results = DB::select($query);

        // Return the results
        return $results;
    }
    
}
