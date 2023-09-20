<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\pump_report; 
use Illuminate\Http\Request;

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

        // SQL query to fetch data from the database
        $query = "
            SELECT DISTINCT hk.client_id, hk.device_id
            FROM hourly_kwh AS hk
            WHERE hk.client_id = ?
            AND hk.device_id = ?
        ";

        // Execute the query and get the results
        $results = DB::select($query, [$folder, $id]);

        // Prepare an associative array to group data by hour
        $groupedData = [];

        foreach ($csvData as $csvRow) {
            foreach ($results as $result) {
                if ($csvRow[1] === $result->client_id && $csvRow[2] == $result->device_id) {
                    $dt_time = strtotime($csvRow[0]);
                    $hour = date('H', $dt_time);
                    $minute = date('i', $dt_time);
                    $formattedTime = date('H:i', $dt_time);
                    $formattedDate = date('Y-m-d', $dt_time);

                    if (!isset($groupedData[$hour])) {
                        $groupedData[$hour] = [
                            'hour' => $hour,
                            'min_time' => $formattedTime,
                            'wh_R' => $csvRow[22], // New column for first row of 22nd column
                            'wh_D' => $csvRow[24], // New column for first row of 24th column
                            'wh_1' => $csvRow[25], // New column for first row of 25th column
                            'wh_2' => $csvRow[26], // New column for first row of 26th column
                            'wh_3' => $csvRow[27], // New column for first row of 27th column
                            'dt_time' => $formattedDate,
                        ];
                    } elseif ($formattedTime < $groupedData[$hour]['min_time']) {
                        // Update with new minimum time row
                        $groupedData[$hour]['min_time'] = $formattedTime;
                        $groupedData[$hour]['wh_R'] = $csvRow[22]; // Update wh_R with new row value
                        $groupedData[$hour]['wh_D'] = $csvRow[24];
                        $groupedData[$hour]['wh_1'] = $csvRow[25];
                        $groupedData[$hour]['wh_2'] = $csvRow[26];
                        $groupedData[$hour]['wh_3'] = $csvRow[27];
                        $groupedData[$hour]['dt_time'] = $formattedDate;
                    }

                    break; // Match found, no need to check further SQL rows
                }
            }
        }

        // Convert the associative array to a simple array
        $finalOutput = array_values($groupedData);

        // Return the final output as a JSON response
        return response()->json($finalOutput);
    }
}