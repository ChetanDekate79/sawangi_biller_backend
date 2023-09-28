<?php

namespace App\Http\Controllers;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\energy; 
use Illuminate\Support\Facades\Response;

class EmptyRoomConsumptionReport_Controller extends Controller
{
    public function getemptyroomreport(Request $request)
    {
        // $date = $request->input('date');
        // $client_id = $request->input('client_id');

        $query = "
        SELECT Q.*, (Q.selected_wh_max - Q.selected_wh_min) AS consumption
FROM (
    SELECT 
    DATE(hk.dt_time) AS date,
        r.hostel_id, 
        hk.device_id,
        r.room_no,
        r.room_id,
        sa.student_id,
        rm.`phase`,
        CASE
            WHEN rm.`phase` = 1 THEN max(hk.wh_1)
            WHEN rm.`phase` = 2 THEN max(hk.wh_2)
            WHEN rm.`phase` = 3 THEN max(hk.wh_3)
        END AS selected_wh_max,
        CASE
            WHEN rm.`phase` = 1 THEN min(hk.wh_1)
            WHEN rm.`phase` = 2 THEN min(hk.wh_2)
            WHEN rm.`phase` = 3 THEN min(hk.wh_3)
        END AS selected_wh_min
   FROM rooms r
LEFT JOIN students_allotment sa ON sa.room_id = r.room_id
LEFT JOIN room_mfd rm ON rm.room_id = r.room_id
LEFT JOIN hourly_kwh hk ON hk.device_id = rm.device_id AND hk.client_id = rm.client_id
WHERE sa.student_id IS NULL  
AND DATE(hk.dt_time) = (select max(date(dt_time)) from hourly_kwh)
GROUP BY r.room_no,hk.device_id
) Q
HAVING consumption > 0.1
ORDER BY hostel_id
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

    return view('empty_room_consumption_report', ['htmlContent' => $htmlContent]);
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
            'Content-Disposition' => 'attachment; filename="empty_room_consumption_report.pdf"'
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
              
                <td>' . ($value->room_no ?? '') . '</td>
               
                <td>' . ($value->consumption ?? '') . '</td>
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
// Assuming $results contains the 'date' field
$date = isset($results[0]->date) ? $results[0]->date : '';  // Assuming 'date' is in the first result
$tableContent = '
            <thead>
                <tr>
                    <th>Hostel ID</th>
                 
                    <th>Room No</th>
                    
                    <th>Consumption </th>
                </tr>
            </thead>
            <tbody>' . $tableRows . '</tbody>';

        $htmlContent = '
            <html>
            <head>
                <title>JNMC-'. $date . '</title>
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
                    </div>
                </center>
                <hr>
                <h3>Empty Rooms Consuption Report - ' . $date . ' </h3>
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
                       

    