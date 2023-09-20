<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class HostelConsumption_Controller extends Controller
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

        return response()->json($results);
    }
}
