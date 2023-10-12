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
    public function billing_report($hostel,$year,$month,$rate){

        $query = "SELECT 
        Q.*, 
        Q.max_wh - Q.min_wh AS units,
        COUNT(Q.room_no) OVER (PARTITION BY Q.dt_time, Q.room_no) AS room_no_count,
        ROUND((Q.max_wh - Q.min_wh) / COUNT(Q.room_no) OVER (PARTITION BY Q.dt_time, Q.room_no),0) AS each_units,
       ? AS rate,
        ROUND(((Q.max_wh - Q.min_wh) / COUNT(Q.room_no) OVER (PARTITION BY Q.dt_time, Q.room_no)),0) * ? AS Amount  
    FROM (
        -- Your existing query here
        SELECT 
            DATE(hk.dt_time) AS dt_time,
            hk.client_id,
            hk.device_id,
            r.room_no,
            r.room_id,
            sa.student_id,
            rm.phase,
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
            ) AS min_wh
        FROM rooms r
        LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
        LEFT JOIN room_mfd rm ON rm.room_id = sa.room_id
        LEFT JOIN hourly_kwh hk ON hk.client_id = rm.client_id AND hk.device_id = rm.device_id
        WHERE MONTH(hk.dt_time) = ? AND YEAR(hk.dt_time) = ? AND r.hostel_id= ?
        GROUP BY DATE(hk.dt_time), hk.client_id, hk.device_id, rm.phase, r.room_no, sa.student_id
        ORDER BY DATE(hk.dt_time)
    ) Q;";

    $result = DB::select($query,[$rate, $rate,$month, $year, $hostel]);

    // return response()->json($result);
    $htmlContent = $this->generate_html($result);

    return view('billing_report', ['htmlContent' => $htmlContent]);
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
        // $sum_ryb = 0;
        // $sum_total = 0;
        // $sum_common_area = 0;

        foreach ($results as $value) {
            $tableRows .= '<tr>
                <td>' . $value->dt_time . '</td>
                <td>' . $value->client_id . '</td>
                <td>' . $value->room_id . '</td>
                <td>' . ($value->room_no ?? '') . '</td>
                <td>' . ($value->phase ?? '') . '</td>
                <td>' . ($value->student_id ?? '') . '</td>
                <td>' . ($value->units ?? '') . '</td>
                <td>' . ($value->room_no_count ?? '') . '</td>
                <td>' . ($value->each_units ?? '') . '</td>
                <td>' . ($value->rate ?? '') . '</td>
                <td>' . ($value->Amount ?? '') . '</td>
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
                    <th>Room ID</th>
                    <th>Room No</th>
                    <th>Phase</th>
                    <th>Student Id </th>
                    <th>Total Units</th>
                    <th>No. of Students</th>
                    <th>Each Units</th>
                    <th>Rate</th>
                    <th>Amount</th>
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

    public function downloadExcel(Request $request)
{
    $htmlContent = $request->input('htmlContent');

    // Load HTML content into a PhpSpreadsheet object
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getActiveSheet()->setCellValue('A1', $htmlContent);

    $writer = new Xlsx($spreadsheet);
    
    ob_start();
    $writer->save('php://output');
    $excelOutput = ob_get_clean();

    return response()->make($excelOutput, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="Billing_Report.xlsx"'
    ]);
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
                       

    