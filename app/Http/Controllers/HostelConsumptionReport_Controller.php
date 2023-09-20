<?php

namespace App\Http\Controllers;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\energy; 
use Illuminate\Support\Facades\Response;

class HostelConsumptionReport_Controller extends Controller
{
    public function gethostelData(Request $request)
    {
        $date = $request->input('date');
        $client_id = $request->input('client_id');

        $query = "
        SELECT * FROM (
            SELECT
            q2.dt_time,
            q2.client_id,
            q2.HOUR,
            ROUND(SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END) / 1000) AS sum_ryb,
            ROUND(SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) / 1000) AS sum_total,
            ROUND((SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) - SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END)) / 1000) AS common_area,
            ROUND(((SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) - SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END)) / SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END)) * 100) AS Avg
        
          FROM
              ( SELECT
                    r.hostel_id AS client_id,
                    h.dt_time,
                    h.device_id,
                    HOUR,
                    h.wh_1 +h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = ? and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31) AND HOUR = 23
        GROUP BY  h.device_id, h.hour) q1
          
          LEFT JOIN
          
              (SELECT h.dt_time,
                    r.hostel_id AS client_id,
                    h.device_id,
                    h.HOUR,
                    h.wh_1 + h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = (? + INTERVAL 1 DAY) and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31) AND  HOUR = 0
        GROUP BY  h.device_id, h.hour) q2
          
          ON q1.device_id = q2.device_id
          
          
        
        union
        
        
        
        SELECT
            t1.dt_time,
            t1.client_id,
            t1.HOUR,
            ROUND(SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END) / 1000) AS sum_ryb,
            ROUND(SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) / 1000) AS sum_total,
            ROUND((SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) - SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END)) / 1000) AS common_area,
            ROUND(((SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) - SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END)) / SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END)) * 100) AS Avg
        FROM
            (
                SELECT
                    r.hostel_id AS client_id,
                    h.dt_time,
                    h.device_id,
                    HOUR,
                    h.wh_1 +h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = ? and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31)
        GROUP BY  h.device_id, h.hour
        
            ) t1
        LEFT JOIN
            (
                SELECT
                    r.hostel_id AS client_id,
                    h.device_id,
                    h.HOUR,
                    h.wh_1 + h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = ? and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31)
        GROUP BY  h.device_id, h.hour
            ) t2
        ON t1.client_id = t2.client_id AND t1.device_id = t2.device_id AND t1.HOUR = t2.HOUR + 1
            WHERE t1.HOUR <> 0
            GROUP BY t1.client_id, t1.HOUR
        ) AS combined_results
        ORDER BY dt_time,HOUR;
        ";

        $results = DB::select($query, [ $date,$client_id, $date, $client_id,$date,$client_id, $date, $client_id]);

        // // return response()->json($results);
        // return $this->generate_html($client_id, $date, $results);


        // Update the hour of the last row if it's 0
    $lastRow = end($results);
    if ($lastRow && $lastRow->HOUR === 0) {
        $lastRow->HOUR = 24;
    }

    $htmlContent = $this->generate_html($client_id, $date, $results);

    return view('report', ['htmlContent' => $htmlContent]);
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

    public function generate_html($client_id, $date, $results)
    {
        $energy = new energy();
        $tableRows = '';
        $sum_ryb = 0;
        $sum_total = 0;
        $sum_common_area = 0;

        foreach ($results as $value) {
            $tableRows .= '<tr>
                <td>' . $value->HOUR . '</td>
                <td>' . $value->sum_ryb . '</td>
                <td>' . ($value->sum_total ?? '') . '</td>
                <td>' . ($value->common_area ?? '') . '</td>
                <td>' . ($value->Avg ?? '') . '</td>
            </tr>';
            // Accumulate the sum for each column
        $sum_ryb += $value->sum_ryb;
        $sum_total += ($value->sum_total ?? 0);
        $sum_common_area += ($value->common_area ?? 0);
        }
        // Calculate the percentage
    $percentage = round(($sum_common_area / $sum_total) * 100, 0);
        // Add a row for the total
    $tableRows .= '<tr style="font-weight: bold; background-color: #f0f0f0;">
    <td>Total</td>
    <td>' . $sum_ryb . '</td>
    <td>' . $sum_total . '</td>
    <td>' . $sum_common_area . '</td>
    <td>' . $percentage . '</td>
</tr>';

        $tableContent = '
            <thead>
                <tr>
                    <th>Hour</th>
                    <th>Room Kwh</th>
                    <th>Hostel Kwh</th>
                    <th>Common Area</th>
                    <th>Common Area(%)</th>
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
                    <h2>' . $client_id . ' - ' . $energy->device_name . ' Report</h2>
                        <h3> Hourly Energy Report For Date - ' . $date . ' </h3>
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
                       

    