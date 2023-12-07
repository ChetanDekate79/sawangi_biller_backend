<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use DateTime;

class Bill_Controller extends Controller
{
    public function Monthly_Bill($hostel, $room, $month, $year, $rate, $comm_area){
        // Calculate the first and last date of the month
        $start_date = date('Y-m-01', strtotime("$year-$month-01"));
        $end_date = date('Y-m-t', strtotime("$year-$month-01"));

        // Find the previous month and year
        $previousMonthYear = (new DateTime("$year-$month-01"))->modify('-1 month');
        $previousMonth = $previousMonthYear->format('m');
        $previousYear = $previousMonthYear->format('Y');

//         $roomandsrudentdsQuery = "SELECT DISTINCT S.student_id,S.room_no FROM (
//             SELECT sa.student_id,r.room_no, sa.joining_date, sa.leaving_date, DATE(hk.dt_time) AS d_t 
//             FROM rooms r
//             LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
//             LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
//             LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
//             WHERE DATE(hk.dt_time) BETWEEN '$start_date' AND '$end_date' AND r.hostel_id = '$hostel'
//             AND (sa.joining_date IS NULL OR sa.joining_date > '$start_date')
//             AND (sa.leaving_date IS NULL OR sa.leaving_date < '$end_date')
//             AND hk.device_id <> 31
//         ) S order by room_no";

// $roomandsrudentdsResult = DB::select($roomandsrudentdsQuery);

// $room_no_result = array_column($roomandsrudentdsResult, 'room_no');

// $quotedroom_no_result = "'" . implode("','", $room_no_result) . "'";


    
//         $studentIdsQuery = "SELECT DISTINCT student_id,room_no FROM (
//             SELECT sa.student_id,r.room_no, sa.joining_date, sa.leaving_date, DATE(hk.dt_time) AS d_t 
//             FROM rooms r
//             LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
//             LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
//             LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
//             WHERE DATE(hk.dt_time) BETWEEN '$start_date' AND '$end_date'
// 				AND r.hostel_id = '$hostel' 
// 				and r.room_no IN ($quotedroom_no_result)
				
// 				 AND hk.device_id <> 31
//         ) S ORDER BY room_no";
    
//         $studentIdsQuery2 =   "SELECT DISTINCT student_id,room_no FROM (
//             SELECT sa.student_id,r.room_no, sa.joining_date, sa.leaving_date, DATE(hk.dt_time) AS d_t 
//             FROM rooms r
//             LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
//             LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
//             LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
//             WHERE DATE(hk.dt_time) BETWEEN '$start_date' AND '$end_date'
// 				AND r.hostel_id = '$hostel' 
// 				and r.room_no not IN ($quotedroom_no_result)
				
// 				 AND hk.device_id <> 31
//         ) S ORDER BY room_no
          
//       ";
    

// // Execute the query to get studentIds
// $studentIdsResult = DB::select($studentIdsQuery);

// $studentIdsResult2 = DB::select($studentIdsQuery2);


// // Extract student IDs from the result
// $studentIds = [];
// foreach ($studentIdsResult as $row) {
//     $studentIds[] = $row->student_id;
// }

// // Extract student IDs from the result
// $studentIds = array_column($studentIdsResult, 'student_id');

// // // $room_nos = array_column($studentIdsResult, 'room_no');


// $studentIds2 = array_column($studentIdsResult2, 'student_id');


// // // Convert the array of student IDs to a comma-separated string
// $quotedStudentIds = "'" . implode("','", $studentIds) . "'";

// // // $quotedRoomnos = "'" . implode("','", $room_nos) . "'";


// $quotedStudentIds2 = "'" . implode("','", $studentIds2) . "'";


if ($room === 'All') {

    $room_no = " ";

    // $result = $this->monthly_bill_half($hostel, $room_no, $start_date, $end_date, $rate, $comm_area, $quotedStudentIds);

    $result2 = $this->monthly_bill_complete($hostel, $room_no, $month,$year,$previousMonth,$previousYear, $start_date,$end_date, $rate, $comm_area);

    $combinedResult = $result2;

    

} else {

    $room_no = "and room_no = '$room'" ;
   
// $result = $this->monthly_bill_half($hostel, $room_no, $start_date, $end_date, $rate, $comm_area, $quotedStudentIds);

$result2 = $this->monthly_bill_complete($hostel, $room_no, $month,$year,$previousMonth,$previousYear, $start_date,$end_date, $rate, $comm_area);

$combinedResult =  $result2;
    
}
        $sum_units = 0;
        $sum_total = 0;
		$common_area_total = 0;
        $Amount_total = 0;

// Calculate summary values
foreach ($combinedResult as $value) {
    $sum_units += $value->Units ?? 0;
    $sum_total += $value->SUM ?? 0;
    $Amount_total += $value->Total_Amount ?? 0;
    $common_area_total += ($value->ca_amount ?? 0);

    // Calculate the distinct room_no count and student_id count
    $distinctRoomNumbers[$value->room_no] = true;
    $distinctStudentIds[$value->student_id] = true;

    // To get the counts, use count() function on the arrays after processing all the records.
    $distinctRoomCount = count($distinctRoomNumbers);
    $distinctStudentCount = count($distinctStudentIds);

}
if ($distinctRoomCount > 1) {
    DB::table('monthly_bill')->updateOrInsert(
     ['start' => $start_date, 'end' => $end_date, 'hostel_id' => $hostel],
     [
         'hostel_room_units' => $sum_units,
         'bill_amt' => $sum_total,
         'bill_ca_amt' => $common_area_total,
         'student_count' => $distinctStudentCount,
         'room_count' => $distinctRoomCount,
         'total_bill_amt' => $Amount_total,
         // Add other columns and their values here as needed
     ]
 );
 
 }
// Round the sum_units to 2 decimal places
$roundedSumUnits = round($sum_units, 2);

// Add summary values to the combined result
$combinedResult['sum_units'] = $roundedSumUnits;
$combinedResult['sum_total'] = $sum_total;
$combinedResult['Amount_total'] = $Amount_total;

// $combinedResult['previous_month'] = $previousMonth;
// $combinedResult['previous_year'] = $previousYear;



return $combinedResult;

// return $quotedStudentIds;
}

public function monthly_bill_half($hostel,$room,$start_date,$end_date,$rate,$comm_area, $commaSeparatedIds){

    $query = "
    SELECT 
    Z.hostel_id,
    Z.room_no,
	 Z.Student_id,
	 round(sum(Z.each_unit),2) AS Units,
	 Z.rate, 
    round(sum(Z.Amount),0) AS SUM,
    Z.common_area,
    Z.total_days,
	Z.common_area * Z.total_days as ca_amount,
    round(sum(Z.Amount) +( Z.total_days * Z.common_area),0) AS Total_Amount
FROM (
    SELECT *,
    ROUND(A.difference / sc.room_no_count2, 2) AS each_unit,
    $rate AS rate,
    ROUND((A.difference / sc.room_no_count2) * $rate, 2) AS amount,
    $comm_area AS common_area,
  A.NO_DAY AS total_days
FROM (
  SELECT 
    DATE(hk.dt_time) AS dt_time,
    r.hostel_id,
    hk.device_id,
    r.room_no,
    r.room_id,
    sa.student_id,
    rm.phase, 
    sa.leaving_date,
    sa.joining_date,
    MAX(
        CASE 
            WHEN rm.phase = 1 THEN hk.wh_1
            WHEN rm.phase = 2 THEN hk.wh_2
            WHEN rm.phase = 3 THEN hk.wh_3
        END
    ) AS max_wh,
    MIN(
        CASE 
            WHEN rm.phase = 1 THEN hk.wh_1
            WHEN rm.phase = 2 THEN hk.wh_2
            WHEN rm.phase = 3 THEN hk.wh_3
        END
    ) AS min_wh,
    COALESCE(
        (SELECT 
            MIN(
                CASE 
                    WHEN rm2.phase = 1 THEN hk2.wh_1
                    WHEN rm2.phase = 2 THEN hk2.wh_2
                    WHEN rm2.phase = 3 THEN hk2.wh_3
                END
            ) 
        FROM hourly_kwh hk2
        LEFT JOIN room_mfd rm2 ON hk2.client_id = rm2.client_id AND hk2.device_id = rm2.device_id
        WHERE DATE(hk2.dt_time) = DATE_ADD(DATE(hk.dt_time), INTERVAL 1 DAY)
            AND hk2.client_id = hk.client_id
            AND hk2.device_id = hk.device_id
            AND rm2.room_id = r.room_id), 
        MAX(
            CASE 
                WHEN rm.phase = 1 THEN hk.wh_1
                WHEN rm.phase = 2 THEN hk.wh_2
                WHEN rm.phase = 3 THEN hk.wh_3
            END
        )
    ) AS min_wh_next_day,
    (
        COALESCE(
            (SELECT 
                MIN(
                    CASE 
                        WHEN rm2.phase = 1 THEN hk2.wh_1
                        WHEN rm2.phase = 2 THEN hk2.wh_2
                        WHEN rm2.phase = 3 THEN hk2.wh_3
                    END
                ) 
            FROM hourly_kwh hk2
            LEFT JOIN room_mfd rm2 ON hk2.client_id = rm2.client_id AND hk2.device_id = rm2.device_id
            WHERE DATE(hk2.dt_time) = DATE_ADD(DATE(hk.dt_time), INTERVAL 1 DAY)
                AND hk2.client_id = hk.client_id
                AND hk2.device_id = hk.device_id
                AND rm2.room_id = r.room_id), 
            MAX(
                CASE 
                    WHEN rm.phase = 1 THEN hk.wh_1 
                    WHEN rm.phase = 2 THEN hk.wh_2 
                    WHEN rm.phase = 3 THEN hk.wh_3 
                END
            )
        ) - MIN(
            CASE 
                WHEN rm.phase = 1 THEN hk.wh_1 
                WHEN rm.phase = 2 THEN hk.wh_2 
                WHEN rm.phase = 3 THEN hk.wh_3 
            END
        )
    ) / 1000 AS difference,
    COUNT(r.room_no) OVER (PARTITION BY hk.dt_time, r.room_no) AS room_no_count,
    COUNT(sa.student_id) OVER (PARTITION BY sa.student_id, YEAR(hk.dt_time), MONTH(hk.dt_time)) AS NO_DAY
  FROM rooms r
  LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
  LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
  LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
  WHERE DATE(hk.dt_time) BETWEEN '$start_date' AND '$end_date'
  
    AND r.hostel_id = '$hostel'  $room
   and sa.student_id IN ($commaSeparatedIds)
	and hk.device_id <> 31
    AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
    -- Add the condition to exclude student_id based on joining_date
    AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
  GROUP BY DATE(hk.dt_time), hk.client_id, hk.device_id, rm.phase, r.room_no, sa.student_id
) A
LEFT JOIN (
    SELECT
     r.room_id AS room_id2,
     r.room_no AS room_no2,
     date(hk.dt_time) AS dt_time2,
     COUNT(DISTINCT sa.student_id) AS room_no_count2
   FROM rooms r
   LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
   LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
   LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
   WHERE r.hostel_id = '$hostel'    and hk.device_id <> 31 
     AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
     AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
      and DATE(hk.dt_time) BETWEEN '$start_date' AND '$end_date'
 GROUP BY dt_time2 ,  r.room_no
 ) sc
 ON A.room_id = sc.room_id2 AND A.dt_time = sc.dt_time2
ORDER BY DATE(A.dt_time)) Z GROUP BY Z.student_id
";

$result = DB::select($query);

// //return response()->json($result);
// $htmlContent = $this->generate_html_monthly($result,$hostel,$room,$start_date,$end_date);

// return view('billing_report', ['htmlContent' => $htmlContent]);

return $result;
}

public function monthly_bill_complete($hostel, $room_no, $month,$year,$previousMonth,$previousYear, $start_date,$end_date, $rate, $comm_area){

//     $query = "SELECT  Z.hostel_id, Z.device_id,Z.room_no,Z.student_id,round(Z.units,2) Units,$rate as rate,ROUND( Z.units * $rate,0) AS SUM, $comm_area AS common_area, Z.NO_Day as total_days ,Z.NO_Day * $comm_area  AS ca_amount, round(Z.NO_Day * $comm_area + Z.units * $rate , 0) AS Total_Amount 

// FROM (  SELECT Y.dt_time,Y.hostel_id,Y.device_id, Y.room_no,Y.room_id,Y.student_id,Y.phase,Y.leaving_date,Y.joining_date,X.max_wh, Y.max_wh AS min_wh,
//   X.max_wh - Y.max_wh AS difference, 
//  Y.room_no_count,( (X.max_wh - Y.max_wh) / Y.room_no_count) /1000 AS units , DAY(LAST_DAY(CONCAT(YEAR(CURRENT_DATE()), '-', LPAD(MONTH(Y.dt_time), 2, '0'), '-01'))) AS NO_DAY
//  FROM 
//   (SELECT 
//     DATE(hk.dt_time) AS dt_time,
//     r.hostel_id,
//     hk.device_id,
//     r.room_no,
//     r.room_id,
//     sa.student_id,
//     rm.phase, 
//     sa.leaving_date,
//     sa.joining_date,
//     MAX(
//         CASE 
//             WHEN rm.phase = 1 THEN hk.wh_1
//             WHEN rm.phase = 2 THEN hk.wh_2
//             WHEN rm.phase = 3 THEN hk.wh_3
//         END
//     ) AS max_wh,
   
//     COUNT(r.room_no) OVER (PARTITION BY hk.dt_time, r.room_no) AS room_no_count,
//     COUNT(sa.student_id) OVER (PARTITION BY sa.student_id, YEAR(hk.dt_time), MONTH(hk.dt_time)) AS NO_DAY
//   FROM rooms r
//   LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
//   LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
//   LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
//   WHERE  month(hk.dt_time) = $month - 1 and year(hk.dt_time) = $year
  
//     AND r.hostel_id = '$hostel'  $room
   
// 	and hk.device_id <> 31
//     AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
//     -- Add the condition to exclude student_id based on joining_date
//     AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
//     AND sa.student_id IN ($commaSeparatedIds)
//   GROUP BY  hk.client_id, hk.device_id, rm.phase, r.room_no,sa.student_id ORDER BY r.room_no) Y LEFT JOIN 
  
//   (SELECT 
//     DATE(hk.dt_time) AS dt_time,
//     r.hostel_id,
//     hk.device_id,
//     r.room_no,
//     r.room_id,
//     sa.student_id,
//     rm.phase, 
//     sa.leaving_date,
//     sa.joining_date,
//     MAX(
//         CASE 
//             WHEN rm.phase = 1 THEN hk.wh_1
//             WHEN rm.phase = 2 THEN hk.wh_2
//             WHEN rm.phase = 3 THEN hk.wh_3
//         END
//     ) AS max_wh,
   
//     COUNT(r.room_no) OVER (PARTITION BY hk.dt_time, r.room_no) AS room_no_count,
//     COUNT(sa.student_id) OVER (PARTITION BY sa.student_id, YEAR(hk.dt_time), MONTH(hk.dt_time)) AS NO_DAY
//   FROM rooms r
//   LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
//   LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
//   LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
//   WHERE month(hk.dt_time) = $month and year(hk.dt_time) = $year
//     AND r.hostel_id = '$hostel' $room
// 	and hk.device_id <> 31
//     AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
//     -- Add the condition to exclude student_id based on joining_date
//     AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
//     AND sa.student_id IN ($commaSeparatedIds)
//   GROUP BY  hk.client_id, hk.device_id, rm.phase, r.room_no, sa.student_id ORDER BY r.room_no) X 
//   ON Y.hostel_id = X.hostel_id AND Y.device_id = X.device_id AND Y.student_id = X.student_id AND Y.room_id = X.room_id
//   ) Z GROUP BY Z.student_id ORDER BY Z.room_no
// "

$query ="SELECT  Z.hostel_id, Z.device_id,Z.room_no,Z.student_id, Z.difference,ROUND( Z.difference / SUM(Z.NO_Day) OVER (PARTITION BY Z.room_no),2) AS per_day,
	 
round(Z.NO_Day * (Z.difference / SUM(Z.NO_Day) OVER (PARTITION BY Z.room_no)),2)  AS Units,$rate as rate, 

round(Z.NO_Day * (Z.difference / SUM(Z.NO_Day) OVER (PARTITION BY Z.room_no)) * $rate ,0) AS SUM,

$comm_area AS common_area,
Z.NO_Day AS total_days,

Z.NO_Day * $comm_area  AS ca_amount, ROUND((Z.NO_Day * $comm_area) + (Z.NO_Day * (Z.difference / SUM(Z.NO_Day) OVER (PARTITION BY Z.room_no)) * $rate) )AS Total_Amount


FROM (  SELECT Y.dt_time,Y.hostel_id,Y.device_id, Y.room_no,Y.room_id,Y.student_id,Y.phase,
Y.leaving_date,Y.joining_date,X.max_wh, Y.max_wh AS min_wh,
(X.max_wh - Y.max_wh) / 1000 AS difference, 
Y.room_no_count,( (X.max_wh - Y.max_wh) / Y.room_no_count) /1000 AS units , 

CASE
  WHEN (Y.joining_date IS NULL OR Y.joining_date < '$start_date')
    THEN TIMESTAMPDIFF(DAY, '$start_date', COALESCE(Y.leaving_date, '$end_date')) + 1
  ELSE
    TIMESTAMPDIFF(DAY, Y.joining_date, COALESCE(Y.leaving_date, '$end_date'))
END AS NO_DAY

FROM 
(SELECT 
DATE(hk.dt_time) AS dt_time,
r.hostel_id,
hk.device_id,
r.room_no,
r.room_id,
sa.student_id,
rm.phase, 
sa.leaving_date,
sa.joining_date,
MAX(
   CASE 
       WHEN rm.phase = 1 THEN hk.wh_1
       WHEN rm.phase = 2 THEN hk.wh_2
       WHEN rm.phase = 3 THEN hk.wh_3
   END
) AS max_wh,

COUNT(r.room_no) OVER (PARTITION BY hk.dt_time, r.room_no) AS room_no_count,
COUNT(sa.student_id) OVER (PARTITION BY sa.student_id, YEAR(hk.dt_time), MONTH(hk.dt_time)) AS NO_DAY
FROM rooms r
LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
WHERE  month(hk.dt_time) = $previousMonth and year(hk.dt_time) = $previousYear

AND (sa.leaving_date IS NULL OR sa.leaving_date BETWEEN '$start_date' AND '$end_date' OR sa.leaving_date > '$end_date' )
     AND (sa.joining_date IS NULL OR sa.joining_date <= '$end_date' )

AND r.hostel_id = '$hostel'  $room_no

and hk.device_id <> 31


GROUP BY  hk.client_id, hk.device_id, rm.phase, r.room_no,sa.student_id ORDER BY r.room_no) Y LEFT JOIN 

(SELECT 
DATE(hk.dt_time) AS dt_time,
r.hostel_id,
hk.device_id,
r.room_no,
r.room_id,
sa.student_id,
rm.phase, 
sa.leaving_date,
sa.joining_date,
MAX(
   CASE 
       WHEN rm.phase = 1 THEN hk.wh_1
       WHEN rm.phase = 2 THEN hk.wh_2
       WHEN rm.phase = 3 THEN hk.wh_3
   END
) AS max_wh,

COUNT(r.room_no) OVER (PARTITION BY hk.dt_time, r.room_no) AS room_no_count,
COUNT(sa.student_id) OVER (PARTITION BY sa.student_id, YEAR(hk.dt_time), MONTH(hk.dt_time)) AS NO_DAY
FROM rooms r
LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
WHERE month(hk.dt_time) = $month and year(hk.dt_time) = $year
AND r.hostel_id = '$hostel' $room_no
AND (sa.leaving_date IS NULL OR sa.leaving_date BETWEEN '$start_date' AND '$end_date' OR sa.leaving_date > '$end_date' )
     AND (sa.joining_date IS NULL OR sa.joining_date <= '$end_date' )
and hk.device_id <> 31


GROUP BY  hk.client_id, hk.device_id, rm.phase, r.room_no, sa.student_id ORDER BY r.room_no) X 
ON Y.hostel_id = X.hostel_id AND Y.device_id = X.device_id AND Y.student_id = X.student_id AND Y.room_id = X.room_id
) Z GROUP BY Z.student_id ORDER BY Z.room_no";

$result = DB::select($query);

// //return response()->json($result);
// $htmlContent = $this->generate_html_monthly($result,$hostel,$room,$start_date,$end_date);

// return view('billing_report', ['htmlContent' => $htmlContent]);

return $result;
}

public function getDistinctRoomNumbers($hostelId) {
    $roomNumbers = DB::table('rooms as r')
        ->select('r.room_no')
        ->leftJoin('students_allotment as sa', 'sa.room_id', '=', 'r.room_id')
        ->leftJoin('room_mfd as rm', 'rm.room_id', '=', 'sa.room_id')
        ->leftJoin('hourly_kwh as hk', function ($join) {
            $join->on('hk.client_id', '=', 'rm.client_id')
                ->on('hk.device_id', '=', 'rm.device_id');
        })
        ->where('r.hostel_id', $hostelId)
        ->orderBy('r.room_no')
        ->distinct()
        ->pluck('r.room_no')
        ->toArray();

    return $roomNumbers;
}
}
