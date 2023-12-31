<?php

namespace App\Http\Controllers;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\energy; 
use Illuminate\Support\Facades\Response;

class Student_noconsumption_report_Controller extends Controller
{
    public function getstudentnoconsumptionreport(Request $request)
    {
        // $date = $request->input('date');
        // $client_id = $request->input('client_id');

        $query = "
        SELECT
        subquery.dt_time,
        subquery.hostel_id,
        subquery.device_id,
        subquery.room_no,
        subquery.room_id,
        GROUP_CONCAT(DISTINCT subquery.student_id ORDER BY subquery.hostel_id) AS student_ids,
        subquery.`phase`,
        max_subquery.selected_wh_max,
        min_subquery.selected_wh_min,
        max_subquery.selected_wh_max - min_subquery.selected_wh_min AS consumption
    FROM (
        SELECT
            hk.dt_time,
            hk.device_id,
            r.room_no,
            r.room_id,
            sa.student_id,
            rm.`phase`,
            r.hostel_id
        FROM rooms r
        LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
        LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
        LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
        WHERE DATE(hk.dt_time) = (SELECT MAX(DATE(dt_time)) FROM hourly_kwh)
        GROUP BY rm.room_id, sa.student_id, hk.dt_time, rm.`phase`, hk.device_id, r.hostel_id
    ) AS subquery
    LEFT JOIN (
        SELECT
            rm.room_id,
            hk.device_id,
            rm.`phase`,
            MAX(CASE WHEN rm.`phase` = 1 THEN hk.wh_1
                     WHEN rm.`phase` = 2 THEN hk.wh_2
                     WHEN rm.`phase` = 3 THEN hk.wh_3 END) AS selected_wh_max
        FROM rooms r
        LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
        LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
        LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
        WHERE DATE(hk.dt_time) = (SELECT MAX(DATE(dt_time)) FROM hourly_kwh)
        GROUP BY rm.room_id, hk.device_id, rm.`phase`
    ) AS max_subquery ON subquery.room_id = max_subquery.room_id AND subquery.device_id = max_subquery.device_id AND subquery.`phase` = max_subquery.`phase`
    
    LEFT JOIN (
        SELECT
            rm.room_id,
            hk.device_id,
            rm.`phase`,
            MIN(CASE WHEN rm.`phase` = 1 THEN hk.wh_1
                     WHEN rm.`phase` = 2 THEN hk.wh_2
                     WHEN rm.`phase` = 3 THEN hk.wh_3 END) AS selected_wh_min
        FROM rooms r
        LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
        LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
        LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
        WHERE DATE(hk.dt_time) = (SELECT MAX(DATE(dt_time)) FROM hourly_kwh)
        GROUP BY rm.room_id, hk.device_id, rm.`phase`
    ) AS min_subquery ON subquery.room_id = min_subquery.room_id AND subquery.device_id = min_subquery.device_id AND subquery.`phase` = min_subquery.`phase`
    WHERE max_subquery.selected_wh_max - min_subquery.selected_wh_min < 0.1
    GROUP BY subquery.device_id, subquery.`phase`
    HAVING COUNT(*) > 1
    ORDER BY subquery.hostel_id ASC;
        ";

        $results = DB::select($query);

        // // return response()->json($results);
        // return $this->generate_html($client_id, $date, $results);


    //     // Update the hour of the last row if it's 0
    // $lastRow = end($results);
    // if ($lastRow && $lastRow->HOUR === 0) {
    //     $lastRow->HOUR = 24;
    // }

    $htmlContent = $this->generate_html($results);

    return view('student_no_consumption_report', ['htmlContent' => $htmlContent]);
}
    

    public function downloadPDF(Request $request)
    {
        $htmlContent = $request->input('htmlContent');

        $dompdf = new Dompdf();
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        return Response::make($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Student_with_no_consumption_report.pdf"'
        ]);
    }

    public function generate_html( $results)
    {
        $energy = new energy();
        $tableRows = '';
        $sum_ryb = 0;
        $sum_total = 0;
        $sum_common_area = 0;

        foreach ($results as $value) {
            $tableRows .= '<tr>
                <td>' . $value->hostel_id . '</td>
                <td>' . $value->room_id . '</td>
                <td>' . ($value->room_no ?? '') . '</td>
                <td>' . ($value->phase ?? '') . '</td>
                <td>' . ($value->student_id ?? '') . '</td>
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
                    <th>Hostel ID</th>
                    <th>Room ID</th>
                    <th>Room No</th>
                    <th>Phase</th>
                    <th>Student Id </th>
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
                    th, td {
                        border: 1px solid #dddddd;
                        text-align: left;
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
                    </div>
                </center>
                <hr>
                <h3>Rooms with No Consuption Report</h3>
                <table>' . $tableContent . '</table>
                <footer class="footer">
                    <span class="page-number">[Page: ] </span>
                    <span style="text-align:center padding:0 20%;">
                        Heta Datain www.hetadatain.com
                    </span>
                </footer>
            </body>
            </html>';

        return $htmlContent;
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
                       

    