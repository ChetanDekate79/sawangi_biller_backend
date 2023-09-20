<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class CsvDataController extends Controller
{
    public function getByFolderDateId($folder, $date, $id)
    {
        $csvPaths = [
            "F:/wardha/data/tokenFiles/{$date}_{$folder}-6435-mfd_data.csv",
            // "C:/Inetpub/vhosts/hetadatain.com/wardha.hetadatain.com/JNMC_2_copy/$folder/{$date}_{$folder}.csv",
            // "C:/Inetpub/vhosts/hetadatain.com/wardha.hetadatain.com/JNMC_3_copy/$folder/{$date}_{$folder}.csv"
        ];
        $filteredData = [];

        foreach ($csvPaths as $csvPath) {
            if (file_exists($csvPath)) {
                // Generate the new path to copy the file
                $newCsvPath = "F:/wardha/data/copied_data/copied_data-6435-mfd_data.csv";

                // Copy the file to the new location
                if (copy($csvPath, $newCsvPath)) {
                    // Read data from the copied file
                    $data = array_map('str_getcsv', file($newCsvPath));

                    // Filter the data based on the given $id
                    $filteredData = array_merge($filteredData, array_filter($data, function ($row) use ($id) {
                        return isset($row[2]) && $row[2] == $id;
                    }));
                }
                echo get_current_user();
            }
        }
        return response()->json(array_values($filteredData));
    }
}
