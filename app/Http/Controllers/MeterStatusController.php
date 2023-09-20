<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import the DB facade
use Carbon\Carbon;

class MeterStatusController extends Controller
{
    public function processCsv($folder, $date)
    {
        // Read data from CSV files
        $csvPaths = [
            "F:/wardha/Sawangi_Biller_Backend/public/tokenFiles/{$date}_{$folder}-6435-mfd_data.csv",
            // Add other CSV file paths if needed
        ];

        $csvData = [];
        $uniqueSecondThirdColumns = []; // To store unique combinations of the 2nd and 3rd columns

        foreach ($csvPaths as $csvPath) {
            if (file_exists($csvPath)) {
                $data = array_map('str_getcsv', file($csvPath));
                foreach ($data as $row) {
                    $secondColumn = $row[1];
                    $thirdColumn = $row[2];
                    $timestamp = strtotime($row[0]);

                    $columnCombination = $secondColumn . '-' . $thirdColumn;

                    if (!in_array($columnCombination, $uniqueSecondThirdColumns)) {
                        // If the combination is unique, add the row to the $csvData array
                        $uniqueSecondThirdColumns[] = $columnCombination;
                        $csvData[$columnCombination] = [$row[0], $row[1], $row[2]]; // Select only the first three columns
                    } else {
                        // If the combination is not unique, check the timestamp to select the row with the largest value
                        $existingTimestamp = strtotime($csvData[$columnCombination][0]);
                        if ($timestamp > $existingTimestamp) {
                            $csvData[$columnCombination] = [$row[0], $row[1], $row[2]]; // Select only the first three columns
                        }
                    }
                }
            }
        }

        // Get the values from the $csvData array
        $csvData = array_values($csvData);

        // SQL query to fetch data from the database
        $query = "
            SELECT DISTINCT hk.client_id, hk.device_id, r.floor_no
            FROM room_mfd AS hk
            INNER JOIN rooms AS r ON hk.room_id = r.room_id
            WHERE hk.client_id = 'Durga'
            ORDER BY hk.device_id, hk.client_id
        ";

        // Execute the query and get the results
        $results = DB::select($query);

        // Convert "00:10:00" (10 minutes) to seconds
        $tenMinutesInSeconds = 10 * 60;

        // Get the current date and time in the Asia/Kolkata timezone
        $currentTime = Carbon::now('Asia/Kolkata')->format('d-m-Y H:i:s');

        // Merge CSV data and SQL query results into a single array based on the condition
        $mergedData = [];
        foreach ($csvData as $csvRow) {
            $csvSecondColumn = $csvRow[1];
            $csvThirdColumn = $csvRow[2];
            $columnCombination = $csvSecondColumn . '-' . $csvThirdColumn;

            foreach ($results as $result) {
                $sqlSecondColumn = $result->client_id;
                $sqlThirdColumn = $result->device_id;

                if ($csvSecondColumn === $sqlSecondColumn && $csvThirdColumn == $sqlThirdColumn) {
                    // Merge the CSV and SQL data based on the matching combination
                    $mergedRow = array_merge($csvRow, [$result->floor_no]);

                    // Add the current date and time
                    $mergedRow[] = $currentTime;

                    // Calculate the difference between the first and last columns and add it as a new column
                    $firstColumnTimestamp = strtotime($csvRow[0]);
                    $lastColumnTimestamp = strtotime($currentTime);
                    $differenceInSeconds = abs($lastColumnTimestamp - $firstColumnTimestamp);

                    // Convert the difference to HH:MM:SS format
                    $differenceHHMMSS = sprintf('%02d:%02d:%02d', ($differenceInSeconds / 3600), ($differenceInSeconds / 60 % 60), ($differenceInSeconds % 60));
                    $mergedRow[] = $differenceHHMMSS;

                    // Add a new column to compare the difference with 10 minutes (00:10:00) in seconds
                    $isDifferenceLessThanOrEqualTo10Minutes = ($differenceInSeconds <= $tenMinutesInSeconds) ? 1 : 0;
                    $mergedRow[] = $isDifferenceLessThanOrEqualTo10Minutes;

                    $mergedData[] = $mergedRow;
                    break; // We found a match, no need to check further SQL rows
                }
            }
        }

        // Return the merged data as a JSON response
        return response()->json($mergedData);
    }
}
