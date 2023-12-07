<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Hourly_Graph_Controller extends Controller
{
    public function hourly_graph($date, $host, $device)
  {
      $query = "SELECT
      q2.dt_time,
      q2.hour,
      q2.client_Id,
      q2.device_id,
      ROUND(ABS(q1.wh_r - q2.wh_r)) AS value0,
      ROUND(ABS(q1.wh_d - q2.wh_d)) AS value1,
      ROUND(ABS(q1.wh_1 - q2.wh_1)) AS value2,
      ROUND(ABS(q1.wh_2 - q2.wh_2)) AS value3,
      ROUND(ABS(q1.wh_3 - q2.wh_3)) AS value4
  FROM
      (SELECT dt_time, HOUR, client_Id, device_id,wh_r,wh_d,wh_1, wh_2, wh_3
       FROM hourly_kwh
       WHERE DATE(dt_time) = ?
         AND HOUR = 23
         AND client_Id = ?
         AND device_id = ?) q1
  
  LEFT JOIN
  
      (SELECT dt_time, HOUR, client_Id, device_id, wh_r,wh_d,wh_1, wh_2, wh_3
       FROM hourly_kwh
       WHERE DATE(dt_time) = DATE(? + INTERVAL 1 DAY)
         AND HOUR = 0
         AND client_Id = ?
         AND device_id = ?) q2
  
  ON q1.device_id = q2.device_id
  
  union


SELECT 
    dt_time,
    HOUR,
    client_id,
    device_id,
    wh_r - COALESCE((SELECT wh_r FROM hourly_kwh h2 WHERE h2.dt_time < h1.dt_time AND h2.client_id = ? AND h2.device_id = ? ORDER BY h2.dt_time DESC LIMIT 1), 0) AS diff_wh_r,
    wh_d - COALESCE((SELECT wh_d FROM hourly_kwh h2 WHERE h2.dt_time < h1.dt_time AND h2.client_id = ? AND h2.device_id = ? ORDER BY h2.dt_time DESC LIMIT 1), 0) AS diff_wh_d,
    wh_1 - COALESCE((SELECT wh_1 FROM hourly_kwh h2 WHERE h2.dt_time < h1.dt_time AND h2.client_id = ? AND h2.device_id = ? ORDER BY h2.dt_time DESC LIMIT 1), 0) AS diff_wh_1,
    wh_2 - COALESCE((SELECT wh_2 FROM hourly_kwh h2 WHERE h2.dt_time < h1.dt_time AND h2.client_id = ? AND h2.device_id = ? ORDER BY h2.dt_time DESC LIMIT 1), 0) AS diff_wh_2,
    wh_3 - COALESCE((SELECT wh_3 FROM hourly_kwh h2 WHERE h2.dt_time < h1.dt_time AND h2.client_id = ? AND h2.device_id = ? ORDER BY h2.dt_time DESC LIMIT 1), 0) AS diff_wh_3
FROM 
    hourly_kwh h1
WHERE 
    DATE(dt_time) = ?
    AND client_id = ?
    AND device_id = ? AND HOUR != 0
ORDER BY 
    dt_time";

      $result = DB::select($query, [$date, $host, $device, $date, $host, $device, $host, $device,$host, $device, $host, $device, $host, $device, $host, $device, $date, $host, $device]);

      $filteredResult = [];
        $prevHour = null;
        $lastRow = null;

        foreach ($result as $index => $row) {
            $currentHour = $row->HOUR;
            $isLastRow = ($index === count($result) - 1); // Check if it's the last row

            if ($prevHour === null || abs($currentHour - $prevHour) <= 1 || ($prevHour === 0 && !$isLastRow)) {
                $filteredResult[] = $row;
            }

            $prevHour = $currentHour;
            $lastRow = $row;
        }

        // Check if the last row has an hour value of 0
        if ($lastRow !== null && $lastRow->HOUR === 0) {
            $filteredResult[] = $lastRow;
        }

//         $query_first_row = "SELECT dt_time, HOUR, HOST, device_id, dwh_R, dwh_D, dwh_1, dwh_2, dwh_3
//         FROM jnmc_all_kwh
//         WHERE DATE(dt_time) = ? 
//         AND HOST = ? 
//         AND device_id = ? 
//         LIMIT 1";
// // Assuming you've already executed the initial query and obtained its result
// $initialQueryResult = DB::select($query_first_row, [$date, $host, $device]);

// // Assuming you've already executed the complex query and obtained $filteredResult
// $firstFilteredRow = null;
// if (!empty($filteredResult)) {
//     $firstFilteredRow = $filteredResult[0];
// }

// // Check if the initial query result and the first filtered row are available
// if ($initialQueryResult && $firstFilteredRow && $firstFilteredRow->HOUR !== null) {
//     $condition = [
//         'HOUR' => $initialQueryResult[0]->HOUR,
//         'dt_time' => $initialQueryResult[0]->dt_time,
//         'HOST' => $initialQueryResult[0]->HOST,
//         'device_id' => $initialQueryResult[0]->device_id,
//     ];

//     $updateData = [
//         'dwh_R' => $firstFilteredRow->value,
//         'dwh_D' => $firstFilteredRow->value1,
//         'dwh_1' => $firstFilteredRow->value2,
//         'dwh_2' => $firstFilteredRow->value3,
//         'dwh_3' => $firstFilteredRow->value4,
//     ];

//     // Use the update() method to update the data
//     DB::table('jnmc_all_kwh')->where($condition)->update($updateData);
// }

// // Create a separate copy of filteredResult for insertion
// $insertionData = [];

// foreach ($filteredResult as $index => $row) {
//     if ($row->HOUR !== null) {
//         $insertionData[] = clone $row;
//     }
// }

// foreach ($insertionData as $index => $row) {
//     $condition = [
//         'HOUR' => $row->HOUR,
//         'dt_time' => $row->dt_time,
//         'HOST' => $row->HOST,
//         'device_id' => $row->device_id,
//     ];
    
//     $updateData = [
//         'dwh_R' => $row->value,
//         'dwh_D' => $row->value1,
//         'dwh_1' => $row->value2,
//         'dwh_2' => $row->value3,
//         'dwh_3' => $row->value4,
//     ];
    
//     if ($index === 0) {
//         // Insert the calculated values directly into the first row
//         DB::table('jnmc_all_kwh')->updateOrInsert($condition, $updateData);
//     } else {
//         // Insert or update the previous row's data
//         $previousRow = $insertionData[$index - 1];
//         $prevCondition = [
//             'HOUR' => $previousRow->HOUR,
//             'dt_time' => $previousRow->dt_time,
//             'HOST' => $previousRow->HOST,
//             'device_id' => $previousRow->device_id,
//         ];
        
//         DB::table('jnmc_all_kwh')->updateOrInsert($prevCondition, $updateData);
//     }
// }

return response()->json($filteredResult);
}
public function generateHourlyGraphData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $hostDeviceQuery = "SELECT DISTINCT HOST, device_id FROM jnmc_all_kwh GROUP BY HOST, device_id";
        $hostDeviceList = DB::select($hostDeviceQuery);

        $results = [];

        foreach ($hostDeviceList as $hostDevice) {
            $host = $hostDevice->HOST;
            $device = $hostDevice->device_id;

            $result = $this->hourly_graph_range($startDate, $endDate, $host, $device);

            $results[] = [
                'host' => $host,
                'device' => $device,
                'data' => $result,
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function hourly_graph_range($startDate, $endDate, $host, $device)
    {
        $dateRange = $this->generateDateRange($startDate, $endDate);
        $result = [];

        foreach ($dateRange as $date) {
            $data = $this->hourly_graph($date, $host, $device);
            $result[$date] = $data;
        }

        return $result;
    }

    private function generateDateRange($startDate, $endDate)
    {
        $currentDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $dateRange = [];

        while ($currentDate <= $endDate) {
            $dateRange[] = date('Y-m-d', $currentDate);
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $dateRange;
    }

    
}