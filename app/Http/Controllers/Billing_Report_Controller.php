<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Model\energy; 
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Billing_Report_Controller extends Controller
{

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

    public function billing_report($hostel, $room, $start_date, $end_date, $rate)
{

    set_time_limit(600); 
    
    {
        if ($room === 'all') {
            $roomNumbers = $this->getDistinctRoomNumbers($hostel);
            $finalResult = [];
    
            foreach ($roomNumbers as $roomNumber) {
                $result = $this->fetchBillingDataForRoom($hostel, $roomNumber, $start_date, $end_date, $rate);
                $finalResult = array_merge($finalResult, $result);
            }
    
            $htmlContent = $this->generate_html($finalResult, $hostel, $room, $start_date, $end_date);
        } else {
            $result = $this->fetchBillingDataForRoom($hostel, $room, $start_date, $end_date, $rate);
            $htmlContent = $this->generate_html($result, $hostel, $room, $start_date, $end_date);
        }
    
        return view('billing_report', ['htmlContent' => $htmlContent]);
    }}


public function billing_report_monthly($hostel,$room,$start_date,$end_date,$rate,$comm_area)
{

    set_time_limit(600); 
    
    {
        if ($room === 'all') {
            $roomNumbers = $this->getDistinctRoomNumbers($hostel);
            $finalResult = [];
    
            foreach ($roomNumbers as $roomNumber) {
                $result = $this->fetchBillingDataForRoom_monthly($hostel, $roomNumber, $start_date, $end_date, $rate,$comm_area);
                $finalResult = array_merge($finalResult, $result);
            }
    
            $htmlContent = $this->generate_html_monthly($finalResult, $hostel, $room, $start_date, $end_date);
        } else {
            $result = $this->fetchBillingDataForRoom_monthly($hostel, $room, $start_date, $end_date, $rate,$comm_area);
            $htmlContent = $this->generate_html_monthly($result, $hostel, $room, $start_date, $end_date);
        }
    
        return view('billing_report', ['htmlContent' => $htmlContent]);
    } }


    public function fetchBillingDataForRoom($hostel,$room,$start_date,$end_date,$rate){

        $query = "
		SELECT *,
  ROUND(A.difference / sc.room_no_count2, 2) AS each_unit,
  ? AS rate,
  ROUND((A.difference / sc.room_no_count2) * ?, 2) AS amount
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
    COUNT(r.room_no) OVER (PARTITION BY hk.dt_time, r.room_no) AS room_no_count
  FROM rooms r
  LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
  LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
  LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
  WHERE DATE(hk.dt_time) BETWEEN ? AND ?
   
    AND r.hostel_id = ? and r.room_no = ?
    AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
    -- Add the condition to exclude student_id based on joining_date
    AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
  GROUP BY DATE(hk.dt_time), hk.client_id, hk.device_id, rm.phase, r.room_no, sa.student_id
) A
LEFT JOIN (
   SELECT
    r.room_id,
    date(hk.dt_time) AS dt_time2,
    COUNT(DISTINCT sa.student_id) AS room_no_count2
  FROM rooms r
  LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
  LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
  LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
  WHERE r.hostel_id = ? and r.room_no = ?
    AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
    AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
     and DATE(hk.dt_time) BETWEEN ? AND ?
GROUP BY dt_time2 
) sc
ON A.room_id = sc.room_id AND A.dt_time = sc.dt_time2
ORDER BY DATE(A.dt_time);";

$result = DB::select($query,[$rate, $rate,$start_date, $end_date, $hostel,$room,$hostel,$room,$start_date, $end_date]);

    //  return response()->json($result);
    // $htmlContent = $this->generate_html($result,$hostel,$room,$start_date,$end_date);

    // return view('billing_report', ['htmlContent' => $htmlContent]);

    return $result;
}
	public function fetchBillingDataForRoom_monthly($hostel,$room,$start_date,$end_date,$rate,$comm_area){

        $query = "
		SELECT 
    Z.hostel_id,
    Z.room_no,
	 Z.Student_id,
	 sum(Z.each_unit) AS Units,
	 Z.rate, 
    sum(Z.Amount) AS SUM,
    Z.common_area,
    Z.total_days,
    sum(Z.Amount) +( Z.total_days * Z.common_area) AS Total_Amount
FROM (
    SELECT *,
    ROUND(A.difference / sc.room_no_count2, 2) AS each_unit,
    ? AS rate,
    ROUND((A.difference / sc.room_no_count2) * ?, 2) AS amount,
  ? AS common_area,
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
  WHERE DATE(hk.dt_time) BETWEEN ? AND ?
  
    AND r.hostel_id = ?
    AND r.room_no = ?
    AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
    -- Add the condition to exclude student_id based on joining_date
    AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
  GROUP BY DATE(hk.dt_time), hk.client_id, hk.device_id, rm.phase, r.room_no, sa.student_id
) A
LEFT JOIN (
    SELECT
     r.room_id AS room_id2,
     date(hk.dt_time) AS dt_time2,
     COUNT(DISTINCT sa.student_id) AS room_no_count2
   FROM rooms r
   LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
   LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
   LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
   WHERE r.hostel_id = ?   AND  r.room_no = ?
     AND (sa.leaving_date IS NULL OR sa.leaving_date >= DATE(hk.dt_time))
     AND (sa.joining_date IS NULL OR sa.joining_date <= DATE(hk.dt_time))
      and DATE(hk.dt_time) BETWEEN ? AND ?
 GROUP BY dt_time2 
 ) sc
 ON A.room_id = sc.room_id2 AND A.dt_time = sc.dt_time2
ORDER BY DATE(A.dt_time)) Z GROUP BY Z.student_id";

    $result = DB::select($query,[$rate, $rate, $comm_area,$start_date, $end_date, $hostel,$room,$hostel,$room,$start_date,$end_date]);

    // //return response()->json($result);
    // $htmlContent = $this->generate_html_monthly($result,$hostel,$room,$start_date,$end_date);

    // return view('billing_report', ['htmlContent' => $htmlContent]);

    return $result;
}
	
    public function generate_html($results,$hostel,$room,$start_date,$end_date)
    {
        $energy = new energy();
        $tableRows = '';
        // $sum_ryb = 0;
        // $sum_total = 0;
        // $sum_common_area = 0;

        foreach ($results as $value) {
            $tableRows .= '<tr>
                <td>' . $value->dt_time . '</td>
                <td>' . $value->hostel_id . '</td>
              
                <td>' . ($value->room_no ?? '') . '</td>
              
                <td>' . ($value->student_id ?? '') . '</td>
                <td>' . ($value->difference ?? '') . '</td>
                <td>' . ($value->room_no_count ?? '') . '</td>
                <td>' . ($value->each_unit ?? '') . '</td>
                <td>' . ($value->rate ?? '') . '</td>
                <td>' . ($value->amount ?? '') . '</td>
            </tr>';
            // Accumulate the sum for each column
        // $sum_ryb += $value->sum_ryb;
        // $sum_total += ($value->sum_total ?? 0);
        // $sum_common_area += ($value->common_area ?? 0);
        }
        // Calculate the percentage
    // $percentage = round(($sum_common_area / $sum_total) * 100, 0);
        // Add a row for the total
//     $tableRows .= '<tr style="font-weight: bold; background-color: #f0f0f0;">
//     <td>Total</td>
//     <td>' . $sum_ryb . '</td>
//     <td>' . $sum_total . '</td>
//     <td>' . $sum_common_area . '</td>
//     <td>' . $percentage . '</td>
// </tr>';

        $tableContent = '
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Hostel</th>
				
                    <th>Room No</th>
                  
                    <th>Student Id </th>
                    <th>Total Units</th>
                    <th>No. of Students</th>
                    <th>Each Units</th>
                    <th>Rate</th>
                    <th>Amount(Rs)</th>
                </tr>
            </thead>
            <tbody>' . $tableRows . '</tbody>';

        $htmlContent = '
            <html>
            <head>
                <title>JNMC</title>
                <style>
                    body {
                        font-family: "Comic Sans MS", cursive, sans-serif;
                    }
                    table {
                        font-family: Comic Sans MS;
                        border-collapse: collapse;
                        width: 100%;
                    }
                   th {
                        border: 1px solid #dddddd;
                        text-align: center;
                        padding: 8px;
                    }
					td {
                        border: 1px solid #dddddd;
                        text-align: right;
                        padding: 8px;
                    }
                    .std{ border: 0px; !important}
                    thead{ background-color: #dddddd;}
                    .flex-container {
                        display: flex;
                      }
                    .header-report {
                        top: -60px;
                        left: -60px;
                        right: -60px;
                        background-color: #d1fec5;
                        color: white;
                        text-align: center;
                        line-height: 35px;
                    }
                    @page { margin: 50px 25px 25px 25px; }
                    footer { position: fixed; bottom: -60px; left: 0px; right: 0px; }
                    .footer .page-number:after { content: counter(page); }
                    /* Your other CSS styles here */
                </style>
            </head>
            <body>
                <div class="header-report">
                    <table>
                        <tr>
                            <td class="std" style="text-align: left;">
                                <span style="">
                                    <img width="150px" src="'.$energy->heta_logo.'" id="">
                                </span>
                            </td>
                            <td class="std txt-align" style="text-align: right;">
                                <span style="">
                                    <img width="100px" src="'.$energy->plasto_logo.'" id="">
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <center>
                    <div>
                    <h3>Report for '.$hostel.' / '.$room.' from '.$start_date.' to '.$end_date.' </h3>
                    </div>
                </center>
                <hr>
               
                
                <table>' . $tableContent . '</table>
                <footer class="footer">
                    
                </footer>
            </body>
            </html>';

        return $htmlContent;
    }
	
	
	public function generate_html_monthly( $results,$hostel,$room,$start_date,$end_date)
    {
        $energy = new energy();
        $tableRows = '';
        $sum_units = 0;
        $sum_total = 0;
        $Amount_total = 0;

        foreach ($results as $value) {
            $tableRows .= '<tr>
                
                <td>' . $value->hostel_id . '</td>
              
                <td>' . ($value->room_no ?? '') . '</td>
              
                <td>' . ($value->student_id ?? '') . '</td>
                <td>' . ($value->Units ?? '') . '</td>
              
                <td>' . ($value->rate ?? '') . '</td>
                <td>' . ($value->SUM ?? '') . '</td>
				<td>' . ($value->common_area ?? '') . '</td>
                <td>' . ($value->total_days ?? '') . '</td>
				<td>' . ($value->Total_Amount ?? '') . '</td>
            </tr>';
            // Accumulate the sum for each column
         $sum_units += $value->Units;
         $sum_total += ($value->SUM ?? 0);
         $Amount_total += ($value->Total_Amount ?? 0);
        }
        // Calculate the percentage
    // $percentage = round(($sum_common_area / $sum_total) * 100, 0);
        // Add a row for the total
     $tableRows .= '<tr style="font-weight: bold; background-color: #f0f0f0;">
     <td>Total</td>
	 <td></td>
	 <td></td>
	 
     <td>' . $sum_units . '</td>
	 <td></td>
     <td>' . $sum_total . '</td>
	  <td></td>
	   <td></td>
     <td>' . $Amount_total . '</td>
    
 </tr>';

        $tableContent = '
            <thead>
                <tr>
                   
                    <th>Hostel</th>
				
                    <th>Room No</th>
                  
                    <th>Student Id </th>
                    <th>Total Units</th>
                    <th>Rate</th>
                    <th>Sum</th>
                    <th>Common Area (Rs)</th>
                    <th>Total Days</th>
					<th>Total Amount</th>
                </tr>
            </thead>
            <tbody>' . $tableRows . '</tbody>';

        $htmlContent = '
            <html>
            <head>
                <title>JNMC</title>
                <style>
                    body {
                        font-family: "Comic Sans MS", cursive, sans-serif;
                    }
                    table {
                        font-family: Comic Sans MS;
                        border-collapse: collapse;
                        width: 100%;
                    }
                    th {
                        border: 1px solid #dddddd;
                        text-align: center;
                        padding: 8px;
                    }
					td {
                        border: 1px solid #dddddd;
                        text-align: right;
                        padding: 8px;
                    }
                    .std{ border: 0px; !important}
                    thead{ background-color: #dddddd;}
                    .flex-container {
                        display: flex;
                      }
                    .header-report {
                        top: -60px;
                        left: -60px;
                        right: -60px;
                        background-color: #d1fec5;
                        color: white;
                        text-align: center;
                        line-height: 35px;
                    }
                    @page { margin: 50px 25px 25px 25px; }
                    footer { position: fixed; bottom: -60px; left: 0px; right: 0px; }
                    .footer .page-number:after { content: counter(page); }
                    /* Your other CSS styles here */
                </style>
            </head>
            <body>
                <div class="header-report">
                    <table>
                        <tr>
                            <td class="std" style="text-align: left;">
                                <span style="">
                                    <img width="150px" src="'.$energy->heta_logo.'" id="">
                                </span>
                            </td>
                            <td class="std txt-align" style="text-align: right;">
                                <span style="">
                                    <img width="100px" src="'.$energy->plasto_logo.'" id="">
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <center>
                    <div>
                    <h3>Report for '.$hostel.' / '.$room.' from '.$start_date.' to '.$end_date.' </h3>
                    </div>
                </center>
                <hr>
                
                <table>' . $tableContent . '</table>
                <footer class="footer">
                    
                </footer>
            </body>
            </html>';

        return $htmlContent;
    }
	
	public function downloadPDF(Request $request)
    {
        $htmlContent = $request->input('htmlContent');
		set_time_limit(300);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        return Response::make($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Billing_report.pdf"'
        ]);
    }
	
public function downloadCsv(Request $request) {
        $htmlContent = $request->input('htmlContent');

        // Set appropriate headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');

        // Output the HTML content as CSV
        echo $this->convertHtmlToCsv($htmlContent);
        exit();
    }

    private function convertHtmlToCsv($html) {
        // Remove HTML tags, trim extra spaces and newlines
        $plainText = strip_tags($html);
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $plainText = trim($plainText);

        // Replace newlines with commas to convert to CSV format
        $csv = str_replace("\n", ",", $plainText);

        return $csv;
    }




    public function generate_error_html($errorMessage)
    {
        $htmlContent = '
            <html>
            <head>
                <title>Error</title>
                <style>
                    body {
                        font-family: "Comic Sans MS", cursive, sans-serif;
                        text-align: center;
                    }
                    .error-message {
                        color: red;
                        font-size: 24px;
                    }
                    </style>
                </head>
                <body>
                    <div class="error-message">
                        <p>' . $errorMessage . '</p>
                    </div>
                </body>
                </html>';
    
            return $htmlContent;
        }
    }
                       

    