<?php

namespace App\Http\Controllers;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\energy; 
use Illuminate\Support\Facades\Response;

class Monthly_Report_Controller extends Controller
{
    public function gethostelData($client_id,$Month,$Year)
    {
        $client_id = strtolower($client_id); // Convert to lowercase

        $client_id2 = ($client_id === 'radhika') ? 'j8' : (($client_id === 'indira') ? 'j8' : "");
        // Set device_id_new based on client_id
       $device_id_new = ($client_id === 'radhika') ? 20 : (($client_id === 'indira') ? 21 : 0);
       if($client_id =='durga' or $client_id =='shalinta') 
        {$query = "SELECT 
            q1.hoste_name,ROUND(
                (SUM(q2.Min_WH21 - q1.Min_WH1 + q2.Min_WH22 - q1.Min_WH2 + q2.Min_WH23 - q1.Min_WH3) / 1000),2) AS Room_con,
            (ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - if(q3.Diff!='null',q3.Diff,0)) AS Net,
            0 AS Gen,
            ((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - if(q3.Diff!='null',q3.Diff,0))) AS Hostel_Load, 
            ROUND((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - if(q3.Diff!='null',q3.Diff,0)), 2) - ROUND(
                (SUM(q2.Min_WH21 - q1.Min_WH1 + q2.Min_WH22 - q1.Min_WH2 + q2.Min_WH23 - q1.Min_WH3) / 1000),2) AS CA,
                ROUND(((ROUND((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - if(q3.Diff!='null',q3.Diff,0)), 2) - ROUND(
                (SUM(q2.Min_WH21 - q1.Min_WH1 + q2.Min_WH22 - q1.Min_WH2 + q2.Min_WH23 - q1.Min_WH3) / 1000),2))/((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - if(q3.Diff!='null',q3.Diff,0))))*100,0) AS Ca_par 
            
        FROM
        (
            SELECT h.device_id,h.client_id AS hoste_name, 
                   MIN(h.wh_1) AS Min_WH1, 
                   MIN(h.wh_2) AS Min_WH2, 
                   MIN(h.wh_3) AS Min_WH3
            FROM hourly_kwh h
            WHERE h.client_id LIKE '{$client_id}%' AND h.device_id != 31 AND MONTH(h.dt_time) = {$Month} and year(h.dt_time) = {$Year}
            GROUP BY h.device_id
        ) q1
        JOIN
        (
            SELECT r.device_id, 
                   MIN(r.wh_1) AS Min_WH21, 
                   MIN(r.wh_2) AS Min_WH22, 
                   MIN(r.wh_3) AS Min_WH23
            FROM hourly_kwh r
            WHERE r.client_id LIKE '{$client_id}%' AND r.device_id != 31 AND MONTH(r.dt_time) = {$Month}+1 and year(r.dt_time) = {$Year}
            GROUP BY r.device_id
        ) q2
        ON q1.device_id = q2.device_id
        JOIN
        (
            SELECT w.device_id, 
                   MIN(w.wh_R) AS Min_WhR,
                   (MAX(CASE WHEN w.wh_D != 0 THEN w.wh_D END) - MIN(w.wh_D)) / 1000 AS Diff
            FROM hourly_kwh w
            WHERE w.client_id LIKE '{$client_id}%' AND w.device_id = 31 AND MONTH(w.dt_time) = {$Month} and year(w.dt_time) = {$Year}
            GROUP BY w.device_id
        ) q3
        JOIN
        (
            SELECT n.device_id, 
                   MIN(n.wh_R) AS Min_WhR2
            FROM hourly_kwh n
            WHERE n.client_id LIKE '{$client_id}%' AND n.device_id = 31 AND MONTH(n.dt_time) = {$Month}+1 and year(n.dt_time) = {$Year}
            GROUP BY n.device_id
        ) q4
        ON q3.device_id = q4.device_id;";
    }
    else
  {$query = "
    SELECT 
    q1.hoste_name AS client_id, -- Include the client_id column
    ROUND(
        (SUM(q2.Min_WH21 - q1.Min_WH1 + q2.Min_WH22 - q1.Min_WH2 + q2.Min_WH23 - q1.Min_WH3) / 1000),2) AS Room_con,
    (ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - q3.Diff) AS Net,
    q5.Gen,
    ((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - q3.Diff)+ q5.Gen) AS Hostel_Load, 
    ROUND((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - q3.Diff)+ q5.Gen, 2) - ROUND(
        (SUM(q2.Min_WH21 - q1.Min_WH1 + q2.Min_WH22 - q1.Min_WH2 + q2.Min_WH23 - q1.Min_WH3) / 1000),2) AS CA,
        ROUND(((ROUND((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - q3.Diff)+ q5.Gen, 2) - ROUND(
        (SUM(q2.Min_WH21 - q1.Min_WH1 + q2.Min_WH22 - q1.Min_WH2 + q2.Min_WH23 - q1.Min_WH3) / 1000),2))/((ROUND((q4.Min_WhR2 - q3.Min_WhR) / 1000, 2) - q3.Diff)+ q5.Gen))*100,0) AS Ca_par
    
FROM
(
    SELECT h.device_id,h.client_id AS hoste_name, 
           MIN(h.wh_1) AS Min_WH1, 
           MIN(h.wh_2) AS Min_WH2, 
           MIN(h.wh_3) AS Min_WH3
    FROM hourly_kwh h
    WHERE h.client_id LIKE '{$client_id}%' AND h.device_id != 31 AND MONTH(h.dt_time) = {$Month} and year(h.dt_time) = {$Year}
    GROUP BY h.device_id
) q1
JOIN
(
    SELECT r.device_id, 
           MIN(r.wh_1) AS Min_WH21, 
           MIN(r.wh_2) AS Min_WH22, 
           MIN(r.wh_3) AS Min_WH23
    FROM hourly_kwh r
    WHERE r.client_id LIKE '{$client_id}%' AND r.device_id != 31 AND MONTH(r.dt_time) = {$Month}+1 and year(r.dt_time) = {$Year}
    GROUP BY r.device_id
) q2
ON q1.device_id = q2.device_id
JOIN
(
    SELECT w.device_id, 
           MIN(w.wh_R) AS Min_WhR,
           (MAX(CASE WHEN w.wh_D != 0 THEN w.wh_D END) - MIN(w.wh_D)) / 1000 AS Diff
    FROM hourly_kwh w
    WHERE w.client_id LIKE '{$client_id}%' AND w.device_id = 31 AND MONTH(w.dt_time) = {$Month} and year(w.dt_time) = {$Year} 
    GROUP BY w.device_id,client_id
) q3
JOIN
(
    SELECT n.device_id, 
           MIN(n.wh_R) AS Min_WhR2
    FROM hourly_kwh n
    WHERE n.client_id LIKE '{$client_id}%' AND n.device_id = 31 AND MONTH(n.dt_time) = {$Month}+1 and year(n.dt_time) = {$Year}
    GROUP BY n.device_id ,client_id
) q4
ON q3.device_id = q4.device_id
LEFT JOIN 
(
    SELECT (MAX(h.wh_R) - MIN(h.wh_R)) / 1000 AS Gen
    FROM hourly_kwh h
    WHERE h.client_id LIKE '{$client_id2}%' AND h.device_id = $device_id_new AND MONTH(h.dt_time) = {$Month} and year(h.dt_time) = {$Year}
) q5
ON 1=1;
    ";
}
        // return $query;
        $results = DB::select($query);

        // // return response()->json($results);
        // return $this->generate_html($client_id, $date, $results);


    //     // Update the hour of the last row if it's 0
    // $lastRow = end($results);
    // if ($lastRow && $lastRow->HOUR === 0) {
    //     $lastRow->HOUR = 24;
    // }
    #return $results;
    $htmlContent = $this->generate_html($results,$client_id,$Month,$Year);

    return view('
    
    
    ', ['htmlContent' => $htmlContent]);
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
            'Content-Disposition' => 'attachment; filename="energy_report.pdf"'
        ]);
    }

    public function generate_html( $results,$nss,$Month,$Year)
    {
        $energy = new energy();
        $tableRows = '';
        $sum_ryb = 0;
        $sum_total = 0;
        $sum_common_area = 0;

        foreach ($results as $value) {
            $tableRows .= '<tr>
            <td>' . $nss . '</td>
            <td>' . $value->Room_con . '</td>
            <td>' . $value->Net . '</td>
            <td>' . $value->Gen . '</td>
                <td>' . $value->Hostel_Load . '</td>
                <td>' . ($value->CA ?? '') . '</td>
                <td>' . ($value->Ca_par ?? '') . '</td>
            
            </tr>';
//             // Accumulate the sum for each column
//         $sum_ryb += $value->sum_ryb;
//         $sum_total += ($value->sum_total ?? 0);
//         $sum_common_area += ($value->common_area ?? 0);
//         }
//         // Calculate the percentage
//     $percentage = round(($sum_common_area / $sum_total) * 100, 0);
//         // Add a row for the total
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
                    <th>Client_id</th>
                    <th>Total Room Consumption</th>
                    <th>Net</th>
                    <th>Gen</th>
                    <th>Hostel Load</th>
                    <th>Ca</th>
                    <th>CA %</th>
                </tr>
            </thead>
            <tbody>' . $tableRows . '</tbody>';

        $htmlContent = '
            <html>
            <head>
                <title>JNMC-</title>
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
                    <h2> '.$nss.' - Report</h2>
                        <h3> Monthly Consumption Report For Date -'.$Month.'-'.$Year.'   </h3>
                    </div>
                </center>
                <hr>
                <h3>Energy Consumption</h3>
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
    }}

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
                       

    