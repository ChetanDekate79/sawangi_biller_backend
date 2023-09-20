<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DatetimeController extends Controller
{
    public function getCurrentDatetime($folder, $date)
    {
        date_default_timezone_set('Asia/Kolkata');
        $currentDatetime = date('d-m-Y H:i');

        $csvPattern = "{$date}_{$folder}-6435-mfd_data.csv";
        $csvPath = "F:/wardha/Sawangi_Biller_Backend/public/tokenFiles/{$csvPattern}";
        $filteredData = [];
        $processedDeviceIds = [];
        $dataWithin10Minutes = [];
        $lastDateTimeById = []; // New array to track last date and time for each ID

        $matchingFiles = glob($csvPath);
        foreach ($matchingFiles as $file) {
            $data = array_map('str_getcsv', file($file));
            $filteredData = array_merge($filteredData, array_filter($data));
        }

        foreach ($filteredData as $csvRow) {
            // Assuming the first 3 columns of the CSV contain the relevant data.
            $csvRow = array_slice($csvRow, 0, 3);
            $deviceId = $csvRow[2];

            // Check if the device ID is already processed, if yes, skip it.
            if (in_array($deviceId, $processedDeviceIds)) {
                continue;
            }

            // Assuming the first column of the CSV contains the date and time in "d-m-Y H:i" format.
            $csvDateTime = \DateTime::createFromFormat('d-m-Y H:i', $csvRow[0]);
            $tenMinutesAgo = (new \DateTime())->sub(new \DateInterval('PT10M'));

            if ($csvDateTime > $tenMinutesAgo && $csvDateTime <= new \DateTime()) {
                $dataWithin10Minutes[] = $csvRow;
            }

            // Update the last date and time for each unique ID
            if (!isset($lastDateTimeById[$deviceId]) || $csvDateTime > $lastDateTimeById[$deviceId]) {
                $lastDateTimeById[$deviceId] = $csvDateTime;
            }

            // Mark the device ID as processed.
            $processedDeviceIds[] = $deviceId;
        }

        // Find the maximum date and time from column 1 of the CSV
        $maxDateTime = null;
        foreach ($filteredData as $csvRow) {
            $csvDateTime = \DateTime::createFromFormat('d-m-Y H:i', $csvRow[0]);
            if ($csvDateTime instanceof \DateTime) {
                if ($maxDateTime === null || $csvDateTime > $maxDateTime) {
                    $maxDateTime = $csvDateTime;
                }
            }
        }

        // Calculate the difference between current_datetime and max_dt in hours, minutes, and seconds
        $currentDtObj = \DateTime::createFromFormat('d-m-Y H:i', $currentDatetime);
        $maxDtObj = $maxDateTime ? \DateTime::createFromFormat('d-m-Y H:i', $maxDateTime->format('d-m-Y H:i')) : null;

        // Format the maximum date and time as needed for the database column
        $maxDateTimeFormatted = $maxDtObj !== null ? $maxDtObj->format('d-m-Y H:i:s') : null;

        // Calculate the difference between current_datetime and max_dt in hours, minutes, and seconds
        $diffFormatted = 'N/A'; // Default value for the difference
        if ($maxDtObj !== null) {
            $diffInterval = $currentDtObj->diff($maxDtObj);
            $diffHours = $diffInterval->h;
            $diffMinutes = $diffInterval->i;
            $diffSeconds = $diffInterval->s;

            // Format the difference as HH:MM:SS
            $diffFormatted = sprintf("%02d:%02d:%02d", $diffHours, $diffMinutes, $diffSeconds);
        }

        // Adding the SQL query to fetch distinct client_id, device_id, and floor_no from the hourly_kwh table
        $distinctData = \DB::select("SELECT DISTINCT hk.client_id, hk.device_id, r.floor_no
        FROM room_mfd AS hk
        INNER JOIN rooms AS r ON hk.room_id = r.room_id
        WHERE hk.client_id = 'Durga'
        ORDER BY hk.device_id, hk.client_id");

        // Processing the data to compare CSV column 2 with database column 1 and CSV column 3 with database column 2
        $resultData = [];
        foreach ($distinctData as $row) {
            $clientId = $row->client_id;
            $deviceId = $row->device_id;
            $floorNo = $row->floor_no; // Get the floor_no from the database result
            $state = 0; // Default value for the "state" column (0 for non-match)
            $dt_time = null; // Initialize the dt_time column
            $timeDiff = null; // Initialize the time difference column

            // Set the last date and time for each ID from the $lastDateTimeById array
            if ($maxDateTime) {
                // Calculate the time difference between current_datetime and dt_last
                $currentDtObj = \DateTime::createFromFormat('d-m-Y H:i', $currentDatetime);
$maxDtObj = $maxDateTime ? \DateTime::createFromFormat('d-m-Y H:i', $maxDateTime->format('d-m-Y H:i')) : null;
$diffInterval = $currentDtObj->diff($maxDtObj);

$maxDtObj = \DateTime::createFromFormat('d-m-Y H:i', $maxDateTime->format('d-m-Y H:i'));

            } else {
                $dtLastObj = null; // If no data found for the ID, set it to null
            }

            foreach ($dataWithin10Minutes as $csvRow) {
                // Assuming the second and third columns of the CSV contain the "client_id" and "device_id" information.
                // Change the indices (1 and 2) according to your CSV structure if needed.
                if ($csvRow[1] == $clientId && $csvRow[2] == $deviceId) {
                    // Assuming the first column of the CSV contains the date and time in "d-m-Y H:i" format.
                    // Change the index (0) according to your CSV structure if needed.
                    $csvDateTime = \DateTime::createFromFormat('d-m-Y H:i', $csvRow[0]);
                    $dt_time = $csvDateTime->format('Y-m-d H:i:s');

                    // Assuming the fourth column of the CSV contains the "state" information.
                    // Change the index (3) according to your CSV structure if needed.
                    $state = 1; // Set state to 1 for matches

                    break;
                }
            }

            // Add the 'last_dt' and 'max_dt' to the result data
            $resultData[] = [
                'client_id' => $clientId,
                'device_id' => $deviceId,
                'floor_no' => $floorNo,
                'state' => $state,
                'dt_time' => $dt_time,
                'max_dt' => $maxDateTimeFormatted, // Add the maximum date and time to the result data
                'diff_dt' => $diffFormatted, // Add the difference between current_datetime and max_dt as 'diff_dt'
                'time_diff' => $timeDiff, // Add the time difference as 'time_diff' to the result data
            ];
        }

        // Return the result as a JSON response
        return response()->json([
            'current_datetime' => $currentDatetime,
            'data_within_10_minutes' => $dataWithin10Minutes,
            'distinct_data' => $resultData,
        ]);
    }
}
