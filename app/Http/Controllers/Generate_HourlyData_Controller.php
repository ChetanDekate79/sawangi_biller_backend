<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;


class Generate_HourlyData_Controller extends Controller
{
    public function generate_data($folder, $date, $id)
    {
        // Read data from CSV files
        $csvPaths = [
            "F:/wardha/Sawangi_Biller_Backend/public/tokenFiles/{$date}_{$folder}.csv",
            // Add more CSV paths if needed
        ];

        $csvData = [];

        foreach ($csvPaths as $csvPath) {
            if (file_exists($csvPath)) {
                $data = array_map('str_getcsv', file($csvPath));
                foreach ($data as $row) {
                    $csvData[] = $row;
                }
            }
        }

        // dd($csvData);


        // SQL query to fetch data from the database
        $query = "
            SELECT DISTINCT hk.client_id, hk.device_id
            FROM hourly_kwh AS hk
            WHERE hk.client_id = ?
            AND hk.device_id = ?
        ";

        // Execute the query and get the results
        $results = DB::select($query, [$folder, $id]);

$groupedData = [];

foreach ($csvData as $csvRow) {
    
    $clientId = $csvRow[1];
    $deviceId = $csvRow[2];
    $dt       = $csvRow[0];

    // Check if this client_id and device_id combination exists in groupedData
    if (!isset($groupedData[$clientId][$deviceId])) {
        $groupedData[$clientId][$deviceId] = [
            'client_id' => $clientId,
            'device_id' => $deviceId,
            'dt'        => $dt,
            'data' => [],
        ];
    }

    $dt_time = strtotime($csvRow[0]);
    $hour = date('H', $dt_time);
    $minute = date('i', $dt_time);
    $formattedTime = date('H:i', $dt_time);
    $formattedDate = date('Y-m-d', $dt_time);

    // Check if there is already data for this hour
    if (isset($groupedData[$clientId][$deviceId]['data'][$hour])) {
        // If the current timestamp is newer, update the existing data
        if ($formattedTime < $groupedData[$clientId][$deviceId]['data'][$hour]['min_time']) {
            $groupedData[$clientId][$deviceId]['data'][$hour] = [
                'hour' => $hour,
                'min_time' => $formattedTime,
                'd_t' => $csvRow[0],
                'wh_R' => $csvRow[22],
                'wh_D' => $csvRow[24],
                'wh_1' => $csvRow[25],
                'wh_2' => $csvRow[26],
                'wh_3' => $csvRow[27],
                'dt_time' => $formattedDate,
            ];
        }
    } else {
        // No data for this hour yet, add it
        $groupedData[$clientId][$deviceId]['data'][$hour] = [
            'hour' => $hour,
            'min_time' => $formattedTime,
            'd_t' => $csvRow[0],
            'wh_R' => $csvRow[22],
            'wh_D' => $csvRow[24],
            'wh_1' => $csvRow[25],
            'wh_2' => $csvRow[26],
            'wh_3' => $csvRow[27],
            'dt_time' => $formattedDate,
        ];
    }
}

// Convert the associative array to a simple array with a single row for each hour
$finalOutput = [];

foreach ($groupedData as $clientId => $devices) {
    foreach ($devices as $deviceId => $data) {
        foreach ($data['data'] as $hourData) {
            $finalOutput[] = [
                'd_t' => $hourData['d_t'],
                'client_id' => $data['client_id'],
                'device_id' => $data['device_id'],
                'hour' => $hourData['hour'],
                // 'min_time' => $hourData['min_time'],
                'wh_R' => $hourData['wh_R'],
                'wh_D' => $hourData['wh_D'],
                'wh_1' => $hourData['wh_1'],
                'wh_2' => $hourData['wh_2'],
                'wh_3' => $hourData['wh_3'],
                'dt_time' => $hourData['dt_time']
            ];
        }
    }
}
// Sort the final output based on 'hour'
usort($finalOutput, function($a, $b) {
    return $a['hour'] <=> $b['hour'];
});

 // Filter the final output to include only the data for the specified $id
 $filteredOutput = array_filter($finalOutput, function($row) use ($id) {
    return $row['device_id'] == $id;
});

// Insert data into the database
foreach ($filteredOutput as $row) {
    // Convert the dt_time to the correct format
    $dtTime = date('Y-m-d H:i:s', strtotime($row['d_t']));

    // Check if a row with the same hour, client_id, and device_id exists
    $existingRow = DB::table('hourly_kwh')
        ->where('hour', $row['hour'])
        ->where('client_id', $row['client_id'])
        ->where('device_id', $row['device_id'])
        ->first();

    if (!$existingRow) {
        DB::table('hourly_kwh')->updateOrInsert([
            'client_id' => $row['client_id'],
            'device_id' => $row['device_id'],
            'dt_time' => $dtTime,
            'hour' => $row['hour'],
            'wh_R' => $row['wh_R'],
            'wh_D' => $row['wh_D'],
            'wh_1' => $row['wh_1'],
            'wh_2' => $row['wh_2'],
            'wh_3' => $row['wh_3'],
        ]);
    }
}

// Return the filtered and sorted output as a JSON response
return response()->json(array_values($filteredOutput));
}
}