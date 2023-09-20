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
